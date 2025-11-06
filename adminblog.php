<?php
session_start();
require_once 'back.php';

// Check if admin
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: web.php");
    exit();
}

// Get admin ID
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
    
    // If $post_id is null, bind NULL; else bind actual value
    if ($post_id === null) {
        $stmt->bind_param("iiss", $user_id, $post_id, $action, $description); // post_id will be NULL
    } else {
        $stmt->bind_param("iiss", $user_id, $post_id, $action, $description);
    }
    
    $stmt->execute();
    $stmt->close();
}


// Handle post deletion
if (isset($_GET['delete_post'])) {
    $post_id = intval($_GET['delete_post']);
    $res = $conn->query("SELECT p_title, auth_id FROM posts WHERE post_id=$post_id");
    if ($res && $res->num_rows > 0) {
        $post = $res->fetch_assoc();
        $title = $post['p_title'];
        $author_id = $post['auth_id'];
        logActivity($conn, $admin_id, $post_id, 'admin_delete', "Admin deleted post '$title' by user ID $author_id");
        logActivity($conn, $author_id, $post_id, 'deleted_by_admin', "Your post '$title' was deleted by an Admin");
        $conn->query("DELETE FROM posts WHERE post_id=$post_id");
    }
    header("Location: adminblog.php");
    exit();
}

// Fetch all posts
$posts = $conn->query("SELECT post_id, p_title, auth_id, u.name AS author FROM posts p JOIN users u ON p.auth_id = u.id ORDER BY p.post_id DESC");

$pageTitle = "Manage Blog Posts - Admin";
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
    <div class="posts-box">
        <h2>All Blog Posts</h2>
        <table class="admin-posts-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $posts->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['post_id']; ?></td>
                        <td><?= htmlspecialchars($row['p_title']); ?></td>
                        <td><?= htmlspecialchars($row['author']); ?></td>
                        <td>
                            <a href="adminblog.php?delete_post=<?= $row['post_id']; ?>" onclick="return confirm('Are you sure you want to delete this post?');" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>
