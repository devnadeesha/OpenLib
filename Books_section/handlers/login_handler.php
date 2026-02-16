<?php
/**
 * User Login Handler
 * Authenticates users and creates session
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
    $email = sanitizeInput($_POST['email'] ?? '', $conn);
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email) || !validateEmail($email)) {
        $response['errors'][] = 'Valid email is required';
    }
    if (empty($password)) {
        $response['errors'][] = 'Password is required';
    }

    // If no validation errors, check credentials
    if (empty($response['errors'])) {
        $sql = "SELECT id, email, first_name, last_name, password_hash FROM users WHERE email = '$email' AND status = 'active'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (verifyPassword($password, $user['password_hash'])) {
                // Login successful - create session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                
                $response['success'] = true;
                $response['message'] = 'Login successful! Redirecting...';
            } else {
                $response['errors'][] = 'Invalid email or password';
            }
        } else {
            $response['errors'][] = 'Invalid email or password';
        }
    }
} else {
    $response['errors'][] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
