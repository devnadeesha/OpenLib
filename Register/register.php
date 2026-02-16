<?php
session_start();
require_once 'db_connect.php';

if (isset($_POST['submit'])) {
    // Sanitize and validate input
    $first_name = trim($_POST['f_name']);
    $last_name = trim($_POST['l_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmPass'];

    //add the role
    $role = 'user';
    
    // Server-side validation
    $errors = [];
    
    // Validate names
    if (empty($first_name) || strlen($first_name) < 2) {
        $errors[] = "First name must be at least 2 characters long";
    }
    
    if (empty($last_name) || strlen($last_name) < 2) {
        $errors[] = "Last name must be at least 2 characters long";
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate password
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email already exists
    $check_email_sql = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($check_email_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "Email already registered";
    }
    $stmt->close();
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user into database
        $insert_sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! Please login.";
            header("Location: ../Login/user_login.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
    
    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = [
            'f_name' => $first_name,
            'l_name' => $last_name,
            'email' => $email
        ];
        header("Location: register_form.php");
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
  <title>Register Form</title>
  <link rel="stylesheet" href="register.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

  <div class="wrapper">
  <?php
        if (isset($_SESSION['errors'])) {
            echo '<div class="alert alert-danger">';
            foreach ($_SESSION['errors'] as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
            unset($_SESSION['errors']);
        }

        // Preserve form data
        $form_data = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);
    ?>

    <form action="register.php" method="POST" autocomplete="off" onsubmit=" return checkPassword()" >
      <h1>Register</h1>
      <div class="input-box">
        <input type="text" placeholder="First Name" name="f_name" required>
        <i class='bx bxs-user'></i>
      </div>
      <div class="input-box">
        <input type="text" placeholder="Last Name" name="l_name" required>
        <i class='bx bxs-user'></i>
      </div>

      <div class="input-box">
        <input type="email" placeholder="Email" name="email" required autocomplete="off" >
        <!--    <input type="text" name="email" inputmode="email">   mehema demmoth popup msg eka nawaththaganna puluwan. -->

        <i class='bx bxs-envelope'></i>
      </div>

      <div class="input-box">
        <input type="password" placeholder="Password" id= "pwd" name="password" required autocomplete="new-password">
        <i class='bx bxs-lock-alt'></i>
      </div>

      <div class="input-box">
        <input type="password" placeholder="Confirm Password" id= "confirm_pwd" name="confirmPass" required autocomplete="new-password">
        <i class='bx bxs-lock'></i>
      </div>

      

      <button type="submit" class="btn" name= "submit" >Create Account</button>

      <div class="register-link">
        <p>Already have an account? <a href="../Login/user_login.php">Login</a></p>
      </div>
    
      <div id= "alert"></div>
    </form>
  </div>
  <script>
    function checkPassword(){
      let p1 = document.getElementById("pwd").value;
      let p2 = document.getElementById("confirm_pwd").value;

        if(p1 !== p2){
          alert("Passwords do not match!");
          return false;
        }
        return true;
        }
  </script>

</body>
</html>
