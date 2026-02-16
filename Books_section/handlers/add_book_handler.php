<?php
session_start();
require_once '../../db_connect.php'; // database connection eka hadaganna mula 

// Only allow POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Invalid Request");
}

// Get form values
$title = $_POST['title'];
$author = $_POST['author'];
$isbn = $_POST['isbn'];
$genre = $_POST['genre'];
$year = $_POST['year'];
$pages = $_POST['pages'];
$publisher = $_POST['publisher'];
$quantity = $_POST['quantity'];
$description = $_POST['description'];

// Simple validation
if (empty($title) || empty($author) || empty($isbn)) {
    die("Please fill required fields");
}

// ============================
// IMAGE UPLOAD (Simple) section eka hadana thana 
// ============================

$cover_image = "";// crud eke update eka thamai methana yanne 

if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {

    $upload_folder = "../upload_images_of_books_admin/";

    
    if (!is_dir($upload_folder)) {
        mkdir($upload_folder, 0777, true);
    }

    $file_name = time() . "_" . $_FILES['cover_image']['name'];
    $file_tmp = $_FILES['cover_image']['tmp_name'];

    move_uploaded_file($file_tmp, $upload_folder . $file_name);

    $cover_image = $file_name;
}

// ============================
// INSERT INTO DATABASE
// ============================

$sql = "INSERT INTO books(title, author, isbn, genre, publication_year, pages, publisher, quantity, available_quantity, description, cover_image)

('$title', '$author', '$isbn', '$genre', '$year', '$pages', '$publisher',
 '$quantity', '$available_quantity', '$description', '$cover_image')";

// $stmt = $conn->prepare($sql); meka dannae owata berenna  ❌ SQL Injection ❌ Hackers inserting malicious SQL

$available_quantity = $quantity;

/*$stmt->bind_param(
    "ssssiiisiss",
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
    $cover_image
);
*/
//            👀😎   -----mama nikan sql nikan run wenna dunna  meka prevent karanna ona na --------
if ($conn->query($sql)) {
    echo "Book Added Successfully!";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
//$stmt->close();

?>
