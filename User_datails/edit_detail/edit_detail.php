<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile</title>
<link rel="stylesheet" href="edit_detail.css">
</head>
<body>

<div class="edit-container">

  <div class="edit-card">

    <h2>Edit Profile</h2>

    <div class="photo-section">
      <img src="user.jpg" >
      <label class="upload-btn">
        Change Photo
        <input type="file" hidden>
      </label>
    </div>

    <form>

      <div class="form-group">
        <label>Full Name</label>
        <input type="text" placeholder="Enter your name" value="Nadeesha Kalhara">
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" placeholder="Enter your email" value="nadeesha@gmail.com">
      </div>

      <div class="form-group">
        <label>Library Code</label>
        <input type="text" value="LIB1023" readonly>
      </div>

      <div class="form-group">
        <label>New Password</label>
        <input type="password" placeholder="Enter new password">
      </div>

      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" placeholder="Confirm new password">
      </div>

      <button class="save-btn">Save Changes</button>

      <a href="../user_detail.php" class="back-btn">← Back to Profile</a>

    </form>

  </div>

</div>

</body>
</html>
