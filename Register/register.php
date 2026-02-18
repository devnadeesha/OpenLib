<?php
session_start();

// ── DB CONNECTION (built in) ──────────────────────────────────────────────────
$conn = new mysqli('localhost', 'root', '', 'openlib');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ── HANDLE REGISTRATION ───────────────────────────────────────────────────────
if (isset($_POST['submit'])) {
    $first_name       = trim($_POST['f_name']);
    $last_name        = trim($_POST['l_name']);
    $email            = trim($_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirmPass'];
    $role             = 'user';
    $errors           = [];

    // Validate names
    if (empty($first_name) || strlen($first_name) < 2) {
        $errors[] = "First name must be at least 2 characters.";
    }
    if (empty($last_name) || strlen($last_name) < 2) {
        $errors[] = "Last name must be at least 2 characters.";
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Validate password
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->get_result()->num_rows > 0 && $errors[] = "Email already registered.";
    $stmt->close();

    // Insert if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            "INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $role);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! Please login.";
            header("Location: ../Policies/terms.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }

    // Store errors and go back
    if (!empty($errors)) {
        $_SESSION['errors']    = $errors;
        $_SESSION['form_data'] = [
            'f_name' => $first_name,
            'l_name' => $last_name,
            'email'  => $email,
        ];
        header("Location: register.php");
        exit();
    }
}

$conn->close();

// ── GET SESSION DATA ──────────────────────────────────────────────────────────
$errors    = $_SESSION['errors']    ?? [];
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['errors'], $_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | OpenLib</title>
  <link rel="stylesheet" href="register.css">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>

<body>
  <div class= "welcome-text">
    <p class="p1">Welcome</p>
    <p class="p2">to <span class="span1">Open</span><span>Lib</span></p>
  </div>
  <div class="wrapper">
    <form action="register.php" method="POST" autocomplete="off" onsubmit="return checkPassword()">
      <h1>Register</h1>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="input-box">
        <input type="text" placeholder="First Name" name="f_name"
               value="<?= htmlspecialchars($form_data['f_name'] ?? '') ?>" required>
        <i class='bx bxs-user'></i>
      </div>

      <div class="input-box">
        <input type="text" placeholder="Last Name" name="l_name"
               value="<?= htmlspecialchars($form_data['l_name'] ?? '') ?>" required>
        <i class='bx bxs-user'></i>
      </div>

      <div class="input-box">
        <input type="email" placeholder="Email" name="email"
               value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required autocomplete="off">
        <i class='bx bxs-envelope'></i>
      </div>

      <div class="input-box">
        <input type="password" placeholder="Password" id="pwd" name="password"
               required autocomplete="new-password">
        <i class='bx bxs-lock-alt'></i>
      </div>

      <div class="input-box">
        <input type="password" placeholder="Confirm Password" id="confirm_pwd" name="confirmPass"
               required autocomplete="new-password">
        <i class='bx bxs-lock'></i>
      </div>

      <button type="submit" name="submit" class="btn">Create Account</button>

      <div class="register-link">
        <p>Already have an account? <a href="../Login/user_login.php">Login</a></p>
      </div>
    </form>
  </div>

  <script>
    function checkPassword() {
      const p1 = document.getElementById("pwd").value;
      const p2 = document.getElementById("confirm_pwd").value;
      if (p1 !== p2) {
        alert("Passwords do not match!");
        return false;
      }
      return true;
    }
  </script>

</body>
</html>