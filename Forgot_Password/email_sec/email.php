<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="../email_sec/email.css">
</head>
<body>

<div class="wrapper">
  <?php
session_start();
require_once '../../db_connect.php'; // Adjust path to your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    // Check if email exists in database
    $sql = "SELECT user_id, email FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        // Email exists
        $user = $result->fetch_assoc();
        $_SESSION['reset_email'] = $user['email'];
        echo "ok";
        header("Location: ../reset_sec/reset.php");
    } else {
        // Email not found
        echo "not_found";
    }
    
    $stmt->close();
    $conn->close();
}
?>
  <form action="email.php" method="POST" id="forgotForm">
    <h1>Forgot Password</h1>

    
    <div class="input-box"> 
      <input type="email" id="email" name="email" placeholder="Enter your email" required autocomplete="off">
    </div> 
    <button type="submit" class="btn">Continue</button><br><br>
    <p id="msg"></p>
</form>

</div>
<!-- <script>
    document.getElementById("forgotForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let email = document.getElementById("email").value;
    let msg = document.getElementById("msg");

    fetch("email.php", {
        method: "POST",
        headers: {
        "Content-Type": "application/x-www-form-urlencoded"
        },
        body: "email=" + encodeURIComponent(email)
    })
    .then(res => res.text())
    .then(data => {
        if (data === "ok") {
        window.location.href = "reset.php?email=" + email;
        } else {
        msg.style.color = "red";
        msg.innerHTML = "❌ This email is not registered!";
        }
    });
    });
</script> -->




</body>
</html>
