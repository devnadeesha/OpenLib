
<?php
// ADD BOOK HANDLER - PHP/MySQL Integration
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is admin (optional security check)
// For now, we'll allow anyone to add books, but in production add user role check

require_once '../config/db.php';
require_once '../config/security.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON data from request
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $required_fields = ['title', 'author', 'isbn', 'genre', 'year', 'pages', 'quantity'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }

    // Sanitize and validate input
    $title = sanitizeInput($input['title']);
    $author = sanitizeInput($input['author']);
    $isbn = sanitizeInput($input['isbn']);
    $genre = sanitizeInput($input['genre']);
    $year = (int)$input['year'];
    $pages = (int)$input['pages'];
    $publisher = sanitizeInput($input['publisher'] ?? '');
    $quantity = (int)$input['quantity'];
    $description = sanitizeInput($input['description'] ?? '');

    // Validate lengths
    if (strlen($title) < 3 || strlen($title) > 100) {
        throw new Exception("Book title must be between 3-100 characters");
    }

    if (strlen($author) < 3 || strlen($author) > 50) {
        throw new Exception("Author name must be between 3-50 characters");
    }

    if ($year < 1000 || $year > date('Y') + 1) {
        throw new Exception("Invalid publication year");
    }

    if ($pages < 1) {
        throw new Exception("Pages must be at least 1");
    }

    if ($quantity < 1) {
        throw new Exception("Quantity must be at least 1");
    }

    // Check if ISBN already exists
    $check_isbn = $conn->prepare("SELECT id FROM books WHERE isbn = ?");
    $check_isbn->bind_param("s", $isbn);
    $check_isbn->execute();
    $result = $check_isbn->get_result();

    if ($result->num_rows > 0) {
        throw new Exception("A book with this ISBN already exists");
    }
    $check_isbn->close();

    // Insert book into database
    $insert_book = $conn->prepare(
        "INSERT INTO books (title, author, isbn, genre, publication_year, pages, publisher, quantity, available_quantity, description, added_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
    );

    $available_quantity = $quantity; // Initially all books are available

    $insert_book->bind_param(
        "ssssiiisss",
        $title,
        $author,
        $isbn,
        $genre,
        $year,
        $pages,
        $publisher,
        $quantity,
        $available_quantity,
        $description
    );

    if (!$insert_book->execute()) {
        throw new Exception("Error adding book: " . $insert_book->error);
    }

    $book_id = $conn->insert_id;
    $insert_book->close();

    // Log this activity
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $activity = "New book added: $title";
        
        $log_activity = $conn->prepare(
            "INSERT INTO activities (user_id, activity, activity_date) VALUES (?, ?, NOW())"
        );
        $log_activity->bind_param("is", $user_id, $activity);
        $log_activity->execute();
        $log_activity->close();
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Book added successfully',
        'book_id' => $book_id,
        'book' => [
            'id' => $book_id,
            'title' => $title,
            'author' => $author,
            'isbn' => $isbn,
            'genre' => $genre,
            'year' => $year,
            'pages' => $pages,
            'quantity' => $quantity
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
