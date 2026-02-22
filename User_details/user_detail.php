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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // For testing, set a default user
    $_SESSION['user_id'] = 1; // Change this in production
    // Uncomment below for production:
    // header("Location: ../Login/user_login.php");
    // exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Fetch borrowed books (currently borrowed)
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

// Fetch borrowing history (returned books)
$stmt = $conn->prepare("
    SELECT br.*, b.title, b.author, b.cover_image
    FROM borrow_records br
    JOIN books b ON br.book_id = b.id
    WHERE br.user_id = ? AND br.status = 'returned'
    ORDER BY br.return_date DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total fines
$stmt = $conn->prepare("SELECT SUM(fine_amount) as total_fines FROM borrow_records WHERE user_id = ? AND fine_amount > 0");
$stmt->execute([$user_id]);
$fines_result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_fines = $fines_result['total_fines'] ?? 0;

// Count statistics
$currently_borrowed = count($borrowed_books);
$overdue_count = 0;
foreach ($borrowed_books as $book) {
    if ($book['status'] === 'overdue') {
        $overdue_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile | OpenLib</title>
  <link rel="stylesheet" href="user_detail.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<!-- HEADER -->
<header class="header">
  <div class="logo">Open<span>Lib</span></div>

     <nav class="navbar" id="navMenu">
                <a href="../User_details/user_detail.php" class="active">My Profile</a>
                <a href="../Home/index.php">Home</a>
                <a href="../Borrow_books/borrow_books.php">Borrow Books</a>
                <a href="../contact/contact.php">Contact</a>
                <a href="../About us/About us.php">About Us</a>
                

                <a href="../Login/user_login.php" class="btn logout">Logout</a>
               
            </nav>


  <div class="menu-toggle" onclick="toggleMenu()">☰</div>
</header>

<!-- MAIN CONTAINER -->
<div class="container">
  
  <div class="page-header">
    <h1><i class='bx bx-user-circle'></i> My Profile</h1>
    <p>View and manage your library account</p>
  </div>

  <div class="profile-grid">
    
    <!-- LEFT COLUMN - Profile Card -->
    <div class="profile-card">
      <div class="profile-image-wrapper">
        <?php if (!empty($user['profile_picture']) && file_exists('uploads/profile_pictures/' . $user['profile_picture'])): ?>
          <img src="uploads/profile_pictures/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-image">
        <?php else: ?>
          <div class="default-avatar">
            <i class='bx bx-user'></i>
          </div>
        <?php endif; ?>
        <button class="btn-upload-photo" onclick="document.getElementById('photoUpload').click()">
          <i class='bx bx-camera'></i>
        </button>
        <form id="photoUploadForm" method="POST" action="upload_profile_picture.php" enctype="multipart/form-data" style="display: none;">
          <input type="file" id="photoUpload" name="profile_picture" accept="image/*" onchange="document.getElementById('photoUploadForm').submit()">
        </form>
      </div>

      <div class="profile-info">
        <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
        <p class="email"><i class='bx bx-envelope'></i> <?php echo htmlspecialchars($user['email']); ?></p>
        
        <?php if (!empty($user['phone'])): ?>
          <p class="phone"><i class='bx bx-phone'></i> <?php echo htmlspecialchars($user['phone']); ?></p>
        <?php endif; ?>
      </div>

      <div class="profile-details">
        <div class="detail-row">
          <span class="detail-label"><i class='bx bx-shield'></i> Account Status</span>
          <span class="status-badge status-<?php echo $user['status']; ?>">
            <?php echo ucfirst($user['status']); ?>
          </span>
        </div>
        
        <div class="detail-row">
          <span class="detail-label"><i class='bx bx-calendar'></i> Member Since</span>
          <span class="detail-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
        </div>
        
        <?php if (!empty($user['address'])): ?>
          <div class="detail-row full-width">
            <span class="detail-label"><i class='bx bx-map'></i> Address</span>
            <span class="detail-value"><?php echo htmlspecialchars($user['address']); ?></span>
          </div>
        <?php endif; ?>
      </div>

      <div class="profile-actions">
        <a href="./edit_detail/edit_profile.php" class="btn-primary">
          <i class='bx bx-edit'></i> Edit Profile
        </a>
      </div>
    </div>

    <!-- RIGHT COLUMN - Statistics and Books -->
    <div class="profile-content">
      
      <!-- Statistics Cards -->
      <div class="stats-row">
        <div class="stat-box">
          <div class="stat-icon" style="background: rgba(26, 188, 156, 0.1); color: #1abc9c;">
            <i class='bx bx-book-bookmark'></i>
          </div>
          <div class="stat-info">
            <h3><?php echo $currently_borrowed; ?></h3>
            <p>Currently Borrowed</p>
          </div>
        </div>

        <div class="stat-box">
          <div class="stat-icon" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
            <i class='bx bx-error-circle'></i>
          </div>
          <div class="stat-info">
            <h3><?php echo $overdue_count; ?></h3>
            <p>Overdue Books</p>
          </div>
        </div>

        <div class="stat-box">
          <div class="stat-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
            <i class='bx bx-dollar-circle'></i>
          </div>
          <div class="stat-info">
            <h3>$<?php echo number_format($total_fines, 2); ?></h3>
            <p>Total Fines</p>
          </div>
        </div>
      </div>

      <!-- Currently Borrowed Books -->
      <div class="books-section">
        <div class="section-header">
          <h3><i class='bx bx-book-open'></i> Currently Borrowed Books</h3>
        </div>

        <?php if (empty($borrowed_books)): ?>
          <div class="empty-state">
            <i class='bx bx-book'></i>
            <p>You haven't borrowed any books yet.</p>
            <a href="../Borrow_books/borrow_books.php" class="btn-link">Borrow Book</a>
          </div>
        <?php else: ?>
          <div class="books-list">
            <?php foreach ($borrowed_books as $book): ?>
            <div class="book-item <?php echo $book['status'] === 'overdue' ? 'overdue' : ''; ?>">
              <div class="book-cover">
                <?php if (!empty($book['cover_image']) && file_exists($book['cover_image'])): ?>
                  <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Book Cover">
                <?php else: ?>
                  <div class="no-cover"><i class='bx bx-book'></i></div>
                <?php endif; ?>
              </div>
              
              <div class="book-details">
                <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>
                
                <div class="book-dates">
                  <span><i class='bx bx-calendar'></i> Borrowed: <?php echo date('M d, Y', strtotime($book['borrow_date'])); ?></span>
                  <span><i class='bx bx-calendar-check'></i> Due: <?php echo date('M d, Y', strtotime($book['due_date'])); ?></span>
                </div>

                <?php if ($book['status'] === 'overdue'): ?>
                  <div class="overdue-alert">
                    <i class='bx bx-error'></i> Overdue by <?php echo abs($book['days_remaining']); ?> days
                  </div>
                <?php else: ?>
                  <div class="due-info <?php echo $book['days_remaining'] <= 3 ? 'urgent' : ''; ?>">
                    <i class='bx bx-time'></i>
                    <?php if ($book['days_remaining'] == 0): ?>
                      Due today!
                    <?php elseif ($book['days_remaining'] == 1): ?>
                      Due tomorrow
                    <?php else: ?>
                      <?php echo $book['days_remaining']; ?> days remaining
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Borrowing History -->
      <?php if (!empty($history)): ?>
      <div class="history-section">
        <div class="section-header">
          <h3><i class='bx bx-history'></i> Recent Borrowing History</h3>
          <a href="full_history.php" class="view-all">View All <i class='bx bx-chevron-right'></i></a>
        </div>

        <table class="history-table">
          <thead>
            <tr>
              <th>Book</th>
              <th>Author</th>
              <th>Borrowed</th>
              <th>Returned</th>
              <th>Fine</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($history as $record): ?>
            <tr>
              <td>
                <div class="table-book">
                  <?php if (!empty($record['cover_image']) && file_exists($record['cover_image'])): ?>
                    <img src="<?php echo htmlspecialchars($record['cover_image']); ?>" alt="Book">
                  <?php endif; ?>
                  <span><?php echo htmlspecialchars($record['title']); ?></span>
                </div>
              </td>
              <td><?php echo htmlspecialchars($record['author']); ?></td>
              <td><?php echo date('M d, Y', strtotime($record['borrow_date'])); ?></td>
              <td><?php echo date('M d, Y', strtotime($record['return_date'])); ?></td>
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
          <li><a href="../Borrow_books/borrow_books.php">Borrow Books</a></li>
          <li><a href="../contact/contact.php">Contact</a></li>
          <li><a href="../About us/about us.php">About Us</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Account</h3>
        <ul>
          <li><a href="../User_details/user_detail.php">My Profile</a></li>
          <li><a href="../User_details/edit_detail/edit_profile.php">Edit Profile</a></li>
          <li><a href="../Login/user_login.php">Logout</a></li>
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

<script>
function toggleMenu() {
  const navMenu = document.getElementById('navMenu');
  navMenu.classList.toggle('active');
}
</script>
</body>
</html>