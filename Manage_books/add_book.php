<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'openlib';
$username = 'root';
$password = '';

// Create connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if user is admin (implement proper authentication)
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header("Location: ../Login/user_login.php");
//     exit();
// }

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        handleAdd($conn, $errors, $success);
    } elseif ($action === 'edit') {
        handleEdit($conn, $errors, $success);
    } elseif ($action === 'delete') {
        handleDelete($conn, $errors, $success);
    }
    
    // Store messages in session and redirect
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    }
    if (!empty($success)) {
        $_SESSION['success'] = $success;
    }
    header("Location: add_book.php");
    exit();
}

// Handle Add Book
function handleAdd($conn, &$errors, &$success) {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $publication_year = trim($_POST['publication_year'] ?? '');
    $pages = trim($_POST['pages'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validation
    if (empty($title) || empty($author) || empty($isbn) || empty($genre) || 
        empty($publication_year) || empty($pages) || empty($quantity)) {
        $errors[] = "All required fields must be filled.";
        return;
    }
    
    if (!is_numeric($publication_year) || $publication_year < 1000 || $publication_year > date('Y')) {
        $errors[] = "Invalid publication year.";
        return;
    }
    
    if (!is_numeric($pages) || $pages < 1) {
        $errors[] = "Pages must be a positive number.";
        return;
    }
    
    if (!is_numeric($quantity) || $quantity < 0) {
        $errors[] = "Quantity must be a non-negative number.";
        return;
    }
    
    // Handle image upload
    $cover_image = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Only JPG, JPEG, PNG, and GIF images are allowed.";
            return;
        }
        
        $cover_image = $upload_dir . uniqid() . '.' . $file_extension;
        if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $cover_image)) {
            $errors[] = "Failed to upload cover image.";
            return;
        }
    }
    
    // Insert book
    try {
        $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, genre, publication_year, pages, publisher, quantity, available_quantity, description, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $author, $isbn, $genre, $publication_year, $pages, $publisher, $quantity, $quantity, $description, $cover_image]);
        $success = "Book added successfully!";
    } catch(PDOException $e) {
        $errors[] = "Error adding book: " . $e->getMessage();
    }
}

// Handle Edit Book
function handleEdit($conn, &$errors, &$success) {
    $id = $_POST['book_id'] ?? 0;
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $publication_year = trim($_POST['publication_year'] ?? '');
    $pages = trim($_POST['pages'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validation
    if (empty($title) || empty($author) || empty($isbn) || empty($genre) || 
        empty($publication_year) || empty($pages) || empty($quantity)) {
        $errors[] = "All required fields must be filled.";
        return;
    }
    
    // Handle image upload (if new image provided)
    $cover_image = $_POST['existing_cover'] ?? '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_extension = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            // Delete old image
            if (!empty($cover_image) && file_exists($cover_image)) {
                unlink($cover_image);
            }
            
            $cover_image = $upload_dir . uniqid() . '.' . $file_extension;
            move_uploaded_file($_FILES['cover_image']['tmp_name'], $cover_image);
        }
    }
    
    // Update book
    try {
        $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, isbn = ?, genre = ?, publication_year = ?, pages = ?, publisher = ?, quantity = ?, description = ?, cover_image = ? WHERE id = ?");
        $stmt->execute([$title, $author, $isbn, $genre, $publication_year, $pages, $publisher, $quantity, $description, $cover_image, $id]);
        $success = "Book updated successfully!";
    } catch(PDOException $e) {
        $errors[] = "Error updating book: " . $e->getMessage();
    }
}

// Handle Delete Book
function handleDelete($conn, &$errors, &$success) {
    $id = $_POST['book_id'] ?? 0;
    
    if (empty($id)) {
        $errors[] = "Invalid book ID.";
        return;
    }
    
    try {
        // Get cover image to delete
        $stmt = $conn->prepare("SELECT cover_image FROM books WHERE id = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($book && !empty($book['cover_image']) && file_exists($book['cover_image'])) {
            unlink($book['cover_image']);
        }
        
        // Delete book
        $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Book deleted successfully!";
    } catch(PDOException $e) {
        $errors[] = "Error deleting book: " . $e->getMessage();
    }
}

