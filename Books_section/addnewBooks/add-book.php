<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Book | OpenLib</title>
  <link rel="stylesheet" href="../assets/add-book.css">
  <link rel="stylesheet" href="../assets/dashboard.css">
</head>
<body>

<header class="header">
  <div class="logo">Open<span>Lib</span></div>

  <nav class="navbar" id="navMenu">
    <a href="../../Admin_dashboard/dashboard.php">Dashboard</a>
    <a href="../../Home/index.php">Home</a>
    <a href="../../admin_catlog/catlog.php">Catalog</a>
    <a href="../../contact/contact.php">Contact</a>
    <a href="../../About us/About us.php">About Us</a>
    
    <a href="../../Login/user_login.php" class="btn login">Login</a>
    <a href="../../Register/register.php" class="btn signup">Sign Up</a>
  </nav>

  <div class="menu-toggle" onclick="toggleMenu()">☰</div>
</header>

<section class="add-book-section">
  <div class="add-book-container">
    <h1 class="page-title">Add New Book</h1>
    
    <div class="form-wrapper">
     <form id="bookForm"
      class="book-form"
      method="POST"
      enctype="multipart/form-data"
      onsubmit="handleAddBook(event)">

        
        <div class="form-row">
          <div class="form-group">
            <label for="bookTitle">Book Title *</label>
            <input type="text" id="bookTitle" name="title" placeholder="Enter book title" required>
            <span class="error-message" id="titleError"></span>
          </div>

          <div class="form-group">
            <label for="bookAuthor">Author *</label>
            <input type="text" id="bookAuthor" name="author" placeholder="Enter author name" required>
            <span class="error-message" id="authorError"></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="bookISBN">ISBN *</label>
            <input type="text" id="bookISBN" name="isbn" placeholder="e.g., 978-0-13-110362-7" required>
            <span class="error-message" id="isbnError"></span>
          </div>

          <div class="form-group">
            <label for="bookGenre">Genre *</label>
            <select id="bookGenre" name="genre" required>
              <option value="">Select a genre</option>
              <option value="Fiction">Fiction</option>
              <option value="Non-Fiction">Non-Fiction</option>
              <option value="Science">Science</option>
              <option value="Technology">Technology</option>
              <option value="History">History</option>
              <option value="Biography">Biography</option>
              <option value="Romance">Romance</option>
              <option value="Mystery">Mystery</option>
              <option value="Fantasy">Fantasy</option>
              <option value="Self-Help">Self-Help</option>
            </select>
            <span class="error-message" id="genreError"></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="bookYear">Publication Year *</label>
            <input type="number" id="bookYear" name="year" placeholder="2024" min="1000" max="9999" required>
            <span class="error-message" id="yearError"></span>
          </div>

          <div class="form-group">
            <label for="bookPages">Number of Pages *</label>
            <input type="number" id="bookPages" name="pages" placeholder="300" min="1" required>
            <span class="error-message" id="pagesError"></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="bookPublisher">Publisher</label>
            <input type="text" id="bookPublisher" name="publisher" placeholder="Enter publisher name">
          </div>

          <div class="form-group">
            <label for="bookQuantity">Quantity Available *</label>
            <input type="number" id="bookQuantity" name="quantity" placeholder="5" min="1" required>
            <span class="error-message" id="quantityError"></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group full-width">
            <label for="bookDescription">Description</label>
            <textarea id="bookDescription" name="description" placeholder="Enter book description..." rows="5"></textarea>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group full-width">
            <label for="bookCover">Book Cover Image *</label>
            <div class="image-upload-wrapper">
              <input type="file" id="bookCover" name="cover_image" accept="image/*" required>
              <p class="upload-hint">Upload a JPG, PNG, or WebP image (Max 5MB)</p>
            </div>
            <span class="error-message" id="imageError"></span>
            
            <!-- Image Preview -->
            <div id="imagePreviewContainer" class="image-preview-container" style="display:none;">
              <img id="imagePreview" alt="Book Cover Preview">
              <button type="button" class="btn-remove-image" onclick="removeImage()">Remove Image</button>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-submit">Add Book</button>
          <button type="reset" class="btn-cancel">Clear Form</button>
        </div>

        <div id="successMessage" class="success-message" style="display:none;">
          Book added successfully!
        </div>
        <div id="errorMessage" class="error-alert" style="display:none;"></div>

      </form>
    </div>

    <div class="recently-added">
      <h2>Recently Added Books</h2>
      <div id="recentBooksList" class="books-list">
        <p class="no-books">No books added yet</p>
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
          <li><a href="../../Admin_dashboard/dashboard.php">Dashboard</a></li>    
          <li><a href="../../Home/index.php">Home</a></li>
          <li><a href="../../admin_catlog/catlog.php">Book Catalog</a></li>
          <li><a href="../../contact/contact.php">Contact</a></li>
          <li><a href="../../About us/About us.php">About Us</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Account</h3>
        <ul>
          <li><a href="../../Login/user_login.php">Log In</a></li>
          <li><a href="../../Register/register.php">Sign Up</a></li>
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
      <p>OpenLib. All rights reserved.</p>
    </div>
  </div>
</footer>

<script src="../assets/add-book.js"></script>
</body>
</html>
