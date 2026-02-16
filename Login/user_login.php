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
    
    <form action="user_login.php" method="POST">
      <h1>Login</h1>
      <div class="input-box">
        <input type="text" placeholder="Enter your Email" required name="email" autocomplete="off">
        <i class='bx bxs-user'></i>
      </div>
      <div class="input-box">
        <input type="password" placeholder="Password" required name="password" autocomplete="new-password">
        <i class='bx bxs-lock-alt' ></i>
      </div>
      <div class="remember-forgot">
        <label><input type="checkbox" name="Checkbox">Remember Me</label>
        <a href="../forgot_password/email_section/email.html">Forgot Password</a>
      </div>
      <button type="submit" class="btn" name="login">Login</button>
      <div class="register-link">
        <p>Dont have an account? <a href="../Register/register.php">Register</a></p>
      </div>
    </form>
  </div>
</body>
</html>