// Fetch all books
$stmt = $conn->query("SELECT * FROM books ORDER BY added_date DESC");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Books | OpenLib</title>
  <link rel="stylesheet" href="add_book.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<!-- HEADER -->
<header class="header">
  <div class="logo">Open<span>Lib</span></div>

  <nav class="navbar" id="navMenu">
    <a href="../dashboard/dashboard.html">Dashboard</a>
    <a href="../Home/Main.html">Home</a>
    <a href="#">Catalog</a>
    <a href="../contact us/contact.html">Contact</a>
    <a href="../Abou_us/about_us.html">About Us</a>
    <a href="../Login/user_login.php" class="btn-nav">Logout</a>
  </nav>

  <div class="menu-toggle" onclick="toggleMenu()">☰</div>
</header>

<!-- MAIN CONTENT -->
<div class="container">
  <div class="page-header">
    <h1>Manage Books</h1>
    <button class="btn-add" onclick="openModal()">
      <i class='bx bx-plus'></i> Add New Book
    </button>
  </div>

  <?php
  if (isset($_SESSION['success'])) {
      echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
      unset($_SESSION['success']);
  }
  
  if (isset($_SESSION['errors'])) {
      echo '<div class="alert alert-danger">';
      foreach ($_SESSION['errors'] as $error) {
          echo '<p>' . htmlspecialchars($error) . '</p>';
      }
      echo '</div>';
      unset($_SESSION['errors']);
  }
  ?>

  <div class="search-box">
    <i class='bx bx-search'></i>
    <input type="text" id="searchInput" placeholder="Search books by title, author, or ISBN..." onkeyup="searchBooks()">
  </div>

  <div class="books-grid">
    <?php foreach ($books as $book): ?>
    <div class="book-card" data-book-id="<?php echo $book['id']; ?>">
      <div class="book-image">
        <?php if (!empty($book['cover_image']) && file_exists($book['cover_image'])): ?>
          <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
        <?php else: ?>
          <div class="no-image">
            <i class='bx bx-book'></i>
          </div>
        <?php endif; ?>
      </div>
      <div class="book-info">
        <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
        <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
        <div class="book-details">
          <span class="genre-badge"><?php echo htmlspecialchars($book['genre']); ?></span>
          <span class="year-badge"><?php echo htmlspecialchars($book['publication_year']); ?></span>
        </div>
        <div class="book-stats">
          <div class="stat">
            <i class='bx bx-book-open'></i>
            <span><?php echo htmlspecialchars($book['pages']); ?> pages</span>
          </div>
          <div class="stat">
            <i class='bx bx-package'></i>
            <span><?php echo htmlspecialchars($book['available_quantity']); ?>/<?php echo htmlspecialchars($book['quantity']); ?> available</span>
          </div>
        </div>
        <div class="book-actions">
          <button class="btn-action btn-view" onclick="viewBook(<?php echo $book['id']; ?>)" title="View Details">
            <i class='bx bx-show'></i>
          </button>
          <button class="btn-action btn-edit" onclick="editBook(<?php echo $book['id']; ?>)" title="Edit">
            <i class='bx bx-edit'></i>
          </button>
          <button class="btn-action btn-delete" onclick="openDeleteModal(<?php echo $book['id']; ?>)" title="Delete">
            <i class='bx bx-trash'></i>
          </button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($books)): ?>
    <div class="no-books">
      <i class='bx bx-book-open'></i>
      <h2>No Books Yet</h2>
      <p>Click "Add New Book" to start building your library collection.</p>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ADD/EDIT MODAL -->
