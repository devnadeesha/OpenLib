<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | OpenLib</title>
    <link rel="stylesheet" href="index.css">
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
    <a href="../Register/register.php" class="btn signup">Sign Up</a>
  </nav>

        <div class="menu-toggle" onclick="toggleMenu()">☰</div>
     </header>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <p class="hero-subtitle">Welcome to OpenLib Library</p>
            <h1 class="hero-title">Discover Your Next Great Read</h1>
            <p class="hero-description">Explore thousands of books across every genre — from timeless classics to the latest releases.</p>
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search" class="search-input">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Choose PageTurn</h2>
            <p class="section-subtitle">More than just a library — we are a community dedicated to the love of reading.</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <h3>Vast Collection</h3>
                    <p>Over 50,000 titles spanning fiction, non-fiction, academic texts, and rare editions.</p>
                </div>
                <div class="feature-card">
                    <h3>Community Events</h3>
                    <p>Join book clubs, author readings, and workshops that bring readers together.</p>
                </div>
                <div class="feature-card">
                    <h3>Flexible Hours</h3>
                    <p>Open seven days a week with extended evening hours for your convenience.</p>
                </div>
                <div class="feature-card">
                    <h3>Digital Access</h3>
                    <p>Borrow e-books and audiobooks from anywhere with your membership card.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Books Section -->
    <section class="popular-books">
        <div class="container">
            <h2 class="section-title">Popular This Month</h2>
            <p class="section-subtitle">See what our readers are loving right now.</p>
            
            <div class="books-grid">
                <a href="/catalog" class="book-card">
                    <img src="https://jumpbooks.lk/wp-content/uploads/2017/06/How-to-Talk-to-Anyone-530x800.jpeg " alt="How to Talk to Anyone">
                    <div class="book-info">
                        <span class="book-genre">Classic</span>
                        <h3 class="book-title">How to Talk to Anyone</h3>
                        <p class="book-author">Leil Lowndes</p>
                    </div>
                </a>
                <a href="/catalog" class="book-card">
                    <img src="https://jumpbooks.lk/wp-content/uploads/2024/04/The-Art-of-Being-Alone-1-521x800.jpg" alt="The Art of Being Alone:">
                    <div class="book-info">
                        <span class="book-genre">Self-Help</span>
                        <h3 class="book-title">The Art of Being Alone</h3>
                        <p class="book-author">Renuka Gavrani</p>
                    </div>
                </a>
                <a href="/catalog" class="book-card">
                    <img src="https://jumpbooks.lk/wp-content/uploads/2017/03/The-Subtle-Art-of-Not-Giving-a-F_ck-A-Counterintuitive-Approach-to-Living-a-Good-Life-533x800.jpg" alt="The Subtle Art of Not Giving a F*ck">
                    <div class="book-info">
                        <span class="book-genre">History</span>
                        <h3 class="book-title">The Subtle Art of Not Giving a F*ck</h3>
                        <p class="book-author">Mark Manson</p>
                    </div>
                </a>
                <a href="/catalog" class="book-card">
                    <img src="https://jumpbooks.lk/wp-content/uploads/2017/03/How-to-Win-Friends-and-Influence-People-Vermilion.jpg " alt="How to Win Friends and Influence People">
                    <div class="book-info">
                        <span class="book-genre">Fiction</span>
                        <h3 class="book-title">How to Win Friends and Influence People</h3>
                        <p class="book-author">Dale Carnegie</p>
                    </div>
                </a>
            </div>
            
            <div class="text-center">
                <a href="/catalog" class="btn btn-primary">View all books</a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2 class="cta-title">Ready to Start Reading?</h2>
            <p class="cta-description">Create a free account today and gain access to our entire catalog. Borrow books, save favorites, and join our reading community.</p>
            <div class="cta-buttons">
                <a href="/register" class="btn btn-primary">Create Free Account</a>
                <a href="/catalog" class="btn btn-outline">Browse Catalog</a>
            </div>
        </div>
    </section>

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
