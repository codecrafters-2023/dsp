<?php
// send-driver-email.php

// Enable CORS (replace * with your domain in production)
header("Access-Control-Allow-Origin: https://deliveryservicepartner.ca");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Configuration
$recipient_email = 'amar@deliveryservicepartner.ca'; // Replace with your email
$site_name = 'Delivery Service Partner'; // Replace with your site name
$rate_limit = 5; // Max submissions per hour per IP

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Method not allowed']));
}

// Get and sanitize input data
$data = json_decode(file_get_contents('php://input'), true);

// Get all form fields
$name = filter_var($data['name'] ?? '', FILTER_SANITIZE_STRING);
$email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
$phone = filter_var($data['phone'] ?? '', FILTER_SANITIZE_STRING);
$dob = filter_var($data['dob'] ?? '', FILTER_SANITIZE_STRING);
$license_type = filter_var($data['license-type'] ?? '', FILTER_SANITIZE_STRING);
$vehicle_type = filter_var($data['vehicle-type'] ?? '', FILTER_SANITIZE_STRING);
$status = filter_var($data['status'] ?? '', FILTER_SANITIZE_STRING);
$experience = filter_var($data['experience'] ?? '', FILTER_SANITIZE_STRING);
$address = filter_var($data['address'] ?? '', FILTER_SANITIZE_STRING);
$job = filter_var($data['job'] ?? '', FILTER_SANITIZE_STRING);
$contract_type = filter_var($data['contractType'] ?? '', FILTER_SANITIZE_STRING);
$start_date = filter_var($data['startDate'] ?? '', FILTER_SANITIZE_STRING);

// Handle availability (array of checkboxes)
$availability = [];
if (isset($data['availability']) && is_array($data['availability'])) {
    foreach ($data['availability'] as $avail) {
        $availability[] = filter_var($avail, FILTER_SANITIZE_STRING);
    }
}
$availability_str = !empty($availability) ? implode(', ', $availability) : 'Not specified';

// Validate inputs
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

if (empty($phone)) {
    $errors[] = 'Phone number is required';
}

if (empty($dob)) {
    $errors[] = 'Date of birth is required';
}

if (empty($license_type)) {
    $errors[] = 'License type is required';
}

if (empty($vehicle_type)) {
    $errors[] = 'Vehicle type is required';
}

if (empty($status)) {
    $errors[] = 'Status in Canada is required';
}

if (empty($address)) {
    $errors[] = 'Address is required';
}

if (empty($contract_type)) {
    $errors[] = 'Contract type is required';
}

// if (empty($start_date)) {
//     $errors[] = 'Start date is required';
// }

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
    'Content-type' => 'text/html; charset=utf-8'
];

// Create header string
$header_string = implode("\r\n", array_map(
    function ($v, $k) { return "$k: $v"; },
    $headers,
    array_keys($headers)
));

// Prepare email content (HTML format for better readability)
$email_subject = "New Driver Application: $name";
$email_body = "
<html>
<head>
    <title>New Driver Application</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .application-details { border-collapse: collapse; width: 100%; }
        .application-details th, .application-details td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .application-details th { background-color: #f5f5f5; font-weight: bold; }
        .application-details tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <h2>New Driver Application Received</h2>
    <p>A new driver application has been submitted through the website.</p>
    
    <table class='application-details'>
        <tr>
            <th>Field</th>
            <th>Value</th>
        </tr>
        <tr>
            <td><strong>Full Name</strong></td>
            <td>$name</td>
        </tr>
        <tr>
            <td><strong>Email</strong></td>
            <td>$email</td>
        </tr>
        <tr>
            <td><strong>Phone Number</strong></td>
            <td>$phone</td>
        </tr>
        <tr>
            <td><strong>Date of Birth</strong></td>
            <td>$dob</td>
        </tr>
        <tr>
            <td><strong>License Type</strong></td>
            <td>$license_type</td>
        </tr>
        <tr>
            <td><strong>Vehicle Type</strong></td>
            <td>$vehicle_type</td>
        </tr>
        <tr>
            <td><strong>Availability</strong></td>
            <td>$availability_str</td>
        </tr>
        <tr>
            <td><strong>Status in Canada</strong></td>
            <td>$status</td>
        </tr>
        <tr>
            <td><strong>Delivery Experience</strong></td>
            <td>" . nl2br(htmlspecialchars($experience)) . "</td>
        </tr>
        <tr>
            <td><strong>Address</strong></td>
            <td>$address</td>
        </tr>
        <tr>
            <td><strong>Current Job</strong></td>
            <td>$job</td>
        </tr>
        <tr>
            <td><strong>Contract Type</strong></td>
            <td>$contract_type</td>
        </tr>
        <tr>
            <td><strong>Start Date</strong></td>
            <td>$start_date</td>
        </tr>
    </table>
    
    <br>
    <p>This email was sent from the driver application form on your website.</p>
</body>
</html>
";

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

    echo json_encode(['status' => 'success', 'message' => 'Application submitted successfully!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit application. Please try again later.']);
}