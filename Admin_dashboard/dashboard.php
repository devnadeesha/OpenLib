<?php
// ── DB CONNECTION ─────────────────────────────────────────────────────────────
$host   = 'localhost';
$dbname = 'openlib';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// ── LIVE STATS ────────────────────────────────────────────────────────────────

// Total books (sum of all quantities)
$totalBooks = $pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM books")->fetchColumn();

// Total members (users with role = 'user')
$totalMembers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

// Currently borrowed (active borrow records)
$totalBorrowed = $pdo->query("SELECT COUNT(*) FROM borrow_records WHERE status = 'borrowed'")->fetchColumn();

// Available books (sum of available_quantity)
$totalAvailable = $pdo->query("SELECT COALESCE(SUM(available_quantity), 0) FROM books")->fetchColumn();

// Recent activities (last 5 borrow/return events)
$recentStmt = $pdo->query("
    SELECT br.status, br.borrow_date, br.return_date,
           b.title,
           CONCAT(u.first_name, ' ', u.last_name) AS member_name
    FROM borrow_records br
    JOIN books b ON br.book_id = b.id
    JOIN users u ON br.user_id = u.user_id
    ORDER BY br.updated_at DESC
    LIMIT 5
");
$recentActivities = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | OpenLib</title>
  <link rel="stylesheet" href="dashboard.css?v=2">
</head>
<body>

<header class="header">
  <div class="logo">Open<span>Lib</span></div>
  <nav class="navbar" id="navMenu">
    <a href="../view_massages/view_messages.php" class="btn login special">View Messages</a>
    <a href="../Admin_dashboard/dashboard.php" class="active">Dashboard</a>
    <a href="../admin_catlog/catalog.php">Catalog</a>
    <a href="../Manage_books/add_book.php">Add New Book</a>
    <a href="../Login/user_login.php" class="btn login">Logout</a>
  </nav>
  <div class="menu-toggle" onclick="toggleMenu()">☰</div>
</header>

<section class="dashboard">
  <div class="dash-container">

    <h1 class="dash-title">Dashboard</h1>

    <!-- LIVE STATS -->
    <div class="stats-grid">
      <div class="stat-card">
        <h3>Total Books</h3>
        <p><?= number_format($totalBooks) ?></p>
      </div>
      <div class="stat-card">
        <h3>Members</h3>
        <p><?= number_format($totalMembers) ?></p>
      </div>
      <div class="stat-card">
        <h3>Borrowed</h3>
        <p><?= number_format($totalBorrowed) ?></p>
      </div>
      <div class="stat-card">
        <h3>Available</h3>
        <p><?= number_format($totalAvailable) ?></p>
      </div>
    </div>

    <div class="dashboard-grid">

      <div class="dashboard-card">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
          <a href="../Manage_books/add_book.php" class="btn">Add New Book</a>
          <a href="../admin_catlog/catalog.php" class="btn">View Catalog</a>
          <a href="../Manage_members/manage_members.php" class="btn">Manage Users</a>
          <a href="../Borrow_history/borrow_history.php" class="btn">Borrow History</a>
        
          
        </div>
      </div>

      <!-- LIVE RECENT ACTIVITIES -->
      <div class="dashboard-card">
        <h2>Recent Activities</h2>
        <ul class="activity-list">
          <?php if (empty($recentActivities)): ?>
            <li>No recent activity.</li>
          <?php else: ?>
            <?php foreach ($recentActivities as $a): ?>
              <?php if ($a['status'] === 'borrowed'): ?>
                <li>📖 "<?= htmlspecialchars($a['title']) ?>" borrowed by <?= htmlspecialchars($a['member_name']) ?></li>
              <?php elseif ($a['status'] === 'returned'): ?>
                <li>📘 "<?= htmlspecialchars($a['title']) ?>" returned by <?= htmlspecialchars($a['member_name']) ?></li>
              <?php elseif ($a['status'] === 'overdue'): ?>
                <li>⚠️ "<?= htmlspecialchars($a['title']) ?>" overdue — <?= htmlspecialchars($a['member_name']) ?></li>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
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
          <li><a href="../dashboard/dashboard.php">Dashboard</a></li>
          <li><a href="../Borrow_history/borrow_history.php">Borrow_history</a></li>
          <li><a href="../admin_catlog/catalog.php">Book Catalog</a></li>
          <li><a href="../Manage_books/add_book.php">Manage Books</a></li>
          <li><a href="../Manage_members/manage_members.php">Manage Members</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Account</h3>
        <ul>
          <li><a href="../Login page/user_login.html">Log Out</a></li>
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