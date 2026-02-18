<?php
session_start();

// ── DB CONNECTION (built in) ──────────────────────────────────────────────────
$conn = new mysqli('localhost', 'root', '', 'openlib');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ── HANDLE FORM SUBMIT ────────────────────────────────────────────────────────
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT user_id, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['reset_email'] = $user['email'];
        $stmt->close();
        $conn->close();
        header("Location: ../reset_sec/reset.php");
        exit();
    } else {
        $error = "❌ This email is not registered.";
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password | OpenLib</title>
  <link rel="stylesheet" href="../email_sec/email.css?v=2">
</head>
<body>

<div class="wrapper">
  <form action="email.php" method="POST">
    <h1>Forgot Password</h1>

    <?php if (!empty($error)): ?>
      <p style="color:red; margin-bottom:12px; font-size:14px;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <div class="input-box">
      <input type="email" name="email" placeholder="Enter your email" required autocomplete="off">
    </div>

    <button type="submit" class="btn">Continue</button>
  </form>
</div>

</body>
</html>