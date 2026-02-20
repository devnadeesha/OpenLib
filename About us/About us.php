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

// Get total book count
$stmt = $conn->query("SELECT COUNT(*) as total FROM books");
$total_books = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get active members count
$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
$active_members = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total users count (all time)
$stmt = $conn->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Calculate years of service (from oldest user registration or set manually)
$stmt = $conn->query("SELECT MIN(created_at) as first_date FROM users");
$first_date = $stmt->fetch(PDO::FETCH_ASSOC)['first_date'];
if ($first_date) {
    $years_service = date('Y') - date('Y', strtotime($first_date));
    // If less than 1 year, set to 1
    $years_service = max(1, $years_service);
} else {
    $years_service = 1; // Default if no users
}

// Get total borrow records (as proxy for events/activity)
$stmt = $conn->query("SELECT COUNT(*) as total FROM borrow_records WHERE YEAR(borrow_date) = YEAR(CURDATE())");
$yearly_activity = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us | OpenLib</title>
  <link rel="stylesheet" href="about us.css?v=2">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<header class="header">
  <div class="logo">Open<span>Lib</span></div>

  <nav class="navbar" id="navMenu">
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="../User_details/user_detail.php">My Profile</a>
      <a href="../Home/index.php">Home</a>
      <a href="../Borrow_books/borrow_books.php">Borrow Books</a>
      <a href="../contact/contact.php">Contact</a>
      <a href="about us.php" class="active">About Us</a>
      <a href="../Login/user_login.php" class="btn logout">Logout</a>
    <?php else: ?>
      <a href="../Home/index.php">Home</a>
      <a href="about us.php" class="active">About Us</a>
      <a href="../contact/contact.php">Contact</a>
      <a href="../Login/user_login.php" class="btn login">Login</a>
      <a href="../Register/user_register.php" class="btn signup">Sign Up</a>
    <?php endif; ?>
  </nav>

  <div class="menu-toggle" onclick="toggleMenu()">☰</div>
</header>

<!-- Hero Section -->
<section class="hero">
  <p class="small-heading">OUR STORY</p>
  <h1>About OpenLib</h1>
  <p class="description">
    For over <?php echo $years_service; ?> years, OpenLib has been the intellectual heart of our community,
    nurturing curiosity and connecting readers with the stories that shape their lives.
  </p>
</section>

<!-- Stats Section - DYNAMIC DATA -->
<section class="stats">
  <div class="card">
    <div class="card-icon">
      <i class='bx bx-book'></i>
    </div>
    <h2><?php echo number_format($total_books); ?>+</h2>
    <p>Books in Collection</p>
  </div>

  <div class="card">
    <div class="card-icon">
      <i class='bx bx-user'></i>
    </div>
    <h2><?php echo number_format($active_members); ?>+</h2>
    <p>Active Members</p>
  </div>

  <div class="card">
    <div class="card-icon">
      <i class='bx bx-calendar'></i>
    </div>
    <h2><?php echo $years_service; ?>+</h2>
    <p>Years of Service</p>
  </div>

  <div class="card">
    <div class="card-icon">
      <i class='bx bx-group'></i>
    </div>
    <h2><?php echo number_format($yearly_activity); ?>+</h2>
    <p>Books Borrowed This Year</p>
  </div>
</section>

<!-- Our Mission Section -->
<section class="mission">
  <h2>Our Mission</h2>
  <p>
    OpenLib is dedicated to empowering our community through open access
    to information, education, and culture. We provide innovative services and
    programs that inspire lifelong learning, encourage literacy, and bridge the digital divide.
  </p>
</section>

<!-- Our Values Section -->
<section class="values">
  <h2>Our Values</h2>

  <div class="value-cards">
    <div class="value-card">
      <div class="value-icon">
        <i class='bx bx-library'></i>
      </div>
      <h3>Knowledge for All</h3>
      <p>
        We believe every person deserves free access to information,
        literature, and learning resources regardless of background.
      </p>
    </div>

    <div class="value-card">
      <div class="value-icon">
        <i class='bx bx-group'></i>
      </div>
      <h3>Community First</h3>
      <p>
        Our library is more than a building. It is a gathering place where
        neighbors connect, ideas spark, and friendships form.
      </p>
    </div>

    <div class="value-card">
      <div class="value-icon">
        <i class='bx bx-star'></i>
      </div>
      <h3>Excellence in Service</h3>
      <p>
        Our dedicated staff is committed to helping you find exactly what
        you need, whether for research, education, or leisure.
      </p>
    </div>

    <div class="value-card">
      <div class="value-icon">
        <i class='bx bx-heart'></i>
      </div>
      <h3>Passion for Reading</h3>
      <p>
        We foster a lifelong love of reading through programs for all ages,
        from toddler story time to senior book clubs.
      </p>
    </div>
  </div>
