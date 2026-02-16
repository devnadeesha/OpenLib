<?php
/**
 * Contact Form Handler
 * Processes contact form submissions
 */

require_once '../config/db.php';
require_once '../config/security.php';

$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $name = sanitizeInput($_POST['name'] ?? '', $conn);
    $email = sanitizeInput($_POST['email'] ?? '', $conn);
    $phone = sanitizePhone($_POST['phone'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '', $conn);
    $message = sanitizeInput($_POST['message'] ?? '', $conn);

    // Validation
    if (empty($name)) {
        $response['errors'][] = 'Name is required';
    }
    if (empty($email) || !validateEmail($email)) {
        $response['errors'][] = 'Valid email is required';
    }
    if (empty($message)) {
        $response['errors'][] = 'Message is required';
    }
    if (strlen($message) < 10) {
        $response['errors'][] = 'Message must be at least 10 characters';
    }

    // If no errors, insert into database
    if (empty($response['errors'])) {
        $sql = "INSERT INTO contact_messages (name, email, phone, subject, message) 
                VALUES ('$name', '$email', '$phone', '$subject', '$message')";
        
        if ($conn->query($sql) === TRUE) {
            $response['success'] = true;
            $response['message'] = 'Message sent successfully! We will get back to you soon.';
        } else {
            $response['errors'][] = 'Failed to send message: ' . $conn->error;
        }
    }
} else {
    $response['errors'][] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
