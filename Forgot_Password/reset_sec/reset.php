<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <link rel="stylesheet" href="reset.css">
</head>
<body>

<div class="wrapper">
  <form action="reset_password.php" method="POST" onsubmit="return checkPassword()">
    <h1>Reset Password</h1>

    <input type="hidden" name="email" value="<?php echo $_GET['email']; ?>">

    <div class="input-box">
      <input type="password" id="new" name="new_pass" placeholder="New Password" required>
    </div>

    <div class="input-box">
      <input type="password" id="confirm" name="confirm_pass" placeholder="Confirm Password" required>
    </div>

    <button type="submit" class="btn">Update Password</button>
  </form>
</div>

<script>
function checkPassword(){
  let p1 = document.getElementById("new").value;
  let p2 = document.getElementById("confirm").value;

  if(p1 !== p2){
    alert("Passwords do not match!");
    return false;
  }
  return true;
}
</script>

</body>
</html>
