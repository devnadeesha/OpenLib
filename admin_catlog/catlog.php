<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Catalog - OpenLib</title>
  <link rel="stylesheet" href="../Books_section/assets/dashboard.css">
  <link rel="stylesheet" href="../admin_catlog/assets/view-catalog.css">
</head>
<body>
    <!--Header eka-->
 <header class="header">
  <div class="logo">Open<span>Lib</span></div>

  <nav class="navbar" id="navMenu">
    <a href="../Admin_dashboard/dashboard.php">Dashboard</a>
    <a href="../Home/index.php">Home</a>
    <a href="./catlog.php">Catalog</a>
    <a href="../Contact us/Contact.html">Contact</a>
    <a href="../About us/About us.php">About Us</a>
    

    <a href="../Login/user_login.php" class="btn login">Logout</a>
    
  </nav>

  <div class="menu-toggle" onclick="toggleMenu()">☰</div>
</header>
  <!-- Main Content -->
  <main class="catalog-container">
    <!-- Page Title -->
    <div class="catalog-header">
      <h1>Library Catalog</h1>
      <p>Explore our collection of thousands of books</p>
    </div>

    <!-- Search and Filter Section -->
    <div class="catalog-controls">
      <div class="search-section">
        <input 
          type="text" 
          id="searchInput" 
          class="search-input" 
          placeholder="Search by title, author, or ISBN..."
          onkeyup="filterCatalog()"
        >
      </div>

      <div class="filter-section">
        <select id="genreFilter" class="filter-select" onchange="filterCatalog()">
          <option value="">All Genres</option>
          <option value="Fiction">Fiction</option>
          <option value="Science">Science</option>
          <option value="Fantasy">Fantasy</option>
          <option value="Romance">Romance</option>
          <option value="Mystery">Mystery</option>
          <option value="Self-Help">Self-Help</option>
          <option value="Biography">Biography</option>
          <option value="History">History</option>
          <option value="Technology">Technology</option>
          <option value="Education">Education</option>
        </select>

        <select id="quantityFilter" class="filter-select" onchange="filterCatalog()">
          <option value="">All Books</option>
          <option value="available">In Stock Only</option>
          <option value="all">Including Out of Stock</option>
        </select>
      </div>

      <div class="view-toggle">
        <button class="toggle-btn active" data-view="grid" onclick="toggleView('grid')">Grid View</button>
        <button class="toggle-btn" data-view="list" onclick="toggleView('list')">List View</button>
      </div>
    </div>

    <!-- Results Count -->
    <div class="results-info">
      <p id="resultsCount">Showing all books</p>
    </div>

    <!-- Books Grid -->
    <div class="books-grid" id="booksGrid">
      <!-- Books will be loaded here by JavaScript -->
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="empty-state" style="display: none;">
      <p>No books found matching your criteria.</p>
      <button class="btn-reset" onclick="resetFilters()">Reset Filters</button>
    </div>
  </main>

<!--footer --eka-->
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

  <script src="../admin_catlog/assets/view-catalog.js"></script>
</body>
</html>

