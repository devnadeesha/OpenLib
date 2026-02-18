<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'openlib';
$username = 'root';
$password = '';

// Create PDO connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['errors'] = ['Please login to upload profile picture.'];
    header("Location: ../Login/user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading file.";
    } else {
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $file['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF images are allowed.";
        }
        
        // Validate file size (max 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB in bytes
        if ($file['size'] > $max_size) {
            $errors[] = "File size must be less than 5MB.";
        }
        
        if (empty($errors)) {
            // Create upload directory if it doesn't exist
            $upload_dir = 'uploads/profile_pictures/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            // Get old profile picture
            $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Delete old profile picture if exists and not default
                if (!empty($user['profile_picture']) && 
                    $user['profile_picture'] !== 'default-avatar.png' && 
                    file_exists($upload_dir . $user['profile_picture'])) {
                    unlink($upload_dir . $user['profile_picture']);
                }
                
                // Update database
                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
                $stmt->execute([$new_filename, $user_id]);
                
                $success = "Profile picture updated successfully!";
            } else {
                $errors[] = "Failed to upload file.";
            }
        }
    }
}

// Store messages in session
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
}
if (!empty($success)) {
    $_SESSION['success'] = $success;
}

// Redirect back to profile
header("Location: user_profile.php");
exit();
?>