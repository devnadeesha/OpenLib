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

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required.";
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'unread')");
            $stmt->execute([$name, $email, $subject, $message]);
            
            $success = "Thank you for contacting us! We will get back to you soon.";
            
            // Clear form fields after successful submission
            $_POST = [];
            
        } catch(PDOException $e) {
            $errors[] = "Error sending message. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact | OpenLib</title>
  <link rel="stylesheet" href="contact.css?v=2">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<header class="header">
  <div class="logo">Open<span>Lib</span></div>

  <nav class="navbar" id="navMenu">
    <a href="../User_details/user_detail.php">My Profile</a>
    <a href="../Home/index.php">Home</a>
    <a href="../Borrow_books/borrow_books.php">Borrow Books</a>
    <a href="../contact/contact.php" class="active">Contact</a>
    <a href="../About us/About us.php">About Us</a>
    <a href="../Login/user_login.php" class="btn logout">Logout</a>
  </nav>
  
  <div class="menu-toggle" onclick="toggleMenu()">☰</div>
</header>

<section class="form-contact">

  <div class="contact-container">
    <h2>Send Us a Message</h2>
    <p>We would love to hear from you!</p>

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

    <form method="POST" action="contact.php" id="contactForm">
      <div class="input-group">
        <label>Name *</label>
        <input type="text" name="name" placeholder="Enter your name" 
               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
      </div>

      <div class="input-group">
        <label>Email *</label>
        <input type="email" name="email" placeholder="Enter your email" 
               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
      </div>

      <div class="input-group">
        <label>Subject (Optional)</label>
        <input type="text" name="subject" placeholder="Enter subject" 
               value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
      </div>

      <div class="input-group">
        <label>Message *</label>
        <textarea name="message" rows="4" placeholder="Type your message" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
      </div>

      <button type="submit" class="btn-msg">
        <i class='bx bx-send'></i> Send Message
      </button>
    </form>
  </div>
  
  <br>
  
  <div class="contact-container-info">
    <div class="group-content-info">
      <h2>Library Information</h2><br><br><br>
      <h3><i class='bx bx-map'></i> Address:</h3><br>
      <p>NSBM Green University, Homagama, Colombo, Sri Lanka</p><br><br>
    </div>

    <div class="group-content-info">
      <h3><i class='bx bx-envelope'></i> Email:</h3><br>
      <p>openlib@gmail.com</p><br><br>
    </div>

    <div class="group-content-info">
      <h3><i class='bx bx-phone'></i> Contact No:</h3><br>  
      <p>+9470 346 6781</p><br><br>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container">
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
          <li><a href="../Borrow_books/borrow_books.php">Borrow Books</a></li>
          <li><a href="../contact/contact.php">Contact</a></li>
          <li><a href="../About us/about_us.php">About Us</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Account</h3>
        <ul>
          <li><a href="../Login/user_login.php">Log In</a></li>
          <li><a href="../Register/user_register.php">Sign Up</a></li>
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

<script src="contact.js"></script>
<script>
function toggleMenu() {
  const navMenu = document.getElementById('navMenu');
  navMenu.classList.toggle('active');
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.3s';
      setTimeout(() => {
        alert.style.display = 'none';
      }, 300);
    }, 5000);
  });
});

// Form validation
document.getElementById('contactForm').addEventListener('submit', function(e) {
  const name = this.querySelector('[name="name"]').value.trim();
  const email = this.querySelector('[name="email"]').value.trim();
  const message = this.querySelector('[name="message"]').value.trim();
  
  if (!name || !email || !message) {
    e.preventDefault();
    alert('Please fill in all required fields.');
    return false;
  }
  
  // Simple email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    e.preventDefault();
    alert('Please enter a valid email address.');
    return false;
  }
});
</script>
</body>
</html>