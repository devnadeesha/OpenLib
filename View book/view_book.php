<?php
session_start();

// ── DB CONNECTION ─────────────────────────────────────────────────────────────
$conn = new mysqli('localhost', 'root', '', 'openlib');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ── SEARCH & FILTER ───────────────────────────────────────────────────────────
$search = trim($_GET['search'] ?? '');
$genre  = trim($_GET['genre']  ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 12;
$offset = ($page - 1) * $limit;

$where  = "WHERE 1=1";
$params = [];
$types  = '';

if ($search) {
    $where   .= " AND (title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
    $like     = "%$search%";
    $params   = array_merge($params, [$like, $like, $like]);
    $types   .= 'sss';
}
if ($genre) {
    $where   .= " AND genre = ?";
    $params[] = $genre;
    $types   .= 's';
}

// Total count
$countStmt = $conn->prepare("SELECT COUNT(*) FROM books $where");
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countStmt->bind_result($total);
$countStmt->fetch();
$countStmt->close();
$totalPages = max(1, (int)ceil($total / $limit));

// Books query
$allParams  = array_merge($params, [$limit, $offset]);
$allTypes   = $types . 'ii';
$stmt = $conn->prepare("
    SELECT id, title, author, genre, publication_year,
           available_quantity, quantity, cover_image, description
    FROM books
    $where
    ORDER BY added_date DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// All genres for filter dropdown
$genres = $conn->query("SELECT DISTINCT genre FROM books ORDER BY genre")
    ->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Books | OpenLib</title>
    <link rel="stylesheet" href="view_book.css">
</head>

<body>

    <!-- HEADER -->
    <header class="header">
        <div class="logo">Open<span>Lib</span></div>
        <nav class="navbar">
            <a href="index.php" class="active">Home</a>
            <a href="../About/about_us.php">About Us</a>
            <a href="../contact/contact.php">Contact</a>
            <a href="../Login/user_login.php" class="btn btn-primary">Login</a>
            <a href="../Register/register.php" class="btn btn-primary">Sign Up</a>
        </nav>
    </header>

    <main class="main">
        <h1 class="page-title">Browse Books</h1>

        <!-- SEARCH & FILTER -->
        <form method="GET" class="search-bar">
            <input type="text" name="search"
                placeholder="Search by title, author or ISBN..."
                value="<?= htmlspecialchars($search) ?>">
            <select name="genre">
                <option value="">All Genres</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= htmlspecialchars($g['genre']) ?>"
                        <?= $genre === $g['genre'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['genre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="browse_books.php" class="btn btn-secondary">Reset</a>
        </form>

        <!-- RESULTS COUNT -->
        <p class="results-count">
            <?= $total ?> book<?= $total !== 1 ? 's' : '' ?> found
            <?= $search ? " for \"" . htmlspecialchars($search) . "\"" : '' ?>
            <?= $genre  ? " in " . htmlspecialchars($genre) : '' ?>
        </p>

        <!-- BOOKS GRID -->
        <?php if (empty($books)): ?>
            <div class="empty">
                <p>📚 No books found. Try a different search.</p>
            </div>
        <?php else: ?>
            <div class="books-grid">
                <?php foreach ($books as $book): ?>
                    <a href="view_book.php?id=<?= $book['id'] ?>" class="book-card">
                        <div class="book-cover">
                            <?php if (!empty($book['cover_image'])): ?>
                                <img src="../Manage_books/<?= htmlspecialchars($book['cover_image']) ?>"
                                    alt="<?= htmlspecialchars($book['title']) ?>">
                            <?php else: ?>
                                <div class="no-cover">📖</div>
                            <?php endif; ?>
                        </div>
                        <div class="book-info">
                            <h3 class="book-title"><?= htmlspecialchars($book['title']) ?></h3>
                            <p class="book-author">by <?= htmlspecialchars($book['author']) ?></p>
                            <span class="book-genre"><?= htmlspecialchars($book['genre']) ?></span>
                            <div class="book-availability">
                                <?php if ($book['available_quantity'] > 0): ?>
                                    <span class="available">✔ Available (<?= $book['available_quantity'] ?>)</span>
                                <?php else: ?>
                                    <span class="unavailable">✘ Not Available</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&genre=<?= urlencode($genre) ?>">← Prev</a>
                <?php endif; ?>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&genre=<?= urlencode($genre) ?>"
                        class="<?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&genre=<?= urlencode($genre) ?>">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-inner">
            <div class="footer-col">
                <span class="footer-logo">OpenLib</span>
                <p>Your gateway to knowledge.</p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <a href="../dashboard/dashboard.php">Dashboard</a>
                <a href="../Home/Main.html">Home</a>
                <a href="browse_books.php">Catalog</a>
            </div>
            <div class="footer-col">
                <h4>Account</h4>
                <a href="../Login/user_login.php">Log In</a>
                <a href="../Register/register.php">Sign Up</a>
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

</body>

</html>