<?php
/**
 * User Registration Handler
 * Processes registration form submission
 */

require_once '../config/db.php';
require_once '../config/security.php';
require_once '../config/session.php';

$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $first_name = sanitizeInput($_POST['first_name'] ?? '', $conn);
    $last_name = sanitizeInput($_POST['last_name'] ?? '', $conn);
    $email = sanitizeInput($_POST['email'] ?? '', $conn);
    $phone = sanitizePhone($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($first_name)) {
        $response['errors'][] = 'First name is required';
    }
    if (empty($last_name)) {
        $response['errors'][] = 'Last name is required';
    }
    if (empty($email) || !validateEmail($email)) {
        $response['errors'][] = 'Valid email is required';
    }
    if (empty($password)) {
        $response['errors'][] = 'Password is required';
    }
    if ($password !== $confirm_password) {
        $response['errors'][] = 'Passwords do not match';
    }
    if (strlen($password) < 8) {
        $response['errors'][] = 'Password must be at least 8 characters';
    }

    // If no errors, proceed with registration
    if (empty($response['errors'])) {
        // Check if email already exists
        $email_check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        
        if ($email_check->num_rows > 0) {
            $response['errors'][] = 'Email already registered';
        } else {
            // Hash password and insert user
            $password_hash = hashPassword($password);
            
            $sql = "INSERT INTO users (first_name, last_name, email, phone, password_hash) 
                    VALUES ('$first_name', '$last_name', '$email', '$phone', '$password_hash')";
            
            if ($conn->query($sql) === TRUE) {
                $response['success'] = true;
                $response['message'] = 'Registration successful! Redirecting to login...';
                $_SESSION['register_success'] = true;
            } else {
                $response['errors'][] = 'Registration failed: ' . $conn->error;
            }
        }
    }
} else {
    $response['errors'][] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
