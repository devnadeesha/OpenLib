<?php
// ── DB CONNECTION ─────────────────────────────────────────────────────────────
$host   = 'localhost';
$dbname = 'openlib';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // fixes LIMIT/OFFSET int issue
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// ── ACTIONS ───────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $id = (int)$_POST['id'];

    switch ($_POST['action']) {
        case 'return':
            $pdo->prepare("UPDATE borrow_records SET status='returned', return_date=CURDATE(), updated_at=NOW() WHERE id=?")
                ->execute([$id]);
            $pdo->prepare("UPDATE books b JOIN borrow_records br ON b.id=br.book_id SET b.available_quantity=b.available_quantity+1 WHERE br.id=?")
                ->execute([$id]);
            break;

        case 'overdue':
            $pdo->prepare("UPDATE borrow_records SET status='overdue', updated_at=NOW() WHERE id=?")
                ->execute([$id]);
            break;

        case 'update':
            $pdo->prepare("UPDATE borrow_records SET fine_amount=?, notes=?, updated_at=NOW() WHERE id=?")
                ->execute([(float)$_POST['fine'], trim($_POST['notes']), $id]);
            break;

        case 'delete':
            $pdo->prepare("DELETE FROM borrow_records WHERE id=?")->execute([$id]);
            break;
    }

    header("Location: borrow_history.php?msg=" . $_POST['action']);
    exit;
}

// ── SEARCH & FILTER ───────────────────────────────────────────────────────────
$search  = trim($_GET['search'] ?? '');
$status  = $_GET['status'] ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 10;
$offset  = ($page - 1) * $limit;

$where  = "WHERE 1=1";
$params = [];

if ($search) {
    // search by book title OR member first/last name OR email
    $where   .= " AND (b.title LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $like     = "%$search%";
    $params   = [$like, $like, $like, $like];
}
if ($status) {
    $where   .= " AND br.status=?";
    $params[] = $status;
}

// Total count for pagination
$countStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM borrow_records br
    JOIN users u ON br.user_id = u.user_id
    JOIN books b ON br.book_id = b.id
    $where
");
$countStmt->execute($params);
$total      = $countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $limit));

// Main records query
$stmt = $pdo->prepare("
    SELECT br.id,
           CONCAT(u.first_name, ' ', u.last_name) AS member,
           u.email,
           b.title,
           b.author,
           br.borrow_date,
           br.due_date,
           br.return_date,
           br.status,
           br.fine_amount,
           br.notes
    FROM borrow_records br
    JOIN users u ON br.user_id = u.user_id
    JOIN books b ON br.book_id = b.id
    $where
    ORDER BY br.borrow_date DESC
    LIMIT ? OFFSET ?
");
$stmt->execute(array_merge($params, [$limit, $offset]));
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Summary stats
$stats = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'borrowed') AS borrowed,
        SUM(status = 'returned') AS returned,
        SUM(status = 'overdue')  AS overdue,
        COALESCE(SUM(fine_amount), 0) AS fines
    FROM borrow_records
")->fetch(PDO::FETCH_ASSOC);

