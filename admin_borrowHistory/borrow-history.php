<?php
require_once '../db_connect.php';

// Auto-mark overdue records
$pdo->exec("UPDATE borrow_history SET status='Overdue' WHERE status='Borrowed' AND due_date < CURDATE()");

$message = '';
$msgType = '';

// ── Handle POST actions ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $member     = trim($_POST['member_name'] ?? '');
        $book       = trim($_POST['book_title']  ?? '');
        $borrowDate = $_POST['borrow_date'] ?? '';
        $dueDate    = $_POST['due_date']    ?? '';

        if ($member && $book && $borrowDate && $dueDate) {
            $pdo->prepare("INSERT INTO borrow_history (member_name,book_title,borrow_date,due_date,status) VALUES (?,?,?,?,'Borrowed')")
                ->execute([$member,$book,$borrowDate,$dueDate]);
            $message = 'Borrow record added!'; $msgType = 'success';
        } else { $message = 'All fields are required.'; $msgType = 'error'; }
    }

    elseif ($action === 'return') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("UPDATE borrow_history SET return_date=CURDATE(), status='Returned' WHERE id=? AND status != 'Returned'")
                ->execute([$id]);
            $message = 'Book marked as returned!'; $msgType = 'success';
        }
    }

    elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) { $pdo->prepare("DELETE FROM borrow_history WHERE id=?")->execute([$id]); $message = 'Record deleted.'; $msgType = 'success'; }
    }
}

// ── Fetch records ─────────────────────────────────────────────────────────────
$search = trim($_GET['search'] ?? '');
$status = $_GET['status']  ?? '';
$from   = $_GET['from']    ?? '';
$to     = $_GET['to']      ?? '';

$sql    = "SELECT * FROM borrow_history WHERE (member_name LIKE ? OR book_title LIKE ?)";
$params = ["%$search%","%$search%"];
if ($status) { $sql .= " AND status=?"; $params[] = $status; }
if ($from)   { $sql .= " AND borrow_date>=?"; $params[] = $from; }
if ($to)     { $sql .= " AND borrow_date<=?"; $params[] = $to;   }
$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$records = $stmt->fetchAll();

