<?php
session_start();
require_once 'back.php';

// Check if admin
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: web.php");
    exit();
}

// Get admin ID and name
$admin_email = $_SESSION['email'];
$result = $conn->query("SELECT id, name FROM users WHERE email='$admin_email' LIMIT 1");
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $admin_id = $admin['id'];
    $admin_name = $admin['name'];
} else {
    header("Location: logout.php");
    exit();
}

// Log function
function logActivity($conn, $user_id, $post_id, $action, $description) {
    $stmt = $conn->prepare("INSERT INTO activities (user_id, post_id, action, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $post_id, $action, $description);
    $stmt->execute();
    $stmt->close();
}

// Handle category addition
if (isset($_POST['add-category'])) {
    $categoryName = $_POST['category-name'];
    $checkcategory = $conn->query("SELECT * FROM categories WHERE cat_name='$categoryName'");

    if ($checkcategory->num_rows > 0) {
        $_SESSION['message'] = "Category already exists!";
        $_SESSION['message_type'] = "error";
    } else {
        $sql = "INSERT INTO categories (cat_name) VALUES ('$categoryName')";
        if ($conn->query($sql)) {
            $_SESSION['message'] = "Category added successfully!";
            $_SESSION['message_type'] = "success";
            logActivity($conn, $admin_id, null, 'add_category', "Admin added category '$categoryName'");
        } else {
            $_SESSION['message'] = "Error adding category: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
    }
    header("Location: addcat.php");
    exit();
}

// Display message
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? 'error';
unset($_SESSION['message'], $_SESSION['message_type']);

$pageTitle = "Add Category - Admin";
require_once 'header.php';
?>
<br>
<!-- Back to Admin Dashboard Link -->
<div class="back-to-dashboard">
    <a href="admin_dashboard.php">
        &larr; Back to Dashboard
    </a>
</div>


<div class="admin-container">
    <div class="category-box">
        <h2>Add New Category</h2>
        <?= !empty($message) ? "<div class='category-message {$messageType}'><p>$message</p></div>" : ""; ?>
        <form action="addcat.php" method="POST" class="category-form">
            <div class="input-group">
                <label for="category-name">Category Name</label>
                <input type="text" id="category-name" name="category-name" placeholder="Enter category name" required>
            </div>
            <button type="submit" name="add-category" class="btn btn-primary">Add Category</button>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
