<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | OpenLib</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<header class="header">
  <div class="logo">Open<span>Lib</span></div>

  <nav class="navbar" id="navMenu">
    <a href="../dashboard/dashboard.html">Dashboard</a>
    <a href="../Home/Main.html">Home</a>
    <a href="#">Catalog</a>
    <a href="../contact us/contact.html">Contact</a>
    <a href="../Abou_us/about_us.html">About Us</a>
    

    <a href="../Login/user_login.php" class="btn login">Login</a>
    <a href="../Register/user_register.html" class="btn signup">Sign Up</a>
  </nav>

  <div class="menu-toggle" onclick="toggleMenu()">☰</div>
</header>

<section class="dashboard">
  <div class="dash-container">

    <h1 class="dash-title">Dashboard</h1>

    <div class="stats-grid">
      <div class="stat-card">
        <h3>Total Books</h3>
        <p>1,250</p>
      </div>

      <div class="stat-card">
        <h3>Members</h3>
        <p>340</p>
      </div>

      <div class="stat-card">
        <h3>Borrowed</h3>
        <p>180</p>
      </div>

      <div class="stat-card">
        <h3>Available</h3>
        <p>1,070</p>
      </div>
    </div>

    <div class="dashboard-grid">

      <div class="dashboard-card">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
          <a href="#" class="btn">Add New Book</a>
          <a href="#" class="btn">View Catalog</a>
          <a href="#" class="btn">Manage Users</a>
          <a href="#" class="btn">Borrow History</a>
        </div>
      </div>

      <div class="dashboard-card">
        <h2>Recent Activities</h2>
        <ul class="activity-list">
          <li>📖 "Clean Code" borrowed by Nadeesha</li>
          <li>📘 "Python Basics" returned</li>
          <li>👤 New user registered</li>
          <li>📚 New book added</li>
        </ul>
      </div>

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
                        <li><a href="#">Book Catalog</a></li>
                        <li><a href="../contact us/contact.html">Contact</a></li>
                        <li><a href="../Abou_us/about_us.html">About Us</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Account</h3>
                    <ul>
                        <li><a href="../Login page/user_login.html">Log In</a></li>
                        <li><a href="../Register/user_register.html">Sign Up</a></li>
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
</body>
</html>
