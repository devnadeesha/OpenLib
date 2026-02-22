<?php
require_once '../db_connect.php';

$message = '';
$msgType = '';

// ── Handle POST (Add / Edit / Delete) ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title  = trim($_POST['title']  ?? '');
        $author = trim($_POST['author'] ?? '');
        $genre  = $_POST['genre']  ?? '';
        $isbn   = trim($_POST['isbn']   ?? '');
        $status = $_POST['status'] ?? 'Available';

        if ($title && $author && $genre && $isbn) {
            $check = $pdo->prepare("SELECT id FROM books WHERE isbn = ?");
            $check->execute([$isbn]);
            if ($check->fetch()) {
                $message = 'A book with this ISBN already exists.'; $msgType = 'error';
            } else {
                $pdo->prepare("INSERT INTO books (title,author,genre,isbn,status) VALUES (?,?,?,?,?)")
                    ->execute([$title,$author,$genre,$isbn,$status]);
                $message = 'Book added successfully!'; $msgType = 'success';
            }
        } else { $message = 'All fields are required.'; $msgType = 'error'; }
    }

    elseif ($action === 'edit') {
        $id     = (int)($_POST['id'] ?? 0);
        $title  = trim($_POST['title']  ?? '');
        $author = trim($_POST['author'] ?? '');
        $genre  = $_POST['genre']  ?? '';
        $isbn   = trim($_POST['isbn']   ?? '');
        $status = $_POST['status'] ?? 'Available';
        if ($id && $title && $author && $genre && $isbn) {
            $pdo->prepare("UPDATE books SET title=?,author=?,genre=?,isbn=?,status=? WHERE id=?")
                ->execute([$title,$author,$genre,$isbn,$status,$id]);
            $message = 'Book updated!'; $msgType = 'success';
        }
    }

    elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) { $pdo->prepare("DELETE FROM books WHERE id=?")->execute([$id]); $message = 'Book deleted.'; $msgType = 'success'; }
    }
}

// ── Fetch books ───────────────────────────────────────────────────────────────
$search = trim($_GET['search'] ?? '');
$genre  = $_GET['genre']  ?? '';
$status = $_GET['status'] ?? '';
$sql    = "SELECT * FROM books WHERE (title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
$params = ["%$search%","%$search%","%$search%"];
if ($genre)  { $sql .= " AND genre=?";  $params[] = $genre;  }
if ($status) { $sql .= " AND status=?"; $params[] = $status; }
$sql .= " ORDER BY id ASC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$books = $stmt->fetchAll();

