<?php
session_start();
$conn = new mysqli("localhost","root","","library_db");

// login system eken enne
$user_id = $_SESSION['user_id'] ?? 1;

$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();
$books = $conn->query("SELECT * FROM borrow_books WHERE user_id='$user_id'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Profile</title>
<link rel="stylesheet" href="user_detail.css">
</head>
<body>

<div class="container">

  <div class="profile-card">
    <img src="<?php echo $user['photo']; ?>" alt="Profile">
    <h2><?php echo $user['name']; ?></h2>
    <p><?php echo $user['email']; ?></p>

    <div class="info">
      <div><b>Library Code:</b> <?php echo $user['library_code']; ?></div>
      <div><b>Fines:</b> Rs. <?php echo $user['fine']; ?></div>
    </div>

    <a href="../auth/logout.php" class="btn logout">Logout</a>
  </div>

  <div class="book-card">
    <h3>📚 Taken Books</h3>

    <table>
      <tr>
        <th>Book Name</th>
        <th>Borrow Date</th>
        <th>Return Date</th>
      </tr>

      <?php while($row = $books->fetch_assoc()){ ?>
      <tr>
        <td><?php echo $row['book_name']; ?></td>
        <td><?php echo $row['borrow_date']; ?></td>
        <td><?php echo $row['return_date']; ?></td>
      </tr>
      <?php } ?>

    </table>

  </div>

</div>

<script src="script.js"></script>
</body>
</html>
