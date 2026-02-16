<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register Form</title>
  <link rel="stylesheet" href="user_register.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

  <div class="wrapper">
  

    <form action="user_register.php" method="POST" autocomplete="off" onsubmit=" return checkPassword()" >
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
        <p>Already have an account? <a href="../Login page/user_login.html">Login</a></p>
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
