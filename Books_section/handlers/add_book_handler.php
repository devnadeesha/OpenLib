
<?php
// ADD BOOK HANDLER - PHP/MySQL Integration with Image Upload
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

// Create uploads directory if it doesn't exist
$uploads_dir = '../uploads/books';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

try {
    // Check if form data or JSON
    $input = [];
    
    if (!empty($_POST)) {
        $input = $_POST;
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
    }

    // Validate required fields
    $required_fields = ['title', 'author', 'isbn', 'genre', 'year', 'pages', 'quantity'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Handle image upload
    $cover_image_path = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['cover_image'];
        
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception("Invalid image type. Allowed: JPG, PNG, WebP");
        }
        
        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception("Image file is too large. Maximum 5MB allowed");
        }
        
        // Validate file error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error uploading file");
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('book_') . '.' . $extension;
        $filepath = $uploads_dir . '/' . $filename;
        
        // Move file to uploads directory
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception("Failed to save image file");
        }
        
        $cover_image_path = 'uploads/books/' . $filename;
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
        "INSERT INTO books (title, author, isbn, genre, publication_year, pages, publisher, quantity, available_quantity, description, cover_image, added_date)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
    );

    $available_quantity = $quantity; // Initially all books are available

    $insert_book->bind_param(
        "ssssiisssss",
        $title,
        $author,
        $isbn,
        $genre,
        $year,
        $pages,
        $publisher,
        $quantity,
        $available_quantity,
        $description,
        $cover_image_path
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
