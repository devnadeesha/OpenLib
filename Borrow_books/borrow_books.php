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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // For testing, set a default user
    $_SESSION['user_id'] = 1; // Change this in production
    $_SESSION['user_name'] = 'Test User';
    // Uncomment below for production:
    // header("Location: ../Login/user_login.php");
    // exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'borrow') {
        handleBorrow($conn, $user_id, $errors, $success);
    } elseif ($action === 'return') {
        handleReturn($conn, $errors, $success);
    }
    
    // Store messages in session and redirect
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    }
    if (!empty($success)) {
        $_SESSION['success'] = $success;
    }
    header("Location: borrow_books.php");
    exit();
}

// Handle Borrow Book
function handleBorrow($conn, $user_id, &$errors, &$success) {
    $book_id = $_POST['book_id'] ?? 0;
    $borrow_days = 14; // Default 14 days borrowing period
    
    if (empty($book_id)) {
        $errors[] = "Invalid book selection.";
        return;
    }
    
    // Check if book is available
    $stmt = $conn->prepare("SELECT available_quantity, title FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$book) {
        $errors[] = "Book not found.";
        return;
    }
    
    if ($book['available_quantity'] <= 0) {
        $errors[] = "Sorry, '{$book['title']}' is currently not available.";
        return;
    }
    
    // Check if user already has this book borrowed
    $stmt = $conn->prepare("SELECT id FROM borrow_records WHERE user_id = ? AND book_id = ? AND status = 'borrowed'");
    $stmt->execute([$user_id, $book_id]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "You have already borrowed this book.";
        return;
    }
    
    // Check borrowing limit (max 5 books at a time)
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM borrow_records WHERE user_id = ? AND status = 'borrowed'");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['count'] >= 5) {
        $errors[] = "You have reached the maximum borrowing limit (5 books).";
        return;
    }
    
    try {
        $conn->beginTransaction();
        
        // Create borrow record
        $borrow_date = date('Y-m-d');
        $due_date = date('Y-m-d', strtotime("+$borrow_days days"));
        
        $stmt = $conn->prepare("INSERT INTO borrow_records (user_id, book_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
        $stmt->execute([$user_id, $book_id, $borrow_date, $due_date]);
        
        // Decrease available quantity
        $stmt = $conn->prepare("UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ?");
        $stmt->execute([$book_id]);
        
        $conn->commit();
        $success = "Book borrowed successfully! Please return by " . date('F d, Y', strtotime($due_date));
    } catch(PDOException $e) {
        $conn->rollBack();
        $errors[] = "Error borrowing book: " . $e->getMessage();
    }
}

// Handle Return Book
function handleReturn($conn, &$errors, &$success) {
    $record_id = $_POST['record_id'] ?? 0;
    
    if (empty($record_id)) {
        $errors[] = "Invalid record.";
        return;
    }
    
    try {
        $conn->beginTransaction();
        
        // Get borrow record
        $stmt = $conn->prepare("SELECT book_id, due_date FROM borrow_records WHERE id = ? AND status = 'borrowed'");
        $stmt->execute([$record_id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$record) {
            $errors[] = "Borrow record not found.";
            $conn->rollBack();
            return;
        }
        
        // Calculate fine if overdue
        $return_date = date('Y-m-d');
        $fine = 0;
        if (strtotime($return_date) > strtotime($record['due_date'])) {
            $days_overdue = (strtotime($return_date) - strtotime($record['due_date'])) / (60 * 60 * 24);
            $fine = $days_overdue * 1.00; // $1 per day fine
        }
        
        // Update borrow record
        $stmt = $conn->prepare("UPDATE borrow_records SET return_date = ?, status = 'returned', fine_amount = ? WHERE id = ?");
        $stmt->execute([$return_date, $fine, $record_id]);
        
        // Increase available quantity
        $stmt = $conn->prepare("UPDATE books SET available_quantity = available_quantity + 1 WHERE id = ?");
        $stmt->execute([$record['book_id']]);
        
        $conn->commit();
        
        if ($fine > 0) {
            $success = "Book returned successfully! Fine: $" . number_format($fine, 2);
        } else {
            $success = "Book returned successfully!";
        }
    } catch(PDOException $e) {
        $conn->rollBack();
        $errors[] = "Error returning book: " . $e->getMessage();
    }
}

// Update overdue status
$stmt = $conn->prepare("UPDATE borrow_records SET status = 'overdue' WHERE status = 'borrowed' AND due_date < CURDATE()");
$stmt->execute();

// Fetch available books for borrowing
$search = $_GET['search'] ?? '';
$genre_filter = $_GET['genre'] ?? '';

$sql = "SELECT * FROM books WHERE available_quantity > 0";
$params = [];

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($genre_filter)) {
    $sql .= " AND genre = ?";
    $params[] = $genre_filter;
}