$badgeMap = ['Programming'=>'badge-prog','Fiction'=>'badge-fic','Science'=>'badge-sci','History'=>'badge-hist','Self-Help'=>'badge-self'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book Catalog | OpenLib</title>
  <link rel="stylesheet" href="catalog.css">
</head>
<body>

<header class="header">
  <div class="logo">Open<span>Lib</span></div>
  <nav class="navbar" id="navMenu">
    <a href="../Admin_dashboard/dashboard.php">Dashboard</a>
    <a href="../Home/index.php">Home</a>
    <a href="../admin_catlog/catalog.php" class="active">Catalog</a>
    <a href="../contact/contact.php">Contact</a>
    <a href="../About us/About us.php">About Us</a>
    <a href="../Login/user_login.php" class="btn logout">Logout</a>
  </nav>
  <div class="menu-toggle" onclick="toggleMenu()">☰</div>
</header>

<section class="catalog-section">
  <div class="catalog-container">

    <div class="catalog-header">
      <h1>Book Catalog</h1>
      <button class="btn-add" onclick="openModal('addModal')">+ Add New Book</button>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?= $msgType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Filter form (GET) -->
    <form method="GET" action="catalog.php" class="filter-bar">
      <input type="text" name="search" placeholder="🔍  Search title, author, ISBN..." value="<?= htmlspecialchars($search) ?>">
      <select name="genre">
        <option value="">All Genres</option>
        <?php foreach(['Programming','Fiction','Science','History','Self-Help'] as $g): ?>
        <option value="<?= $g ?>" <?= $genre===$g?'selected':'' ?>><?= $g ?></option>
        <?php endforeach; ?>
      </select>
      <select name="status">
        <option value="">All Status</option>
        <option value="Available" <?= $status==='Available'?'selected':'' ?>>Available</option>
        <option value="Borrowed"  <?= $status==='Borrowed' ?'selected':'' ?>>Borrowed</option>
      </select>
      <button type="submit" class="btn-filter">Filter</button>
      <a href="catalog.php" class="btn-clear">Clear</a>
    </form>

    <div class="catalog-stats">Showing <b><?= count($books) ?></b> book<?= count($books)!==1?'s':'' ?></div>

    <div class="table-wrapper">
      <table class="book-table">
        <thead>
          <tr><th>#</th><th>Title</th><th>Author</th><th>Genre</th><th>ISBN</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if(empty($books)): ?>
          <tr><td colspan="7" class="no-data">No books found. Try a different search or add a new book.</td></tr>
          <?php else: ?>
          <?php foreach($books as $i => $b):
            $bc = $badgeMap[$b['genre']] ?? 'badge-prog';
            $sc = isset($b['status']) ? strtolower($b['status']) : 'available';
          ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($b['title']) ?></td>
            <td><?= htmlspecialchars($b['author']) ?></td>
            <td><span class="badge <?= $bc ?>"><?= htmlspecialchars($b['genre']) ?></span></td>
            <td><?= htmlspecialchars($b['isbn']) ?></td>
      <td><span class="status <?= $sc ?>"><?= isset($b['status']) ? htmlspecialchars($b['status']) : 'Available' ?></span></td>            <td class="actions">
<button class="btn-edit" onclick='openEditModal(<?= $b["id"] ?>,"<?= htmlspecialchars($b["title"], ENT_QUOTES) ?>","<?= htmlspecialchars($b["author"], ENT_QUOTES) ?>","<?= htmlspecialchars($b["genre"], ENT_QUOTES) ?>","<?= htmlspecialchars($b["isbn"], ENT_QUOTES) ?>","<?= isset($b["status"]) ? htmlspecialchars($b["status"], ENT_QUOTES) : "Available" ?>")'>Edit</button>              <form method="POST" style="display:inline" onsubmit="return confirm('Delete this book?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $b['id'] ?>">
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
    <h2>Add New Book</h2>
    <form method="POST" action="catalog.php">
      <input type="hidden" name="action" value="add">
      <label>Title</label><input type="text" name="title" required placeholder="Book title">
      <label>Author</label><input type="text" name="author" required placeholder="Author name">
      <label>Genre</label>
      <select name="genre" required>
        <option value="">-- Select Genre --</option>
        <?php foreach(['Programming','Fiction','Science','History','Self-Help'] as $g): ?>
        <option value="<?= $g ?>"><?= $g ?></option>
        <?php endforeach; ?>
      </select>
      <label>ISBN</label><input type="text" name="isbn" required placeholder="978-XXXXXXXXXX">
      <label>Status</label>
      <select name="status">
        <option value="Available">Available</option>
        <option value="Borrowed">Borrowed</option>
      </select>
      <div class="modal-actions">
        <button type="submit" class="btn-save">Add Book</button>
        <button type="button" class="btn-cancel" onclick="closeModal('addModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <h2>Edit Book</h2>
    <form method="POST" action="catalog.php">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" id="editId">
      <label>Title</label><input type="text" name="title" id="editTitle" required>
      <label>Author</label><input type="text" name="author" id="editAuthor" required>
      <label>Genre</label>
      <select name="genre" id="editGenre">
        <?php foreach(['Programming','Fiction','Science','History','Self-Help'] as $g): ?>
        <option value="<?= $g ?>"><?= $g ?></option>
        <?php endforeach; ?>
      </select>
      <label>ISBN</label><input type="text" name="isbn" id="editISBN" required>
      <label>Status</label>
      <select name="status" id="editStatus">
        <option value="Available">Available</option>
        <option value="Borrowed">Borrowed</option>
      </select>
      <div class="modal-actions">
        <button type="submit" class="btn-save">Save Changes</button>
        <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
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
          <li><a href="../dashboard/dashboard.php">Dashboard</a></li>
          <li><a href="../Home/index.php">Home</a></li>
          <li><a href="catalog.php">Book Catalog</a></li>
          <li><a href="../contact us/contact.php">Contact</a></li>
          <li><a href="../Abou_us/about_us.php">About Us</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Account</h3>
        <ul>
          <li><a href="../Login page/user_login.php">Log In</a></li>
          <li><a href="../Register/user_register.php">Sign Up</a></li>
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
function openEditModal(id,title,author,genre,isbn,status){
  document.getElementById('editId').value     = id;
  document.getElementById('editTitle').value  = title;
  document.getElementById('editAuthor').value = author;
  document.getElementById('editGenre').value  = genre;
  document.getElementById('editISBN').value   = isbn;
  document.getElementById('editStatus').value = status;
  openModal('editModal');
}
document.querySelectorAll('.modal-overlay').forEach(el=>{
  el.addEventListener('click',function(e){ if(e.target===this) this.classList.remove('active'); });
});
const alertEl = document.querySelector('.alert');
if(alertEl) setTimeout(()=>alertEl.style.display='none', 3000);
</script>
</body>
</html>
