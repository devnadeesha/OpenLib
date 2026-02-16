<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Profile</title>
<link rel="stylesheet" href="user_detail.css">
</head>
<body>

<div class="container">

  <!-- Profile Card -->
  <div class="profile-card">
    <img src="user.jpg" alt="User Photo">
    <h2>Nadeesha Kalhara</h2>
    <p>nadeesha@gmail.com</p>

    <div class="info">
      <div><b>Library Code:</b> LIB1023</div>
      <div><b>Fines:</b> Rs. 150.00</div>
    </div>

    <a href="../User_datails/edit_detail/edit_detail.php" class="btn">Edit Profile</a>
  </div>

  <!-- Books Section -->
  <div class="books-card">
    <h3>📚 Taken Books</h3>

    <table>
      <tr>
        <th>Book Name</th>
        <th>Borrow Date</th>
        <th>Return Date</th>
      </tr>

      <tr>
        <td>Java Programming</td>
        <td>2025-02-10</td>
        <td>2025-02-17</td>
      </tr>

      <tr>
        <td>Web Development</td>
        <td>2025-02-05</td>
        <td>2025-02-12</td>
      </tr>

    </table>
  </div>

</div>
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
