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

// Fetch popular books (most borrowed books this month)
$stmt = $conn->query("
    SELECT b.*, COUNT(br.id) as borrow_count
    FROM books b
    LEFT JOIN borrow_records br ON b.id = br.book_id 
        AND MONTH(br.borrow_date) = MONTH(CURDATE()) 
        AND YEAR(br.borrow_date) = YEAR(CURDATE())
    GROUP BY b.id
    ORDER BY borrow_count DESC, b.added_date DESC
    LIMIT 8
");
$popular_books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total book count
$stmt = $conn->query("SELECT COUNT(*) as total FROM books");
$total_books = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get active members count
$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
$active_members = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total genres count
$stmt = $conn->query("SELECT COUNT(DISTINCT genre) as total FROM books");
$total_genres = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | OpenLib</title>
    <link rel="stylesheet" href="index.css?v=2">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">Open<span>Lib</span></div>

        <nav class="navbar" id="navMenu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="User_details/user_detail.php">My Profile</a>
                <a href="index.php" class="active">Home</a>
                <a href="Borrow_books/borrow_books.php">Borrow Books</a>
                <a href="contact/contact.php">Contact</a>
                <a href="About us/About us.php">About Us</a>
                <a href="Login/user_login.php" class="btn logout">Logout</a>
            <?php else: ?>
                <a href="index.php" class="active">Home</a>
                <a href="About/about_us.php">About Us</a>
                <a href="contact/contact.php">Contact</a>
                <a href="Login/user_login.php" class="btn login">Login</a>
                <a href="Register/user_register.php" class="btn signup">Sign Up</a>
            <?php endif; ?>
        </nav>

        <div class="menu-toggle" onclick="toggleMenu()">☰</div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <p class="hero-subtitle">Welcome to OpenLib Library</p>
            <h1 class="hero-title">Discover Your Next Great Read</h1>
            <p class="hero-description">Explore thousands of books across every genre — from timeless classics to the latest releases.</p>
            <div class="search-container">
                <i class='bx bx-search'></i>
                <input type="text" id="searchInput" placeholder="Search for books, authors, or ISBN..." class="search-input">
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <i class='bx bx-book'></i>
                    <div>
                        <h3><?php echo number_format($total_books); ?>+</h3>
                        <p>Books Available</p>
                    </div>
                </div>
                <div class="stat-item">
                    <i class='bx bx-user'></i>
                    <div>
                        <h3><?php echo number_format($active_members); ?>+</h3>
                        <p>Active Members</p>
                    </div>
                </div>
                <div class="stat-item">
                    <i class='bx bx-category'></i>
                    <div>
                        <h3><?php echo number_format($total_genres); ?>+</h3>
                        <p>Genres</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Choose OpenLib</h2>
            <p class="section-subtitle">More than just a library — we are a community dedicated to the love of reading.</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class='bx bx-library'></i>
                    </div>
                    <h3>Vast Collection</h3>
                    <p>Over <?php echo number_format($total_books); ?> titles spanning fiction, non-fiction, academic texts, and rare editions.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class='bx bx-group'></i>
                    </div>
                    <h3>Community Events</h3>
                    <p>Join book clubs, author readings, and workshops that bring readers together.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class='bx bx-time'></i>
                    </div>
                    <h3>Flexible Hours</h3>
                    <p>Open seven days a week with extended evening hours for your convenience.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class='bx bx-mobile'></i>
                    </div>
                    <h3>Digital Access</h3>
                    <p>Borrow e-books and manage your account online from anywhere with your membership.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Books Section -->
    <section class="popular-books">
        <div class="container">
            <h2 class="section-title">Popular This Month</h2>
            <p class="section-subtitle">See what our readers are loving right now.</p>
            
            <?php if (empty($popular_books)): ?>
                <div class="no-books">
                    <i class='bx bx-book'></i>
                    <p>No books available at the moment. Check back soon!</p>
                </div>
            <?php else: ?>
                <div class="books-grid">
                    <?php foreach ($popular_books as $book): ?>
                    <div class="book-card">
                        <div class="book-image">
                            <?php if (!empty($book['cover_image']) && file_exists('Manage_books/'.$book['cover_image'])): ?>
                            <img src="<?php echo htmlspecialchars('Manage_books/'.$book['cover_image']); ?>">
                                <div class="no-image">
                                    <i class='bx bx-book'></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($book['available_quantity'] > 0): ?>
                                <span class="availability-badge available">
                                    <i class='bx bx-check-circle'></i> Available
                                </span>
                            <?php else: ?>
                                <span class="availability-badge unavailable">
                                    <i class='bx bx-x-circle'></i> Unavailable
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="book-info">
                            <span class="book-genre"><?php echo htmlspecialchars($book['genre']); ?></span>
                            <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                            <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                            
                            <div class="book-meta">
                                <span><i class='bx bx-calendar'></i> <?php echo htmlspecialchars($book['publication_year']); ?></span>
                                <span><i class='bx bx-book-open'></i> <?php echo htmlspecialchars($book['pages']); ?> pages</span>
                            </div>
                            
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php if ($book['available_quantity'] > 0): ?>
                                    <a href="Borrow/borrow_books.php?search=<?php echo urlencode($book['title']); ?>" class="btn-borrow">
                                        <i class='bx bx-book-add'></i> Borrow Now
                                    </a>
                                <?php else: ?>
                                    <button class="btn-unavailable" disabled>
                                        <i class='bx bx-error'></i> Not Available
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="Login/user_login.php" class="btn-login">
                                    <i class='bx bx-log-in'></i> Login to Borrow
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="text-center">
                <a href="View book/view_book.php" class="btn btn-primary">
                    <i class='bx bx-library'></i> View All Books
                </a>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Start Your Reading Journey?</h2>
                <p>Join <?php echo number_format($active_members); ?>+ book lovers and get access to our extensive collection today!</p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="Register/user_register.php" class="btn btn-cta">
                        <i class='bx bx-user-plus'></i> Sign Up Now
                    </a>
                <?php else: ?>
                    <a href="View book/view_book.php" class="btn btn-cta">
                        <i class='bx bx-book-add'></i> Browse Books
                    </a>
                <?php endif; ?>
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
                        <li><a href="home.php">Home</a></li>
                        <li><a href="View book/view_book.php">Browse Books</a></li>
                        <li><a href="About us/About us.php">About Us</a></li>
                        <li><a href="contact/contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Account</h3>
                    <ul>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="User_details/user_detail.php">My Profile</a></li>
                            <li><a href="Borrow_books/borrow_books.php">Borrow Books</a></li>
                            <li><a href="Login/user_login.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="Login/user_login.php">Log In</a></li>
                            <li><a href="Register/user_register.php">Sign Up</a></li>
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

    <script src="index.js"></script>
</body>
</html>