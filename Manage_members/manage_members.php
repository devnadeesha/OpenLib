<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'openlib';
$username = 'root'; // Change this to your database username
$password = '';     // Change this to your database password

// Create connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if user is admin (you should implement proper authentication)
// For now, we'll just check if there's a user session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Uncomment the line below when you have proper authentication
    // header("Location: ../Login/user_login.php");
    // exit();
}

$errors = [];
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            handleAdd($conn, $errors, $success);
            break;
        case 'edit':
            handleEdit($conn, $errors, $success);
            break;
        case 'delete':
            handleDelete($conn, $errors, $success);
            break;
    }
    
    // Store messages in session and redirect to avoid form resubmission
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    }
    if (!empty($success)) {
        $_SESSION['success'] = $success;
    }
    header("Location: manage_members.php");
    exit();
}

// Functions to handle CRUD operations
function handleAdd($conn, &$errors, &$success) {
    $f_name = trim($_POST['f_name'] ?? '');
    $l_name = trim($_POST['l_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($f_name) || empty($l_name) || empty($email) || empty($password)) {
        $errors[] = "All fields are required.";
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
        return;
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
        return;
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email already exists.";
        return;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    try {
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$f_name, $l_name, $email, $hashed_password, $role, $status]);
        $success = "Member added successfully!";
    } catch(PDOException $e) {
        $errors[] = "Error adding member: " . $e->getMessage();
    }
}

function handleEdit($conn, &$errors, &$success) {
    $user_id = $_POST['user_id'] ?? 0;
    $f_name = trim($_POST['f_name'] ?? '');
    $l_name = trim($_POST['l_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($f_name) || empty($l_name) || empty($email)) {
        $errors[] = "All fields except password are required.";
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
        return;
    }
    
    // Check if email exists for another user
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email already exists for another user.";
        return;
    }
    
    // Update user
    try {
        if (!empty($password)) {
            // Update with new password
            if (strlen($password) < 6) {
                $errors[] = "Password must be at least 6 characters long.";
                return;
            }
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, password = ?, role = ?, status = ? WHERE user_id = ?");
            $stmt->execute([$f_name, $l_name, $email, $hashed_password, $role, $status, $user_id]);
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, status = ? WHERE user_id = ?");
            $stmt->execute([$f_name, $l_name, $email, $role, $status, $user_id]);
        }
        $success = "Member updated successfully!";
    } catch(PDOException $e) {
        $errors[] = "Error updating member: " . $e->getMessage();
    }
}

function handleDelete($conn, &$errors, &$success) {
    $user_id = $_POST['user_id'] ?? 0;
    
    if (empty($user_id)) {
        $errors[] = "Invalid user ID.";
        return;
    }
    
    // Prevent deleting yourself (if you implement user authentication)
    // if ($user_id == $_SESSION['user_id']) {
    //     $errors[] = "You cannot delete your own account.";
    //     return;
    // }
    
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $success = "Member deleted successfully!";
    } catch(PDOException $e) {
        $errors[] = "Error deleting member: " . $e->getMessage();
    }
}

// Fetch all users
$stmt = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Members | OpenLib</title>
  <link rel="stylesheet" href="manage_members.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<!-- HEADER -->
<header class="header">
  <div class="logo">Open<span>Lib</span></div>

  <nav class="navbar" id="navMenu">
    <a href="../dashboard/dashboard.html">Dashboard</a>
    <a href="../Home/Main.html">Home</a>
    <a href="#">Catalog</a>
    <a href="../contact us/contact.html">Contact</a>
    <a href="../Abou_us/about_us.html">About Us</a>
    <a href="../Login/user_login.php" class="btn-nav">Logout</a>
  </nav>

  <div class="menu-toggle" onclick="toggleMenu()">☰</div>
</header>

