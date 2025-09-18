<?php
// business.php

// Enable CORS
header("Access-Control-Allow-Origin: https://deliveryservicepartner.ca");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Configuration
$recipient_email = 'amar@deliveryservicepartner.ca';
$site_name = 'Delivery Service Partner';
$rate_limit = 5; // Max submissions per hour per IP

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Method not allowed']));
}

// Get and sanitize input data
$data = json_decode(file_get_contents('php://input'), true);

$companyName = filter_var($data['companyName'] ?? '', FILTER_SANITIZE_STRING);
$volume = filter_var($data['volume'] ?? '', FILTER_SANITIZE_STRING);
$area = filter_var($data['area'] ?? '', FILTER_SANITIZE_STRING);
$fleetType = filter_var($data['fleetType'] ?? '', FILTER_SANITIZE_STRING);
$resources = filter_var($data['resources'] ?? '', FILTER_SANITIZE_STRING);
$contactInfo = filter_var($data['contactInfo'] ?? '', FILTER_SANITIZE_STRING);

// Validate inputs
$errors = [];

if (empty($companyName)) {
    $errors[] = 'Company name is required';
}

if (empty($volume)) {
    $errors[] = 'Volume per day is required';
}

if (empty($area)) {
    $errors[] = 'Service area is required';
}

if (empty($fleetType)) {
    $errors[] = 'Fleet type is required';
}

if (empty($resources)) {
    $errors[] = 'Number of vehicles and drivers is required';
}

if (empty($contactInfo)) {
    $errors[] = 'Contact information is required';
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
    'From' => "$site_name <noreply@deliveryservicepartner.ca>",
    'Reply-To' => "Business Inquiry <info@deliveryservicepartner.ca>",
    'X-Mailer' => 'PHP/' . phpversion(),
    'Content-type' => 'text/html; charset=utf-8'
];

// Create header string
$header_string = implode("\r\n", array_map(
    function ($v, $k) { return "$k: $v"; },
    $headers,
    array_keys($headers)
));

// Prepare email content (HTML format)
$email_subject = "New Business Inquiry: $companyName";
$email_body = "
<html>
<head>
    <title>New Business Inquiry</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .details { border-collapse: collapse; width: 100%; }
        .details th, .details td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .details th { background-color: #f5f5f5; font-weight: bold; }
    </style>
</head>
<body>
    <h2>New Business Inquiry Received</h2>
    <p>A new business inquiry has been submitted through the website.</p>
    
    <table class='details'>
        <tr>
            <th>Company Name</th>
            <td>$companyName</td>
        </tr>
        <tr>
            <th>Volume per Day</th>
            <td>$volume packages</td>
        </tr>
        <tr>
            <th>Service Area</th>
            <td>$area</td>
        </tr>
        <tr>
            <th>Fleet Type Required</th>
            <td>$fleetType</td>
        </tr>
        <tr>
            <th>Vehicles & Drivers Required</th>
            <td>$resources</td>
        </tr>
        <tr>
            <th>Contact Information</th>
            <td>" . nl2br(htmlspecialchars($contactInfo)) . "</td>
        </tr>
    </table>
    
    <br>
    <p>This email was sent from the business inquiry form on your website.</p>
</body>
</html>
";

// Send email
try {
    $success = mail(
        $recipient_email,
        '=?UTF-8?B?' . base64_encode($email_subject) . '?=',
        $email_body,
        $header_string
    );

    if (!$success) {
        throw new Exception('Failed to send email');
    }

    echo json_encode(['status' => 'success', 'message' => 'Inquiry submitted successfully!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit inquiry. Please try again later.']);
}