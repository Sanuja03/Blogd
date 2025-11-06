<?php
// Start session and include database connection
session_start();
require_once 'back.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: web.php");
    exit();
}

// Get admin ID from email
$admin_email = $_SESSION['email'];
$result = $conn->query("SELECT id, name FROM users WHERE email='$admin_email' LIMIT 1");
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    $admin_id = $admin['id'];
    $admin_name = $admin['name'];
} else {
    // Fallback: logout if email not found
    header("Location: logout.php");
    exit();
}

// --------------------
// Utility: Log Activity
// --------------------
function logActivity($conn, $user_id, $post_id, $action, $description) {
    // Ensure post_id is null-safe for FK constraint
    $stmt = $conn->prepare("INSERT INTO activities (user_id, post_id, action, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $post_id, $action, $description);
    $stmt->execute();
    $stmt->close();
}

// --------------------
// Handle Category Addition
// --------------------
if (isset($_POST['add-category'])) {
    $categoryName = $_POST['category-name'];
    $checkcategory = $conn->query("SELECT * FROM categories WHERE cat_name='$categoryName'");

    if ($checkcategory->num_rows > 0) {
        $_SESSION['message'] = "Category already exists!";
        $_SESSION['message_type'] = "error";
    } else {
        $sql = "INSERT INTO categories (cat_name) VALUES ('$categoryName')";
        $result = mysqli_query($conn, $sql);

        if ($result) {
            $_SESSION['message'] = "Category added successfully!";
            $_SESSION['message_type'] = "success";

            // Log category addition
            logActivity($conn, $admin_id, null, 'add_category', "Admin added category '$categoryName'");
        } else {
            $_SESSION['message'] = "Error adding category: " . mysqli_error($conn);
            $_SESSION['message_type'] = "error";
        }
    }

    header("Location: admin_dashboard.php");
    exit();
}

// --------------------
// Handle Post Deletion by Admin
// --------------------
if (isset($_GET['delete_post'])) {
    $post_id = intval($_GET['delete_post']);

    // Get post details for logging
    $result = $conn->query("SELECT p_title, auth_id FROM posts WHERE post_id=$post_id");
    if ($result && $result->num_rows > 0) {
        $post = $result->fetch_assoc();
        $title = $post['p_title'];
        $author_id = $post['auth_id'];

        // Delete post
        $conn->query("DELETE FROM posts WHERE post_id=$post_id");

        // Log deletion activity for admin and the post owner
        logActivity($conn, $admin_id, $post_id, 'admin_delete', "Admin deleted post '$title' by user ID $author_id");
        logActivity($conn, $author_id, $post_id, 'deleted_by_admin', "Your post '$title' was deleted by an Admin");
    }
    header("Location: admin_dashboard.php");
    exit();
}

// --------------------
// Display Message Function
// --------------------
function showMessage($message, $type) {
    if (!empty($message)) {
        $class = $type === 'success' ? 'category-message success' : 'category-message error';
        return "<div class='$class'><p>$message</p></div>";
    }
    return '';
}

// Retrieve session message
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? 'error';
unset($_SESSION['message'], $_SESSION['message_type']);

// --------------------
// Fetch All Posts for Admin Management
// --------------------
$posts = $conn->query("SELECT post_id, p_title, auth_id, u.name AS author FROM posts p 
                       JOIN users u ON p.auth_id = u.id ORDER BY p.post_id DESC");

// --------------------
// Fetch All Activities
// --------------------
$activities = $conn->query("SELECT a.*, u.name FROM activities a 
                            JOIN users u ON a.user_id = u.id 
                            ORDER BY a.created_at DESC");

// Page setup
$pageTitle = "Admin Dashboard - Blog Management System";
require_once 'header.php';
?>

<!-- Main Content -->
<div class="admin-container">

    <!-- Welcome Section -->
    <div class="welcome-box">
        <h1>Welcome, <span class="username"><?= htmlspecialchars($admin_name); ?></span></h1>
        <p>This is the Admin Dashboard - a protected area accessible only to administrators. <br>Here you can manage all posts, categories, and user activities efficiently. <br>Keep your blog organized and ensure a smooth experience for all users.</p>
</p>
    </div>

    <!-- Admin Action Cards -->
    <div class="admin-actions-cards">
        <div class="action-card" onclick="window.location.href='addcat.php'">
            <h3>Add Category</h3>
            <p>Create new blog categories for authors to select.</p>
        </div>
        <div class="action-card" onclick="window.location.href='adminblog.php'">
            <h3>Manage Blog Posts</h3>
            <p>View, edit, or delete all blog posts on the platform.</p>
        </div>
        <div class="action-card" onclick="window.location.href='admin_activity.php'">
            <h3>Activity Log</h3>
            <p>Track all admin and user actions in the system.</p>
        </div>
    </div>


</div>

<?php require_once 'footer.php'; ?>