$sql .= " ORDER BY title ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$available_books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's borrowed books
$stmt = $conn->prepare("
    SELECT br.*, b.title, b.author, b.cover_image, b.isbn,
           DATEDIFF(br.due_date, CURDATE()) as days_remaining
    FROM borrow_records br
    JOIN books b ON br.book_id = b.id
    WHERE br.user_id = ? AND br.status IN ('borrowed', 'overdue')
    ORDER BY br.borrow_date DESC
");
$stmt->execute([$user_id]);
$borrowed_books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's borrowing history
$stmt = $conn->prepare("
    SELECT br.*, b.title, b.author, b.cover_image
    FROM borrow_records br
    JOIN books b ON br.book_id = b.id
    WHERE br.user_id = ? AND br.status = 'returned'
    ORDER BY br.return_date DESC
    LIMIT 10
");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all genres for filter
$stmt = $conn->query("SELECT DISTINCT genre FROM books ORDER BY genre");
$genres = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Borrow Books | OpenLib</title>
  <link rel="stylesheet" href="borrow_books.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<!-- HEADER -->
<header class="header">
  <div class="logo">Open<span>Lib</span></div>

  <nav class="navbar" id="navMenu">
    <a href="../User_details/user_detail.php">My Profile</a>
    <a href="../Home/index.php">Home</a>
    <a href="borrow_books.php" class="active">Borrow Books</a>
    <a href="../contact/contact.php">Contact</a>
    <a href="../About us/About us.php">About Us</a>
    <a href="../Login/user_login.php" class="btn-nav">Logout</a>
  </nav>

  <div class="menu-toggle" onclick="toggleMenu()">☰</div>
</header>

<!-- MAIN CONTENT -->
<div class="container">
  
  <!-- User Info & Stats -->
  <div class="user-section">
    <div class="user-info">
      <i class='bx bx-user-circle'></i>
      <div>
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>!</h2>
        <p>Your library account</p>
      </div>
    </div>
    <div class="user-stats">
      <div class="stat">
        <span class="stat-number"><?php echo count($borrowed_books); ?></span>
        <span class="stat-label">Currently Borrowed</span>
      </div>
      <div class="stat">
        <span class="stat-number"><?php echo 5 - count($borrowed_books); ?></span>
        <span class="stat-label">Available Slots</span>
      </div>
    </div>
  </div>

  <?php
  if (isset($_SESSION['success'])) {
      echo '<div class="alert alert-success"><i class="bx bx-check-circle"></i> ' . htmlspecialchars($_SESSION['success']) . '</div>';
      unset($_SESSION['success']);
  }
  
  if (isset($_SESSION['errors'])) {
      echo '<div class="alert alert-danger"><i class="bx bx-error"></i> ';
      foreach ($_SESSION['errors'] as $error) {
          echo '<p>' . htmlspecialchars($error) . '</p>';
      }
      echo '</div>';
      unset($_SESSION['errors']);
  }
  ?>

  <!-- Tabs -->
  <div class="tabs">
    <button class="tab-btn active" onclick="showTab('available')">
      <i class='bx bx-book-open'></i> Available Books
    </button>
    <button class="tab-btn" onclick="showTab('borrowed')">
      <i class='bx bx-book-bookmark'></i> My Borrowed Books (<?php echo count($borrowed_books); ?>)
    </button>
    <button class="tab-btn" onclick="showTab('history')">
      <i class='bx bx-history'></i> Borrowing History
    </button>
  </div>

  <!-- AVAILABLE BOOKS TAB -->
  <div id="available" class="tab-content active">
    
    <!-- Search and Filter -->
    <div class="search-filter-section">
      <form method="GET" action="borrow_books.php" class="search-form">
        <div class="search-box">
          <i class='bx bx-search'></i>
          <input type="text" name="search" placeholder="Search books by title, author, or ISBN..." 
                 value="<?php echo htmlspecialchars($search); ?>">
        </div>
        
        <div class="filter-box">
          <i class='bx bx-filter'></i>
          <select name="genre" onchange="this.form.submit()">
            <option value="">All Genres</option>
            <?php foreach ($genres as $genre): ?>
              <option value="<?php echo htmlspecialchars($genre); ?>" 
                      <?php echo ($genre_filter === $genre) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($genre); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <button type="submit" class="btn-search">Search</button>
        <?php if ($search || $genre_filter): ?>
          <a href="borrow_books.php" class="btn-clear">Clear</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Books Grid -->
    <div class="books-grid">
      <?php if (empty($available_books)): ?>
        <div class="no-books">
          <i class='bx bx-book-open'></i>
          <h2>No Books Available</h2>
          <p>Try adjusting your search or filters.</p>
        </div>
      <?php else: ?>
        <?php foreach ($available_books as $book): ?>
        <div class="book-card">
          <div class="book-image">
           <?php if (!empty($book['cover_image']) && file_exists('../Manage_books/' . $book['cover_image'])): ?>
            <img src="../Manage_books/<?php echo htmlspecialchars($book['cover_image']); ?>">
              <div class="no-image"><i class='bx bx-book'></i></div>
            <?php endif; ?>
            <div class="availability-badge">
              <i class='bx bx-check-circle'></i> <?php echo $book['available_quantity']; ?> available
            </div>
          </div>
          <div class="book-info">
            <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
            <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
            <div class="book-details">
              <span class="genre-badge"><?php echo htmlspecialchars($book['genre']); ?></span>
              <span class="year-badge"><?php echo htmlspecialchars($book['publication_year']); ?></span>
            </div>
            <div class="book-meta">
              <span><i class='bx bx-book-open'></i> <?php echo htmlspecialchars($book['pages']); ?> pages</span>
              <span><i class='bx bx-barcode'></i> <?php echo htmlspecialchars($book['isbn']); ?></span>
            </div>
            <?php if (!empty($book['description'])): ?>
              <p class="book-description"><?php echo htmlspecialchars(substr($book['description'], 0, 100)) . '...'; ?></p>
            <?php endif; ?>
            <div class="book-actions">
              <button class="btn-borrow" onclick="borrowBook(<?php echo $book['id']; ?>, '<?php echo htmlspecialchars($book['title']); ?>')">
                <i class='bx bx-book-add'></i> Borrow This Book
              </button>
              <button class="btn-details" onclick="viewDetails(<?php echo $book['id']; ?>)">
                <i class='bx bx-info-circle'></i> Details
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- BORROWED BOOKS TAB -->
  <div id="borrowed" class="tab-content">
    <?php if (empty($borrowed_books)): ?>
      <div class="no-books">
        <i class='bx bx-book-bookmark'></i>
        <h2>No Borrowed Books</h2>
        <p>Browse available books and start borrowing!</p>
      </div>
    <?php else: ?>
      <div class="borrowed-list">
        <?php foreach ($borrowed_books as $record): ?>
        <div class="borrowed-item <?php echo $record['status'] === 'overdue' ? 'overdue' : ''; ?>">
          <div class="borrowed-image">
            <?php if (!empty($record['cover_image']) && file_exists($record['cover_image'])): ?>
              <img src="<?php echo htmlspecialchars($record['cover_image']); ?>" alt="<?php echo htmlspecialchars($record['title']); ?>">
            <?php else: ?>
              <div class="no-image-small"><i class='bx bx-book'></i></div>
            <?php endif; ?>
          </div>
          <div class="borrowed-details">
            <h3><?php echo htmlspecialchars($record['title']); ?></h3>
            <p class="author">by <?php echo htmlspecialchars($record['author']); ?></p>
            <div class="borrow-info">
              <span><i class='bx bx-calendar'></i> Borrowed: <?php echo date('M d, Y', strtotime($record['borrow_date'])); ?></span>
              <span><i class='bx bx-calendar-check'></i> Due: <?php echo date('M d, Y', strtotime($record['due_date'])); ?></span>
            </div>
            <?php if ($record['status'] === 'overdue'): ?>
              <div class="overdue-warning">
                <i class='bx bx-error'></i> Overdue by <?php echo abs($record['days_remaining']); ?> days
              </div>
            <?php else: ?>
              <div class="due-info <?php echo $record['days_remaining'] <= 3 ? 'urgent' : ''; ?>">
                <i class='bx bx-time'></i> 
                <?php if ($record['days_remaining'] == 0): ?>
                  Due today!
                <?php elseif ($record['days_remaining'] == 1): ?>
                  Due tomorrow
                <?php else: ?>
                  <?php echo $record['days_remaining']; ?> days remaining
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="borrowed-actions">
            <button class="btn-return" onclick="returnBook(<?php echo $record['id']; ?>, '<?php echo htmlspecialchars($record['title']); ?>')">
              <i class='bx bx-check'></i> Return Book
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- HISTORY TAB -->
  <div id="history" class="tab-content">
    <?php if (empty($history)): ?>
      <div class="no-books">
        <i class='bx bx-history'></i>
        <h2>No History Yet</h2>
        <p>Your borrowing history will appear here.</p>
      </div>
    <?php else: ?>
      <div class="history-list">
        <table class="history-table">
          <thead>
            <tr>
              <th>Book</th>
              <th>Author</th>
              <th>Borrowed</th>
              <th>Returned</th>
              <th>Duration</th>
              <th>Fine</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($history as $record): ?>
            <tr>
              <td>
                <div class="history-book">
                  <?php if (!empty($record['cover_image']) && file_exists($record['cover_image'])): ?>
                    <img src="<?php echo htmlspecialchars($record['cover_image']); ?>" alt="<?php echo htmlspecialchars($record['title']); ?>">
                  <?php endif; ?>
                  <span><?php echo htmlspecialchars($record['title']); ?></span>
                </div>
              </td>
              <td><?php echo htmlspecialchars($record['author']); ?></td>
              <td><?php echo date('M d, Y', strtotime($record['borrow_date'])); ?></td>
              <td><?php echo date('M d, Y', strtotime($record['return_date'])); ?></td>
              <td>
                <?php 
                $days = (strtotime($record['return_date']) - strtotime($record['borrow_date'])) / (60 * 60 * 24);
                echo ceil($days) . ' days';
                ?>
              </td>
              <td>
                <?php if ($record['fine_amount'] > 0): ?>
                  <span class="fine-amount">$<?php echo number_format($record['fine_amount'], 2); ?></span>
                <?php else: ?>
                  <span class="no-fine">-</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- BORROW CONFIRMATION MODAL -->
<div id="borrowModal" class="modal">
  <div class="modal-content">
    <h2><i class='bx bx-book-add'></i> Confirm Borrow</h2>
    <p id="borrowMessage"></p>
    <form id="borrowForm" method="POST" action="borrow_books.php">
      <input type="hidden" name="action" value="borrow">
      <input type="hidden" name="book_id" id="borrow_book_id">
      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closeBorrowModal()">Cancel</button>
        <button type="submit" class="btn-confirm">Borrow Book</button>
      </div>
    </form>
  </div>
</div>

<!-- RETURN CONFIRMATION MODAL -->
<div id="returnModal" class="modal">
  <div class="modal-content">
    <h2><i class='bx bx-check-circle'></i> Confirm Return</h2>
    <p id="returnMessage"></p>
    <form id="returnForm" method="POST" action="borrow_books.php">
      <input type="hidden" name="action" value="return">
      <input type="hidden" name="record_id" id="return_record_id">
      <div class="modal-actions">
        <button type="button" class="btn-cancel" onclick="closeReturnModal()">Cancel</button>
        <button type="submit" class="btn-confirm">Return Book</button>
      </div>
    </form>
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
          <li><a href="../Home/index.php">Home</a></li>
          <li><a href="borrow_books.php">Borrow Books</a></li>
          <li><a href="../contact/contact.php">Contact</a></li>
          <li><a href="../About us/about us.php">About Us</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Library Policies</h3>
        <ul>
          <li>Borrowing Period: 14 days</li>
          <li>Maximum Books: 5 at a time</li>
          <li>Overdue Fine: $1/day</li>
          <li>Renewal: Available once</li>
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

<script src="borrow_books.js"></script>
<script>
function toggleMenu() {
  const navMenu = document.getElementById('navMenu');
  navMenu.classList.toggle('active');
}
</script>
</body>
</html>
