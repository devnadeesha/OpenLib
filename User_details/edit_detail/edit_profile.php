<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'openlib';
$username = 'root';
$password = '';

// Create PDO connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // For testing, set a default user
    $_SESSION['user_id'] = 1; // Change this in production
    // Uncomment below for production:
    // header("Location: ../Login/user_login.php");
    // exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $errors[] = "First name, last name, and email are required.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email is already in use by another account.";
    }
    
    // Password validation (if changing password)
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to set a new password.";
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect.";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long.";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "New password and confirm password do not match.";
        }
    }
    
    // Update user if no errors
    if (empty($errors)) {
        try {
            if (!empty($new_password)) {
                // Update with new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, password = ? WHERE user_id = ?");
                $stmt->execute([$first_name, $last_name, $email, $phone, $address, $hashed_password, $user_id]);
            } else {
                // Update without changing password
                $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE user_id = ?");
                $stmt->execute([$first_name, $last_name, $email, $phone, $address, $user_id]);
            }
            
            $success = "Profile updated successfully!";
            
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            $errors[] = "Error updating profile: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Profile | OpenLib</title>
  <link rel="stylesheet" href="edit_profile.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<!-- HEADER -->
<header class="header">
  <div class="logo">Open<span>Lib</span></div>

    <nav class="navbar" id="navMenu">
                <a href="../../User_details/user_detail.php">User Profile</a>
                <a href="../../Home/index.php">Home</a>
                <a href="../../User_catalog/user_catalog.php">Catalog</a>
                <a href="../../contact/contact.php">Contact</a>
                <a href="../../About us/About us.php">About Us</a>
                

                <a href="../../Login/user_login.php" class="btn logout">Logout</a>
               
    </nav>


  <div class="menu-toggle" onclick="toggleMenu()">☰</div>
</header>

<!-- MAIN CONTAINER -->
<div class="container">
  
  <div class="page-header">
    <div>
      <h1><i class='bx bx-edit'></i> Edit Profile</h1>
      <p>Update your personal information</p>
    </div>
    <a href="../user_detail.php" class="btn-back">
      <i class='bx bx-arrow-back'></i> Back to Profile
    </a>
  </div>

  <div class="edit-container">
    
    <!-- Profile Picture Section -->
    <div class="photo-card">
      <h3><i class='bx bx-image'></i> Profile Picture</h3>
      
      <div class="photo-section">
        <div class="profile-image-wrapper">
          <?php if (!empty($user['profile_picture']) && file_exists('uploads/' . $user['profile_picture'])): ?>
            <img src="'uploads/'<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" id="profilePreview">
          <?php else: ?>
            <div class="default-avatar" id="profilePreview">
              <i class='bx bx-user'></i>
            </div>
          <?php endif; ?>
        </div>
        
        <form method="POST" action="../upload_profile_picture.php" enctype="multipart/form-data" id="photoForm">
          <label class="upload-btn">
            <i class='bx bx-camera'></i> Change Photo
            <input type="file" name="profile_picture" accept="image/*" hidden onchange="previewImage(event)">
          </label>
          <p class="photo-note">JPG, PNG or GIF. Max size 5MB</p>
        </form>
      </div>
    </div>

    <!-- Edit Form -->
    <div class="form-card">
      <h3><i class='bx bx-user-circle'></i> Personal Information</h3>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success">
          <i class='bx bx-check-circle'></i> <?php echo htmlspecialchars($success); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <i class='bx bx-error'></i>
          <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="edit_profile.php">
        
        <div class="form-row">
          <div class="form-group">
            <label><i class='bx bx-user'></i> First Name *</label>
            <input type="text" name="first_name" placeholder="Enter first name" 
                   value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
          </div>

          <div class="form-group">
            <label><i class='bx bx-user'></i> Last Name *</label>
            <input type="text" name="last_name" placeholder="Enter last name" 
                   value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label><i class='bx bx-envelope'></i> Email Address *</label>
          <input type="email" name="email" placeholder="Enter email address" 
                 value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="form-group">
          <label><i class='bx bx-phone'></i> Phone Number</label>
          <input type="tel" name="phone" placeholder="Enter phone number" 
                 value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label><i class='bx bx-map'></i> Address</label>
          <textarea name="address" placeholder="Enter your address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
        </div>

        <div class="divider">
          <span>Change Password (Optional)</span>
        </div>

        <div class="form-group">
          <label><i class='bx bx-lock'></i> Current Password</label>
          <div class="password-input">
            <input type="password" name="current_password" id="currentPassword" placeholder="Enter current password">
            <button type="button" class="toggle-password" onclick="togglePassword('currentPassword')">
              <i class='bx bx-show' id="currentPasswordIcon"></i>
            </button>
          </div>
          <small>Required only if you want to change your password</small>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label><i class='bx bx-lock-alt'></i> New Password</label>
            <div class="password-input">
              <input type="password" name="new_password" id="newPassword" placeholder="Enter new password">
              <button type="button" class="toggle-password" onclick="togglePassword('newPassword')">
                <i class='bx bx-show' id="newPasswordIcon"></i>
              </button>
            </div>
            <small>Minimum 6 characters</small>
          </div>

          <div class="form-group">
            <label><i class='bx bx-lock-open'></i> Confirm New Password</label>
            <div class="password-input">
              <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm new password">
              <button type="button" class="toggle-password" onclick="togglePassword('confirmPassword')">
                <i class='bx bx-show' id="confirmPasswordIcon"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-save">
            <i class='bx bx-save'></i> Save Changes
          </button>
          <a href="../user_detail.php" class="btn-cancel">
            <i class='bx bx-x'></i> Cancel
          </a>
        </div>
      </form>
    </div>

  </div>
</div>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-container">
    <div class="footer-grid">
      <div class="footer-column">
        <a href="/" class="footer-logo">OpenLib</a>
        <p class="footer-description">Your gateway to knowledge. Explore thousands of books and join our vibrant reading community.</p>
      </div>
      <div class="footer-column">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="../dashboard/dashboard.html">Dashboard</a></li>    
          <li><a href="../Home/Main.html">Home</a></li>
          <li><a href="../Borrow/borrow_books.php">Borrow Books</a></li>
          <li><a href="../contact us/contact.html">Contact</a></li>
          <li><a href="../Abou_us/about_us.html">About Us</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Account</h3>
        <ul>
          <li><a href="../Profile/user_profile.php">My Profile</a></li>
          <li><a href="edit_profile.php">Edit Profile</a></li>
          <li><a href="../../Login/user_login.php">Logout</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Library Hours</h3>
        <ul>
          <li>Mon - Fri: 8:00 AM - 9:00 PM</li>
          <li>Saturday: 9:00 AM - 6:00 PM</li>
          <li>Sunday: 10:00 AM - 5:00 PM</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>PageTurn Library. All rights reserved.</p>
    </div>
  </div>
</footer>

<script src="edit_profile.js"></script>
<script>
function toggleMenu() {
  const navMenu = document.getElementById('navMenu');
  navMenu.classList.toggle('active');
}
</script>
</body>
</html>