<div id="bookModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h2 id="modalTitle">Add New Book</h2>
    
    <form id="bookForm" action="add_book.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" id="book_id" name="book_id">
      <input type="hidden" id="action" name="action" value="add">
      <input type="hidden" id="existing_cover" name="existing_cover">
      
      <div class="form-row">
        <div class="input-box">
          <label>Title *</label>
          <input type="text" id="title" name="title" placeholder="Enter book title" required>
          <i class='bx bx-book'></i>
        </div>
        
        <div class="input-box">
          <label>Author *</label>
          <input type="text" id="author" name="author" placeholder="Enter author name" required>
          <i class='bx bx-user'></i>
        </div>
      </div>
      
      <div class="form-row">
        <div class="input-box">
          <label>ISBN *</label>
          <input type="text" id="isbn" name="isbn" placeholder="Enter ISBN" required>
          <i class='bx bx-barcode'></i>
        </div>
        
        <div class="input-box">
          <label>Genre *</label>
          <select id="genre" name="genre" required>
            <option value="">Select Genre</option>
            <option value="Fiction">Fiction</option>
            <option value="Non-Fiction">Non-Fiction</option>
            <option value="Science">Science</option>
            <option value="History">History</option>
            <option value="Biography">Biography</option>
            <option value="Fantasy">Fantasy</option>
            <option value="Mystery">Mystery</option>
            <option value="Romance">Romance</option>
            <option value="Thriller">Thriller</option>
            <option value="Self-Help">Self-Help</option>
            <option value="Technology">Technology</option>
            <option value="Children">Children</option>
          </select>
          <i class='bx bx-category'></i>
        </div>
      </div>
      
      <div class="form-row">
        <div class="input-box">
          <label>Publication Year *</label>
          <input type="number" id="publication_year" name="publication_year" placeholder="YYYY" min="1000" max="<?php echo date('Y'); ?>" required>
          <i class='bx bx-calendar'></i>
        </div>
        
        <div class="input-box">
          <label>Pages *</label>
          <input type="number" id="pages" name="pages" placeholder="Number of pages" min="1" required>
          <i class='bx bx-file'></i>
        </div>
      </div>
      
      <div class="form-row">
        <div class="input-box">
          <label>Publisher</label>
          <input type="text" id="publisher" name="publisher" placeholder="Enter publisher name">
          <i class='bx bx-building'></i>
        </div>
        
        <div class="input-box">
          <label>Quantity *</label>
          <input type="number" id="quantity" name="quantity" placeholder="Total copies" min="0" required>
          <i class='bx bx-package'></i>
        </div>
      </div>
      
      <div class="input-box full-width">
        <label>Description</label>
        <textarea id="description" name="description" placeholder="Enter book description" rows="4"></textarea>
      </div>
      
      <div class="input-box full-width">
        <label>Cover Image</label>
        <input type="file" id="cover_image" name="cover_image" accept="image/*" onchange="previewImage(event)">
        <div id="imagePreview"></div>
      </div>
      
      <button type="submit" class="btn-submit">
        <i class='bx bx-save'></i> Save Book
      </button>
    </form>
  </div>
</div>

<!-- VIEW MODAL -->
<div id="viewModal" class="modal">
  <div class="modal-content view-modal">
    <span class="close" onclick="closeViewModal()">&times;</span>
    <div id="viewContent"></div>
  </div>
</div>

<!-- DELETE CONFIRMATION MODAL -->
<div id="deleteModal" class="modal">
  <div class="modal-content delete-modal">
    <h2>Confirm Delete</h2>
    <p>Are you sure you want to delete this book? This action cannot be undone.</p>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
      <button class="btn-delete-confirm" onclick="confirmDelete()">Delete</button>
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
          <li><a href="#">Book Catalog</a></li>
          <li><a href="../contact us/contact.html">Contact</a></li>
          <li><a href="../Abou_us/about_us.html">About Us</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Account</h3>
        <ul>
          <li><a href="../Login/user_login.php">Log In</a></li>
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

<script src="add_book.js"></script>
<script>
// Toggle mobile menu
function toggleMenu() {
  const navMenu = document.getElementById('navMenu');
  navMenu.classList.toggle('active');
}
</script>
</body>
</html>
