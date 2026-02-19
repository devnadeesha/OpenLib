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

// Check if user is admin (uncomment in production)
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header("Location: ../Login/user_login.php");
//     exit();
// }

$errors = [];
$success = '';

// Handle actions (mark as read, delete, etc.)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $message_id = $_POST['message_id'] ?? 0;
    
    if ($action === 'mark_read' && $message_id) {
        $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
        $stmt->execute([$message_id]);
        $success = "Message marked as read.";
    } elseif ($action === 'mark_replied' && $message_id) {
        $stmt = $conn->prepare("UPDATE contact_messages SET status = 'replied', replied_at = NOW() WHERE id = ?");
        $stmt->execute([$message_id]);
        $success = "Message marked as replied.";
    } elseif ($action === 'delete' && $message_id) {
        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$message_id]);
        $success = "Message deleted successfully.";
    }
    
    if (!empty($success)) {
        $_SESSION['success'] = $success;
        header("Location: view_messages.php");
        exit();
    }
}

// Get filter
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];

if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR message LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get counts
$stmt = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE status = 'unread'");
$unread_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM contact_messages");
$total_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Messages - Admin | OpenLib</title>
  <link rel="stylesheet" href="view_messages.css?v=2">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<header class="header">
  <div class="logo">Open<span>Lib</span></div>
  <nav class="navbar" id="navMenu">
    <a href="../dashboard/dashboard.html">Dashboard</a>
    <a href="view_messages.php" class="active">Contact Messages</a>
    <a href="../Members/manage_members.php">Manage Users</a>
    <a href="../Books/add_book.php">Manage Books</a>
    <a href="../Login/user_login.php" class="btn-nav">Logout</a>
  </nav>
  <div class="menu-toggle" onclick="toggleMenu()">☰</div>
</header>

<div class="container">
  
  <div class="page-header">
    <div>
      <h1><i class='bx bx-envelope'></i> Contact Messages</h1>
      <p>Manage customer inquiries and messages</p>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-box">
      <div class="stat-icon" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;">
        <i class='bx bx-envelope'></i>
      </div>
      <div class="stat-info">
        <h3><?php echo $unread_count; ?></h3>
        <p>Unread Messages</p>
      </div>
    </div>

    <div class="stat-box">
      <div class="stat-icon" style="background: rgba(26, 188, 156, 0.1); color: #1abc9c;">
        <i class='bx bx-message-dots'></i>
      </div>
      <div class="stat-info">
        <h3><?php echo $total_count; ?></h3>
        <p>Total Messages</p>
      </div>
    </div>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
      <i class='bx bx-check-circle'></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>

  <!-- Filters -->
  <div class="filter-section">
    <form method="GET" action="view_messages.php" class="filter-form">
      <div class="search-box">
        <i class='bx bx-search'></i>
        <input type="text" name="search" placeholder="Search by name, email, or message..." 
               value="<?php echo htmlspecialchars($search); ?>">
      </div>
      
      <select name="status" onchange="this.form.submit()">
        <option value="">All Status</option>
        <option value="unread" <?php echo $status_filter === 'unread' ? 'selected' : ''; ?>>Unread</option>
        <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>Read</option>
        <option value="replied" <?php echo $status_filter === 'replied' ? 'selected' : ''; ?>>Replied</option>
      </select>
      
      <button type="submit" class="btn-filter">Apply</button>
      
      <?php if ($search || $status_filter): ?>
        <a href="view_messages.php" class="btn-clear">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Messages List -->
  <div class="messages-list">
    <?php if (empty($messages)): ?>
      <div class="no-messages">
        <i class='bx bx-envelope-open'></i>
        <p>No messages found.</p>
      </div>
    <?php else: ?>
      <?php foreach ($messages as $msg): ?>
      <div class="message-card <?php echo $msg['status']; ?>">
        <div class="message-header">
          <div class="message-from">
            <i class='bx bx-user-circle'></i>
            <div>
              <h4><?php echo htmlspecialchars($msg['name']); ?></h4>
              <p><?php echo htmlspecialchars($msg['email']); ?></p>
            </div>
          </div>
          <div class="message-meta">
            <span class="status-badge status-<?php echo $msg['status']; ?>">
              <?php echo ucfirst($msg['status']); ?>
            </span>
            <span class="message-date">
              <i class='bx bx-time'></i>
              <?php echo date('M d, Y - h:i A', strtotime($msg['created_at'])); ?>
            </span>
          </div>
        </div>

        <?php if (!empty($msg['subject'])): ?>
          <div class="message-subject">
            <strong>Subject:</strong> <?php echo htmlspecialchars($msg['subject']); ?>
          </div>
        <?php endif; ?>

        <div class="message-content">
          <p><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
        </div>

        <div class="message-actions">
          <?php if ($msg['status'] === 'unread'): ?>
            <form method="POST" action="view_messages.php" style="display: inline;">
              <input type="hidden" name="action" value="mark_read">
              <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
              <button type="submit" class="btn-action btn-read" title="Mark as Read">
                <i class='bx bx-check'></i> Mark Read
              </button>
            </form>
          <?php endif; ?>

          <?php if ($msg['status'] !== 'replied'): ?>
            <form method="POST" action="view_messages.php" style="display: inline;">
              <input type="hidden" name="action" value="mark_replied">
              <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
              <button type="submit" class="btn-action btn-replied" title="Mark as Replied">
                <i class='bx bx-reply'></i> Mark Replied
              </button>
            </form>
          <?php endif; ?>

          <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo htmlspecialchars($msg['subject'] ?? 'Your Message'); ?>" 
             class="btn-action btn-email" title="Reply via Email">
            <i class='bx bx-envelope'></i> Reply
          </a>

          <form method="POST" action="view_messages.php" style="display: inline;" 
                onsubmit="return confirm('Are you sure you want to delete this message?');">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
            <button type="submit" class="btn-action btn-delete" title="Delete">
              <i class='bx bx-trash'></i> Delete
            </button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<footer class="footer">
  <div class="footer-container">
    <div class="footer-bottom">
      <p>&copy; 2026 OpenLib Library. All rights reserved.</p>
    </div>
  </div>
</footer>

<script>
function toggleMenu() {
  const navMenu = document.getElementById('navMenu');
  navMenu.classList.toggle('active');
}

// Auto-hide alerts
document.addEventListener('DOMContentLoaded', function() {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.3s';
      setTimeout(() => alert.style.display = 'none', 300);
    }, 5000);
  });
});
</script>
</body>
</html>