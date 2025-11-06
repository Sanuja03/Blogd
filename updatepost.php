<?php
/**
 * Update Post Page
 * Allows authenticated users to edit and update existing blog posts
 */

session_start();
include "back.php";

// Validate post_id
if (!isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
    header("Location: web.php");
    exit();
}
$post_id = intval($_GET['post_id']);

// Check if user is logged in and has User role
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'User') {
    header("Location: web.php");
    exit();
}

$email = $_SESSION['email'];

// Step 1: Get user's ID securely
$user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$user_stmt->bind_param("s", $email);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
if ($user_result->num_rows === 0) {
    header("Location: web.php");
    exit();
}
$user_row = $user_result->fetch_assoc();
$writer_id = $user_row['id'];

// Step 2: Fetch existing post data
$post_stmt = $conn->prepare("SELECT * FROM posts WHERE post_id = ?");
$post_stmt->bind_param("i", $post_id);
$post_stmt->execute();
$post_result = $post_stmt->get_result();
if ($post_result->num_rows === 0) {
    $_SESSION['message'] = "Post not found.";
    $_SESSION['message_type'] = "error";
    header("Location: displaypost.php");
    exit();
}
$post_data = $post_result->fetch_assoc();

// Step 3: Fetch all categories
$categories_result = $conn->query("SELECT * FROM categories");
if (!$categories_result) {
    $error_message = "Error fetching categories: {$conn->error}";
}

// Step 4: Handle form submission
if (isset($_POST['submit'])) {

    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_name = $_POST['selcategory'];

    // Handle file upload
    $imgname = $_FILES['image']['name'] ?? '';
    $temp_location = $_FILES['image']['tmp_name'] ?? '';
    $upload_folder = "image/";

    if (!empty($imgname)) {
        move_uploaded_file($temp_location, $upload_folder . $imgname);
    } else {
        $imgname = $post_data['image'];
    }

    // Get category ID securely
    $cat_stmt = $conn->prepare("SELECT cat_id FROM categories WHERE cat_name = ?");
    $cat_stmt->bind_param("s", $category_name);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();
    if ($cat_result->num_rows > 0) {
        $cat_row = $cat_result->fetch_assoc();
        $category_id = $cat_row['cat_id'];
    } else {
        $_SESSION['message'] = "Selected category not found.";
        $_SESSION['message_type'] = "error";
        header("Location: updatepost.php?post_id=$post_id");
        exit();
    }

    // Update the post using prepared statement
    $update_stmt = $conn->prepare("UPDATE posts SET p_title = ?, content = ?, auth_id = ?, category_id = ?, image = ? WHERE post_id = ?");
    $update_stmt->bind_param("ssissi", $title, $content, $writer_id, $category_id, $imgname, $post_id);

    if ($update_stmt->execute()) {

        // Log activity
        $activity_stmt = $conn->prepare("INSERT INTO activities (user_id, post_id, action, description, created_at) VALUES (?, ?, ?, ?, NOW())");
        $action = "Update Post";
        $description = "You updated post ID: $post_id (Title: $title)";
        $activity_stmt->bind_param("iiss", $writer_id, $post_id, $action, $description);
        $activity_stmt->execute();

        $_SESSION['message'] = "Post updated successfully!";
        $_SESSION['message_type'] = "success";

    } else {
        $_SESSION['message'] = "Error updating post: {$conn->error}";
        $_SESSION['message_type'] = "error";
    }

    header("Location: updatepost.php?post_id=$post_id");
    exit();
}

// Function to display messages
function showMessage($message, $type) {
    if (!empty($message)) {
        $class = $type === 'success' ? 'update-message success' : 'update-message error';
        return "<div class='$class'><p>$message</p></div>";
    }
    return '';
}

// Get session message
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? 'error';
unset($_SESSION['message'], $_SESSION['message_type']);

$pageTitle = "Update Post - Blog Management System";
require_once 'header.php';
?>

<div class="update-post-container">
    <div class="update-postbox">
        <?= showMessage($message, $messageType); ?>

        <form action="updatepost.php?post_id=<?= $post_id; ?>" 
              method="post" 
              enctype="multipart/form-data" 
              class="update-post-form">
            <h2>Update Post</h2>

            <div class="input-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required
                       value="<?= htmlspecialchars($post_data['p_title']); ?>">
            </div>

            <div class="input-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" rows="10" required><?= htmlspecialchars($post_data['content']); ?></textarea>
            </div>

            <div class="input-group">
                <label for="selcategory">Select Category:</label>
                <select id="selcategory" name="selcategory" required>
                    <option value="" disabled>Choose a category</option>
                    <?php 
                    if (isset($error_message)) {
                        echo "<option disabled>Error loading categories</option>";
                    } else {
                        while ($row = mysqli_fetch_assoc($categories_result)) { 
                            $selected = ($row['cat_id'] == $post_data['category_id']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['cat_name']) . "' $selected>" . htmlspecialchars($row['cat_name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <?php if (!empty($post_data['image'])): ?>
                <div class="current-image-section">
                    <label>Current Image:</label>
                    <img src="image/<?= htmlspecialchars($post_data['image']); ?>" alt="Current post image">
                </div>
            <?php endif; ?>

            <div class="input-group">
                <label for="image">Upload New Image (optional):</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="form-actions">
                <button type="submit" name="submit" class="btn btn-update">Update Post</button>
                <a href="displaypost.php" class="btn btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
