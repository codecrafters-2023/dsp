<?php
// send_email.php

// Enable CORS (replace * with your domain in production)
header("Access-Control-Allow-Origin: https://deliveryservicepartner.ca");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Configuration
$recipient_email = 'amar@deliveryservicepartner.ca'; // Replace with your email
$site_name = 'STI'; // Replace with your site name
$min_message_length = 10;
$max_message_length = 1000;
$rate_limit = 5; // Max submissions per hour per IP

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Method not allowed']));
}

// Get and sanitize input data
$data = json_decode(file_get_contents('php://input'), true);

$name = filter_var($data['name'] ?? '', FILTER_SANITIZE_STRING);
$email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
$phone = filter_var($data['phone'] ?? '', FILTER_SANITIZE_STRING);
$subject = filter_var($data['subject'] ?? '', FILTER_SANITIZE_STRING);
$message = filter_var($data['message'] ?? '', FILTER_SANITIZE_STRING);

// Validate inputs
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

if (empty($subject)) {
    $errors[] = 'Subject is required';
}

if (strlen($message) < $min_message_length) {
    $errors[] = "Message must be at least $min_message_length characters";
}

if (strlen($message) > $max_message_length) {
    $errors[] = "Message cannot exceed $max_message_length characters";
}

// Check for errors
if (!empty($errors)) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'messages' => $errors]));
}

// Rate limiting
session_start();
$ip = $_SERVER['REMOTE_ADDR'];
$rate_key = 'rate_' . $ip;

if (!isset($_SESSION[$rate_key])) {
    $_SESSION[$rate_key] = 1;
} else {
    $_SESSION[$rate_key]++;
}

if ($_SESSION[$rate_key] > $rate_limit) {
    http_response_code(429);
    die(json_encode(['status' => 'error', 'message' => 'Too many requests. Please try again later.']));
}

// Prepare email headers
$headers = [
    'From' => "$site_name <noreply@stidrive.com>",
    'Reply-To' => "$name <$email>",
    'X-Mailer' => 'PHP/' . phpversion(),
    'Content-type' => 'text/plain; charset=utf-8'
];

// Create header string
$header_string = implode("\r\n", array_map(
    function ($v, $k) { return "$k: $v"; },
    $headers,
    array_keys($headers)
));

// Prepare email content
$email_subject = "$site_name - $subject";
$email_body = "Name: $name\n";
$email_body .= "Email: $email\n\n";
$email_body .= "Phone: $phone\n";
$email_body .= "Message:\n$message";

// Send email
try {
    $success = mail(
        $recipient_email,
        '=?UTF-8?B?' . base64_encode($email_subject) . '?=', // Handle special characters
        $email_body,
        $header_string
    );

    if (!$success) {
        throw new Exception('Failed to send email');
    }

    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to send message. Please try again later.']);
}