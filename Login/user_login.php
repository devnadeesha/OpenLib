<?php
// ── MUST BE FIRST: start session & connect DB ─────────────────────────────────
session_start();

$conn = new mysqli('localhost', 'root', '', 'openlib');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ── ALREADY LOGGED IN → REDIRECT ─────────────────────────────────────────────
// if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
//     header("Location: " . ($_SESSION['role'] === 'admin'
//         ? "../Admin_dashboard/dashboard.php"
//         : "../Home/index.php"));
//     exit();
// }

// ── GRAB SESSION ERRORS (from previous failed attempt) ───────────────────────
$login_errors = $_SESSION['login_errors'] ?? [];
$email_value  = $_SESSION['email_value']  ?? '';
unset($_SESSION['login_errors'], $_SESSION['email_value']);

// ── PROCESS LOGIN FORM ────────────────────────────────────────────────────────
if (isset($_POST['login'])) {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $errors   = [];

    // Validate
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    // Check DB
    if (empty($errors)) {
        $stmt = $conn->prepare(
            "SELECT user_id, first_name, last_name, email, password, role, status
             FROM users WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['status'] !== 'active') {
                $errors[] = "Your account is " . $user['status'] . ". Contact the administrator.";
            } elseif (password_verify($password, $user['password'])) {
                // ✅ Login success — set session
                $_SESSION['user_id']    = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name']  = $user['last_name'];
                $_SESSION['full_name']  = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['email']      = $user['email'];
                $_SESSION['role']       = $user['role'];
                $_SESSION['logged_in']  = true;

                if ($user['role'] === 'admin') {
                    header("Location: ../Admin_dashboard/dashboard.php");
                } else {
                    header("Location: ../Home/index.php");
                }
                exit();
            } else {
                $errors[] = "Invalid email or password.";
            }
        } else {
            $errors[] = "Invalid email or password.";
        }

        $stmt->close();
    }

    // ❌ Login failed — save errors and redirect back
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        $_SESSION['email_value']  = $email;
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
  <title>Login | OpenLib</title>
  <link rel="stylesheet" href="user_login.css?v=2">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
  <div class= "welcome-text">
    <p class="p1">Welcome</p>
    <p class="p2">to <span class="span1">Open</span><span>Lib</span></p>
  </div>
  <div class="wrapper">
    <form action="user_login.php" method="POST" autocomplete="off">
      <h1>Login</h1>

      <?php if (!empty($login_errors)): ?>
        <div class="alert">
          <?php foreach ($login_errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="input-box">
        <input type="email" name="email" placeholder="Email"
               value="<?= htmlspecialchars($email_value) ?>"
               required autocomplete="off">
        <i class='bx bxs-envelope'></i>
      </div>

      <div class="input-box">
        <input type="password" name="password" placeholder="Password"
               required autocomplete="current-password">
        <i class='bx bxs-lock-alt'></i>
      </div>

      <div class="remember-forgot">
        <label><input type="checkbox" name="remember"> Remember me</label>
        <a href="../Forgot_password/email_sec/email.php">Forgot password?</a>
      </div>

      <button type="submit" name="login" class="btn">Login</button>

      <div class="register-link">
        <p>Don't have an account? <a href="../Register/register.php">Register</a></p>
      </div>
    </form>
  </div>

</body>
</html>