// Summary counts
$counts = $pdo->query("SELECT status, COUNT(*) as cnt FROM borrow_history GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$total    = array_sum($counts);
$borrowed = $counts['Borrowed'] ?? 0;
$returned = $counts['Returned'] ?? 0;
$overdue  = $counts['Overdue']  ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Borrow History | OpenLib</title>
  <link rel="stylesheet" href="borrow-history.css">
</head>
<body>

<header class="header">
  <div class="logo">Open<span>Lib</span></div>
  <nav class="navbar" id="navMenu">
    <a href="../Admin_dashboard/dashboard.php">Dashboard</a>
    <a href="../Register/register.php">Home</a>
    <a href="../admin_catlog/catalog.php">Catalog</a>
    <a href="../contact/contact.php">Contact</a>
    <a href="../About us/About us.php">About Us</a>
    <a href="../Login/user_login.php" class="btn login">Logout</a>
  </nav>
  <div class="menu-toggle" onclick="toggleMenu()">☰</div>
</header>

<section class="history-section">
  <div class="history-container">

    <div class="page-header">
      <h1>Borrow History</h1>
      <button class="btn-add" onclick="openModal('addModal')">+ Record Borrow</button>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="summary-grid">
      <div class="sum-card">
        <span class="sum-icon">📚</span>
        <div><p class="sum-label">Total Borrows</p><p class="sum-value"><?= $total ?></p></div>
      </div>
      <div class="sum-card">
        <span class="sum-icon">🔄</span>
        <div><p class="sum-label">Currently Borrowed</p><p class="sum-value"><?= $borrowed ?></p></div>
      </div>
      <div class="sum-card">
        <span class="sum-icon">✅</span>
        <div><p class="sum-label">Returned</p><p class="sum-value"><?= $returned ?></p></div>
      </div>
      <div class="sum-card <?= $overdue > 0 ? 'warn' : '' ?>">
        <span class="sum-icon">⚠️</span>
        <div><p class="sum-label">Overdue</p><p class="sum-value"><?= $overdue ?></p></div>
      </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="borrow-history.php" class="filter-bar">
      <input type="text" name="search" placeholder="🔍  Search member or book..." value="<?= htmlspecialchars($search) ?>">
      <select name="status">
        <option value="">All Status</option>
        <option value="Borrowed" <?= $status==='Borrowed'?'selected':'' ?>>Borrowed</option>
        <option value="Returned" <?= $status==='Returned'?'selected':'' ?>>Returned</option>
        <option value="Overdue"  <?= $status==='Overdue' ?'selected':'' ?>>Overdue</option>
      </select>
      <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" title="From date">
      <input type="date" name="to"   value="<?= htmlspecialchars($to) ?>"   title="To date">
      <button type="submit" class="btn-filter">Filter</button>
      <a href="borrow-history.php" class="btn-clear">Clear</a>
    </form>

    <div class="catalog-stats">Showing <b><?= count($records) ?></b> record<?= count($records)!==1?'s':'' ?></div>

    <div class="table-wrapper">
      <table class="history-table">
        <thead>
          <tr><th>#</th><th>Member</th><th>Book Title</th><th>Borrow Date</th><th>Due Date</th><th>Return Date</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if(empty($records)): ?>
          <tr><td colspan="8" class="no-data">No records found.</td></tr>
          <?php else: ?>
          <?php foreach($records as $i => $r):
            $sc = strtolower($r['status']);
          ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($r['member_name']) ?></td>
            <td><?= htmlspecialchars($r['book_title']) ?></td>
            <td><?= htmlspecialchars($r['borrow_date']) ?></td>
            <td><?= htmlspecialchars($r['due_date']) ?></td>
            <td><?= $r['return_date'] ? htmlspecialchars($r['return_date']) : '<span style="color:#aaa">—</span>' ?></td>
            <td><span class="status <?= $sc ?>"><?= htmlspecialchars($r['status']) ?></span></td>
            <td class="actions">
              <?php if($r['status'] !== 'Returned'): ?>
              <form method="POST" style="display:inline" onsubmit="return confirm('Mark as returned?')">
                <input type="hidden" name="action" value="return">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button type="submit" class="btn-return">Return</button>
              </form>
              <?php else: ?>
              <button class="btn-return disabled" disabled>Returned</button>
              <?php endif; ?>

              <form method="POST" style="display:inline" onsubmit="return confirm('Delete this record?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button type="submit" class="btn-delete">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</section>

<!-- ADD MODAL -->
<div class="modal-overlay" id="addModal">
  <div class="modal">
    <h2>Record New Borrow</h2>
    <form method="POST" action="borrow-history.php">
      <input type="hidden" name="action" value="add">
      <label>Member Name</label>
      <input type="text" name="member_name" required placeholder="Enter member name">
      <label>Book Title</label>
      <input type="text" name="book_title" required placeholder="Enter book title">
      <label>Borrow Date</label>
      <input type="date" name="borrow_date" required value="<?= date('Y-m-d') ?>">
      <label>Due Date</label>
      <input type="date" name="due_date" required value="<?= date('Y-m-d', strtotime('+14 days')) ?>">
      <div class="modal-actions">
        <button type="submit" class="btn-save">Save Record</button>
        <button type="button" class="btn-cancel" onclick="closeModal('addModal')">Cancel</button>
      </div>
    </form>
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
          <li><a href="../Admin_dashboard/dashboard.php">Dashboard</a></li>
          <li><a href="../Home/index.php">Home</a></li>
          <li><a href="../admin_catlog/catalog.php">Book Catalog</a></li>
          <li><a href="../contact/contact.php">Contact</a></li>
          <li><a href="../About us/About us.php">About Us</a></li>
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
    <div class="footer-bottom"><p>PageTurn Library. All rights reserved.</p></div>
  </div>
</footer>

<script>
function toggleMenu(){ document.getElementById('navMenu').classList.toggle('open'); }
function openModal(id){ document.getElementById(id).classList.add('active'); }
function closeModal(id){ document.getElementById(id).classList.remove('active'); }
document.querySelectorAll('.modal-overlay').forEach(el=>{
  el.addEventListener('click',function(e){ if(e.target===this) this.classList.remove('active'); });
});
const alertEl = document.querySelector('.alert');
if(alertEl) setTimeout(()=>alertEl.style.display='none', 3500);
</script>
</body>
</html>
