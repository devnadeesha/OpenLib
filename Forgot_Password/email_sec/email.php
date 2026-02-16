<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
  <link rel="stylesheet" href="../email_sec/email.css">
</head>
<body>

<div class="wrapper">
  <form action="email.php" method="POST" id="forgotForm">
    <h1>Forgot Password</h1>

    
    <div class="input-box"> 
      <input type="email" id="email" name="email" placeholder="Enter your email" required autocomplete="off">
    </div> 
    <button type="submit" class="btn">Continue</button><br><br>
    <p id="msg"></p>
</form>

</div>
<script>
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
</script>




</body>
</html>
