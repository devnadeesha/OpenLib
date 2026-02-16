<?php

// Get errors if any
$login_errors = $_SESSION['login_errors'] ?? [];
$email_value = $_SESSION['email_value'] ?? '';

// Clear session errors
unset($_SESSION['login_errors']);
unset($_SESSION['email_value']);

// If already logged in, redirect
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../Dashboard/dashboard.php");
    } else {
        header("Location: ../Home/index.php");
    }
    exit();
}
?>
<?php
session_start();
require_once '../db_connect.php'; // Adjust path based on your folder structure

if (isset($_POST['login'])) {
    // Get and sanitize input
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validation array
    $errors = [];
    
    // Validate email format
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no validation errors, proceed with authentication
    if (empty($errors)) {
        // Prepare SQL statement to prevent SQL injection
        $sql = "SELECT user_id, first_name, last_name, email, password, role, status FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if user exists
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if account is active
            if ($user['status'] !== 'active') {
                $errors[] = "Your account has been " . $user['status'] . ". Please contact administrator.";
            } 
            // Verify password
            elseif (password_verify($password, $user['password'])) {
                // Password is correct, create session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../Dashboard/dashboard.php");
                } else {
                    header("Location: ../Home/index.php");
                }
                exit();
            } else {
                // Invalid password
                $errors[] = "Invalid email or password";
            }
        } else {
            // User not found
            $errors[] = "Invalid email or password";
        }
        
        $stmt->close();
    }
    
    // If there are errors, store them in session
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        $_SESSION['email_value'] = $email; // Preserve email input
        header("Location: user_login.php");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Form</title>
  <link rel="stylesheet" href="user_login.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

  <div class="wrapper">
    <form action="user_login.php" method="POST" autocomplete="off">
      <h1>Login</h1>
      
      <?php if (!empty($login_errors)): ?>
        <div class="alert">
          <?php foreach ($login_errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="input-box">
        <input type="email" placeholder="Email" name="email" value="<?php echo htmlspecialchars($email_value); ?>" required autocomplete="off">
        <i class='bx bxs-envelope'></i>
      </div>

      <div class="input-box">
        <input type="password" placeholder="Password" name="password" required autocomplete="current-password">
        <i class='bx bxs-lock-alt'></i>
      </div>

      <div class="remember-forgot">
        <label><input type="checkbox" name="remember"> Remember me</label>
        <a href="../Forgot_password/email_sec/email.php">Forgot password?</a>
      </div>

      <button type="submit" class="btn" name="login">Login</button>

      <div class="register-link">
        <p>Don't have an account? <a href="../Register/register.php">Register</a></p>
      </div>
    </form>
  </div>

</body>
</html>