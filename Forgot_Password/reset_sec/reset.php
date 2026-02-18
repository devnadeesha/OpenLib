<?php
session_start();

// ── DB CONNECTION (built in) ──────────────────────────────────────────────────
$conn = new mysqli('localhost', 'root', '', 'openlib');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ── GUARD: must come from forgot password page ────────────────────────────────
if (!isset($_SESSION['reset_email'])) {
    header("Location: ../email_sec/email.php");
    exit();
}

$email  = $_SESSION['reset_email'];
$error  = '';
$success = '';

// ── HANDLE FORM SUBMIT ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pass     = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    // Validate
    if (empty($new_pass) || strlen($new_pass) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Passwords do not match.";
    } else {
        // Update password in DB
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt   = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed, $email);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            // Clear reset session
            unset($_SESSION['reset_email']);
            $stmt->close();
            $conn->close();
            // Redirect to login with success message
            $_SESSION['success'] = "Password updated successfully. Please login.";
            header("Location: ../../Login/user_login.php");
            exit();
        } else {
            $error = "Failed to update password. Please try again.";
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password | OpenLib</title>
  <link rel="stylesheet" href="reset.css?v=2">
</head>
<body>

<div class="wrapper">
  <form action="reset.php" method="POST" onsubmit="return checkPassword()">
    <h1>Reset Password</h1><br>

    <?php if (!empty($error)): ?>
      <p style="color:red; font-size:14px; margin-bottom:12px;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="input-box">
      <input type="password" id="new" name="new_pass"
             placeholder="New Password" required autocomplete="new-password">
    </div>

    <div class="input-box">
      <input type="password" id="confirm" name="confirm_pass"
             placeholder="Confirm Password" required autocomplete="new-password"><br><br>
    </div>

    <button type="submit" class="btn">Update Password</button>
  </form>
</div>

<script>
function checkPassword() {
  const p1 = document.getElementById("new").value;
  const p2 = document.getElementById("confirm").value;
  if (p1 !== p2) {
    alert("Passwords do not match!");
    return false;
  }
  if (p1.length < 8) {
    alert("Password must be at least 8 characters.");
    return false;
  }
  return true;
}
</script>

</body>
</html>