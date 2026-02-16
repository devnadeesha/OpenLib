<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | OpenLib</title>
    <link rel="stylesheet" href="user_detail.css">
</head>
<body>
    <!-- header -->
    <header class="header">
        <div class="logo">Open<span>Lib</span></div>

            <nav class="navbar" id="navMenu">
                <a href="../dashboard/dashboard.html">Dashboard</a>
                <a href="../Home/Main.html">Home</a>
                <a href="#">Catalog</a>
                <a href="../contact us/contact.html">Contact</a>
                <a href="../Abou_us/about_us.html">About Us</a>
                

                <a href="../Login/user_login.php" class="btn login">Login</a>
                <a href="../Register/register.php" class="btn signup">Sign Up</a>
            </nav>

        <div class="menu-toggle" onclick="toggleMenu()">☰</div>
    </header>

  <p>test</p>
    <!-- Footer -->
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
                        <li><a href="../Dashboard/dashboard.html">Dashboard</a></li>    
                        <li><a href="../Home/Main.html">Home</a></li>
                        <li><a href="#">Book Catalog</a></li>
                        <li><a href="../contact us/contact.html">Contact</a></li>
                        <li><a href="../Abou_us/about_us.html">About Us</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Account</h3>
                    <ul>
                        <li><a href="../Login/user_login.php">Log In</a></li>
                        <li><a href="../Register/register.php">Sign Up</a></li>
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


    <script src="script.js"></script>
</body>
</html>