// Toast messages
$toasts = [
    'return'  => 'Book marked as returned.',
    'overdue' => 'Record marked as overdue.',
    'update'  => 'Record updated.',
    'delete'  => 'Record deleted.',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Borrow History | OpenLib</title>
  <link rel="stylesheet" href="borrow_history.css">
</head>
<body>

<!-- HEADER -->
<header class="header">
  <div class="logo">Open<span>Lib</span></div>
  <nav class="navbar">
    <a href="../dashboard/dashboard.html">Dashboard</a>
    <a href="../Home/Main.html">Home</a>
    <a href="#">Catalog</a>
    <a href="../contact us/contact.html">Contact</a>
    <a href="../Abou_us/about_us.html">About Us</a>
    <a href="../Login/user_login.php" class="btn-logout">Logout</a>
  </nav>
</header>

<main class="main">
  <h1 class="page-title">Borrow History</h1>

  <!-- TOAST -->
  <?php if (isset($_GET['msg']) && isset($toasts[$_GET['msg']])): ?>
    <div class="toast" id="toast"><?= $toasts[$_GET['msg']] ?></div>
  <?php endif; ?>

  <!-- STATS -->
  <div class="stats-grid">
    <div class="stat-card"><h3>Total</h3><p><?= $stats['total'] ?></p></div>
    <div class="stat-card"><h3>Borrowed</h3><p><?= $stats['borrowed'] ?></p></div>
    <div class="stat-card"><h3>Returned</h3><p><?= $stats['returned'] ?></p></div>
    <div class="stat-card overdue-card"><h3>Overdue</h3><p><?= $stats['overdue'] ?></p></div>
    <div class="stat-card"><h3>Total Fines</h3><p>$<?= number_format($stats['fines'], 2) ?></p></div>
  </div>

  <!-- SEARCH -->
  <form method="GET" class="search-bar">
    <input type="text" name="search"
           placeholder="Search by book title or member name..."
           value="<?= htmlspecialchars($search) ?>">
    <select name="status">
      <option value="">All Status</option>
      <option value="borrowed" <?= $status === 'borrowed' ? 'selected' : '' ?>>Borrowed</option>
      <option value="returned" <?= $status === 'returned' ? 'selected' : '' ?>>Returned</option>
      <option value="overdue"  <?= $status === 'overdue'  ? 'selected' : '' ?>>Overdue</option>
    </select>
    <button type="submit" class="btn btn-primary">Search</button>
    <a href="borrow_history.php" class="btn btn-secondary">Reset</a>
  </form>

  <!-- TABLE -->
  <div class="table-wrap">
    <?php if (empty($records)): ?>
      <p class="empty">No records found.</p>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Member</th>
          <th>Book</th>
          <th>Borrow Date</th>
          <th>Due Date</th>
          <th>Return Date</th>
          <th>Status</th>
          <th>Fine</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($records as $i => $r): ?>
        <tr>
          <td><?= $offset + $i + 1 ?></td>
          <td>
            <?= htmlspecialchars($r['member']) ?><br>
            <small><?= htmlspecialchars($r['email']) ?></small>
          </td>
          <td>
            <?= htmlspecialchars($r['title']) ?><br>
            <small><?= htmlspecialchars($r['author']) ?></small>
          </td>
          <td><?= $r['borrow_date'] ?></td>
          <td><?= $r['due_date'] ?></td>
          <td><?= $r['return_date'] ?? '—' ?></td>
          <td><span class="badge <?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
          <td><?= $r['fine_amount'] > 0 ? '$' . number_format($r['fine_amount'], 2) : '—' ?></td>
          <td class="actions">
            <?php if ($r['status'] === 'borrowed'): ?>
              <form method="POST">
                <input type="hidden" name="action" value="return">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="btn btn-success btn-sm">Return</button>
              </form>
              <form method="POST">
                <input type="hidden" name="action" value="overdue">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button class="btn btn-warning btn-sm">Overdue</button>
              </form>
            <?php endif; ?>
            <button class="btn btn-info btn-sm" onclick="openModal(
              <?= $r['id'] ?>,
              '<?= addslashes(htmlspecialchars($r['member'])) ?>',
              '<?= addslashes(htmlspecialchars($r['title'])) ?>',
              <?= (float)$r['fine_amount'] ?>,
              '<?= addslashes(htmlspecialchars($r['notes'] ?? '')) ?>'
            )">Edit</button>
            <form method="POST" onsubmit="return confirm('Delete this record?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- PAGINATION -->
  <?php if ($totalPages > 1): ?>
  <div class="pagination">
    <?php if ($page > 1): ?>
      <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>">← Prev</a>
    <?php endif; ?>
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
      <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>"
         class="<?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
      <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>">Next →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</main>

<!-- EDIT MODAL -->
<div class="overlay" id="overlay" onclick="closeModal()"></div>
<div class="modal" id="modal">
  <h2>Edit Record</h2>
  <form method="POST">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" id="m_id">
    <div class="field">
      <label>Member</label>
      <input type="text" id="m_member" readonly>
    </div>
    <div class="field">
      <label>Book</label>
      <input type="text" id="m_book" readonly>
    </div>
    <div class="field">
      <label>Fine Amount ($)</label>
      <input type="number" name="fine" id="m_fine" step="0.01" min="0">
    </div>
    <div class="field">
      <label>Notes</label>
      <textarea name="notes" id="m_notes" rows="3"></textarea>
    </div>
    <div class="modal-btns">
      <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
      <button type="submit" class="btn btn-primary">Save</button>
    </div>
  </form>
</div>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-col">
      <span class="footer-logo">OpenLib</span>
      <p>Your gateway to knowledge.</p>
    </div>
    <div class="footer-col">
      <h4>Quick Links</h4>
      <a href="../dashboard/dashboard.html">Dashboard</a>
      <a href="../Home/Main.html">Home</a>
      <a href="#">Catalog</a>
    </div>
    <div class="footer-col">
      <h4>Account</h4>
      <a href="../Login page/user_login.html">Log In</a>
      <a href="../Register/user_register.html">Sign Up</a>
    </div>
    <div class="footer-col">
      <h4>Hours</h4>
      <p>Mon–Fri: 8am–9pm</p>
      <p>Sat: 9am–6pm</p>
      <p>Sun: 10am–5pm</p>
    </div>
  </div>
  <p class="footer-bottom">PageTurn Library. All rights reserved.</p>
</footer>

<script src="borrow_history.js"></script>
</body>
</html>