<?php
/**
 * Create New Post Page
 * Allows authenticated users to create and submit new blog posts
 */

session_start();
require_once "back.php";

// Check if user is logged in and has User role
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'User') {
    header("Location: web.php");
    exit();
}

$email = $_SESSION['email'];

// Step 1: Get current user's ID securely
$user_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$user_stmt->bind_param("s", $email);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    header("Location: web.php");
    exit();
}

$writer_row = $user_result->fetch_assoc();
$writer_id = $writer_row['id'];

// Step 2: Fetch categories
$categories_result = $conn->query("SELECT * FROM categories");
if (!$categories_result) {
    $error_message = "Error fetching categories: {$conn->error}";
}

// Step 3: Handle form submission
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
        header("Location: post.php");
        exit();
    }

    // Insert new post using prepared statement
    $post_stmt = $conn->prepare("INSERT INTO posts (p_title, content, auth_id, category_id, image) VALUES (?, ?, ?, ?, ?)");
    $post_stmt->bind_param("ssiss", $title, $content, $writer_id, $category_id, $imgname);
    $post_result = $post_stmt->execute();

    if ($post_result) {
        $new_post_id = $conn->insert_id;

        // Log activity
        $activity_stmt = $conn->prepare("INSERT INTO activities (user_id, post_id, action, description, created_at) VALUES (?, ?, ?, ?, NOW())");
        $action = "Create Post";
        $description = "You created a new post (Post ID: $new_post_id, Title: $title)";
        $activity_stmt->bind_param("iiss", $writer_id, $new_post_id, $action, $description);
        $activity_stmt->execute();

        $_SESSION['message'] = "Post submitted successfully!";
        $_SESSION['message_type'] = "success";

    } else {
        $_SESSION['message'] = "Error submitting post: {$conn->error}";
        $_SESSION['message_type'] = "error";
    }

    // Redirect to prevent form resubmission
    header("Location: post.php");
    exit();
}

// Function to display messages
function showMessage($message, $type) {
    if (!empty($message)) {
        $class = $type === 'success' ? 'post-message success' : 'post-message error';
        return "<div class='$class'><p>$message</p></div>";
    }
    return '';
}

// Get session message
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? 'error';
unset($_SESSION['message'], $_SESSION['message_type']);

$pageTitle = "Create New Post - Blog Management System";
require_once 'header.php';
?>

<div class="post-container">
    <!-- Back to Admin Dashboard Link -->
<div class="back-to-dashboard">
    <a href="user_dashboard.php">
    &larr; Back to Dashboard
    </a>
</div>
    <div class="postbox">
        <?= showMessage($message, $messageType); ?>

        <form action="post.php" method="post" enctype="multipart/form-data" class="post-form">
            <h2>Create a New Post</h2>

            <div class="input-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" placeholder="Enter the post title" required>
            </div>

            <div class="input-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" rows="10" placeholder="Write your post content here..." required></textarea>
            </div>

            <div class="input-group">
                <label for="selcategory">Select Category:</label>
                <select id="selcategory" name="selcategory" required>
                    <option value="" disabled selected>Choose a category</option>
                    <?php 
                    if (isset($error_message)) {
                        echo "<option disabled>Error loading categories</option>";
                    } else {
                        while ($row = mysqli_fetch_assoc($categories_result)) { 
                            echo "<option value='" . htmlspecialchars($row['cat_name']) . "'>" . htmlspecialchars($row['cat_name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="input-group">
                <label for="image">Upload Image:</label>
                <div class="file-input-wrapper">
                    <input type="file" id="image" name="image" accept="image/*">
                    <span class="file-input-label">Choose an image file</span>
                </div>
            </div>

            <button type="submit" name="submit" class="btn btn-submit">Submit Post</button>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