<!-- MAIN CONTENT -->
<div class="container">
  <div class="page-header">
    <h1>Manage Members</h1>
    <button class="btn-add" onclick="openModal()">
      <i class='bx bx-plus'></i> Add New Member
    </button>
  </div>

  <?php
  if (isset($_SESSION['success'])) {
      echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
      unset($_SESSION['success']);
  }
  
  if (isset($_SESSION['errors'])) {
      echo '<div class="alert alert-danger">';
      foreach ($_SESSION['errors'] as $error) {
          echo '<p>' . htmlspecialchars($error) . '</p>';
      }
      echo '</div>';
      unset($_SESSION['errors']);
  }
  ?>

  <div class="search-box">
    <i class='bx bx-search'></i>
    <input type="text" id="searchInput" placeholder="Search members by name or email..." onkeyup="searchMembers()">
  </div>

  <div class="table-wrapper">
    <table class="members-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Status</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="membersTableBody">
        <?php foreach ($users as $user): ?>
        <tr data-user-id="<?php echo $user['user_id']; ?>">
          <td><?php echo htmlspecialchars($user['user_id']); ?></td>
          <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
          <td><?php echo htmlspecialchars($user['email']); ?></td>
          <td>
            <span class="role-badge role-<?php echo $user['role']; ?>">
              <?php echo ucfirst($user['role']); ?>
            </span>
          </td>
          <td>
            <span class="status-badge status-<?php echo $user['status']; ?>">
              <?php echo ucfirst($user['status']); ?>
            </span>
          </td>
          <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
          <td>
            <div class="action-buttons">
              <button class="btn-action btn-edit" onclick="openModal(<?php echo $user['user_id']; ?>)" title="Edit">
                <i class='bx bx-edit'></i>
              </button>
              <button class="btn-action btn-delete" onclick="openDeleteModal(<?php echo $user['user_id']; ?>)" title="Delete">
                <i class='bx bx-trash'></i>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        
        <?php if (empty($users)): ?>
        <tr>
          <td colspan="7" style="text-align: center; padding: 40px;">
            No members found. Click "Add New Member" to get started.
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ADD/EDIT MODAL -->
<div id="memberModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h2 id="modalTitle">Add New Member</h2>
    
    <form id="memberForm" action="manage_members.php" method="POST" autocomplete="off">
      <input type="hidden" id="user_id" name="user_id">
      <input type="hidden" id="action" name="action" value="add">
      
      <div class="input-box">
        <label>First Name</label>
        <input type="text" id="f_name" placeholder="Enter first name" name="f_name" required>
        <i class='bx bxs-user'></i>
      </div>
      
      <div class="input-box">
        <label>Last Name</label>
        <input type="text" id="l_name" placeholder="Enter last name" name="l_name" required>
        <i class='bx bxs-user'></i>
      </div>
      
      <div class="input-box">
        <label>Email</label>
        <input type="email" id="email" placeholder="Enter email address" name="email" required>
        <i class='bx bxs-envelope'></i>
      </div>
      
      <div class="input-box" id="passwordBox">
        <label>Password</label>
        <input type="password" id="password" placeholder="Enter password" name="password" required>
        <i class='bx bxs-lock-alt'></i>
      </div>
      
      <div class="input-box">
        <label>Role</label>
        <select id="role" name="role" required>
          <option value="">Select Role</option>
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>
        <i class='bx bxs-user-badge'></i>
      </div>
      
      <div class="input-box">
        <label>Status</label>
        <select id="status" name="status" required>
          <option value="">Select Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="suspended">Suspended</option>
        </select>
        <i class='bx bxs-info-circle'></i>
      </div>
      
      <button type="submit" class="btn-submit" name="submit">Save Member</button>
    </form>
  </div>
</div>

<!-- DELETE CONFIRMATION MODAL -->
<div id="deleteModal" class="modal">
  <div class="modal-content delete-modal">
    <h2>Confirm Delete</h2>
    <p>Are you sure you want to delete this member? This action cannot be undone.</p>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
      <button class="btn-delete-confirm" onclick="confirmDelete()">Delete</button>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-container">
    <div class="footer-grid">
      <div class="footer-column">
        <a href="/" class="footer-logo">OpenLib</a>
        <p class="footer-description">Your gateway to knowledge. Explore thousands of books and join our vibrant reading community.</p>
      </div>
      <div class="footer-column">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="../dashboard/dashboard.html">Dashboard</a></li>    
          <li><a href="../Home/Main.html">Home</a></li>
          <li><a href="#">Book Catalog</a></li>
          <li><a href="../contact us/contact.html">Contact</a></li>
          <li><a href="../Abou_us/about_us.html">About Us</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Account</h3>
        <ul>
          <li><a href="../Login/user_login.php">Log In</a></li>
          <li><a href="../Register/user_register.html">Sign Up</a></li>
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

<script src="manage_members.js"></script>
<script>
// Toggle mobile menu
function toggleMenu() {
  const navMenu = document.getElementById('navMenu');
  navMenu.classList.toggle('active');
}
</script>
</body>
</html>