</section>

<!-- Team Section -->
<section class="team">
  <h2>Meet Our Team</h2>
  <p class="team-subtitle">
    The passionate people who keep our library running and our community thriving.
  </p>

  <div class="team-container">
    <div class="team-card">
      <div class="team-image">
        <img src="../Pictures/Kalhara2.jpeg" alt="Nadeesha Kalhara">
      </div>
      <h3>Nadeesha Kalhara</h3>
      <p>Head Librarian</p>
    </div>

    <div class="team-card">
      <div class="team-image">
        <img src="../Pictures/Dayashan.jpeg" alt="Dayashan Chamuditha">
      </div>
      <h3>Dayashan Chamuditha</h3>
      <p>Collections Manager</p>
    </div>

    <div class="team-card">
      <div class="team-image">
        <img src="../Pictures/Dasun.jpeg" alt="Dasun Kavinda">
      </div>
      <h3>Dasun Kavinda</h3>
      <p>Youth Programs Director</p>
    </div>

    <div class="team-card">
      <div class="team-image">
        <img src="../Pictures/Prabasha.jpeg" alt="Prabasha Pasindu">
      </div>
      <h3>Prabasha Pasindu</h3>
      <p>Digital Services Lead</p>
    </div>

    <div class="team-card">
      <div class="team-image">
        <img src="../Pictures/ishan.jpg" alt="Ishan">
      </div>
      <h3>Ishan</h3>
      <p>Community Outreach</p>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="footer">
  <div class="footer-container">
    <div class="footer-grid">
      <div class="footer-column">
        <a href="/" class="footer-logo">OpenLib</a>
        <p class="footer-description">Your gateway to knowledge. Explore thousands of books and join our vibrant reading community.</p>
        <div class="social-links">
          <a href="#"><i class='bx bxl-facebook'></i></a>
          <a href="#"><i class='bx bxl-twitter'></i></a>
          <a href="#"><i class='bx bxl-instagram'></i></a>
          <a href="#"><i class='bx bxl-linkedin'></i></a>
        </div>
      </div>
      <div class="footer-column">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="../Home/home.php">Home</a></li>
          <li><a href="../Borrow/borrow_books.php">Browse Books</a></li>
          <li><a href="about us.php">About Us</a></li>
          <li><a href="../contact/contact.php">Contact</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Account</h3>
        <ul>
          <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="../Profile/user_profile.php">My Profile</a></li>
            <li><a href="../Borrow/borrow_books.php">Borrow Books</a></li>
            <li><a href="../Login/user_login.php">Logout</a></li>
          <?php else: ?>
            <li><a href="../Login/user_login.php">Log In</a></li>
            <li><a href="../Register/user_register.php">Sign Up</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Library Hours</h3>
        <ul>
          <li><i class='bx bx-time'></i> Mon - Fri: 8:00 AM - 9:00 PM</li>
          <li><i class='bx bx-time'></i> Saturday: 9:00 AM - 6:00 PM</li>
          <li><i class='bx bx-time'></i> Sunday: 10:00 AM - 5:00 PM</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2026 OpenLib Library. All rights reserved.</p>
    </div>
  </div>
</footer>

<script>
function toggleMenu() {
  const navMenu = document.getElementById('navMenu');
  navMenu.classList.toggle('active');
}

// Counter animation for stats
document.addEventListener('DOMContentLoaded', function() {
  const stats = document.querySelectorAll('.stats .card h2');
  
  const animateCounter = (element) => {
    const target = parseInt(element.textContent.replace(/[^0-9]/g, ''));
    const duration = 2000;
    const increment = target / (duration / 16);
    let current = 0;
    
    const updateCounter = () => {
      current += increment;
      if (current < target) {
        element.textContent = Math.floor(current).toLocaleString() + '+';
        requestAnimationFrame(updateCounter);
      } else {
        element.textContent = target.toLocaleString() + '+';
      }
    };
    
    updateCounter();
  };
  
  // Intersection Observer to trigger animation when stats come into view
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });
  
  stats.forEach(stat => observer.observe(stat));
});
</script>
</body>
</html>