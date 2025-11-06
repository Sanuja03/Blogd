<?php
/**
 * Posts Display Page
 * Shows all blog posts with preview/full view functionality
 */

session_start();
include "back.php";

// Function to calculate "time ago"
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 31536000) {
        $months = floor($diff / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($diff / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}

// Check login state
$is_logged_in = isset($_SESSION['email']);
$current_user_id = null;
$user_role = null;

if ($is_logged_in) {
    $email = $conn->real_escape_string($_SESSION['email']);
    
    // Fetch user ID and role
    $writer_result = $conn->query("SELECT id, role FROM users WHERE email = '$email'");
    if ($writer_result && $writer_result->num_rows > 0) {
        $writer_row = $writer_result->fetch_assoc();
        $current_user_id = $writer_row['id'];
        $user_role = strtolower($writer_row['role']); // e.g., 'admin', 'user'
    }
}

// Check if viewing a specific post
$view_post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : null;

// Fetch all posts with author names
$sql = "SELECT posts.*, users.name as author_name 
        FROM posts 
        LEFT JOIN users ON posts.auth_id = users.id 
        ORDER BY posts.post_id DESC";
$result = mysqli_query($conn, $sql);

$pageTitle = "All Posts - Blog Management System";
require_once 'header.php';
?>

<div class="posts-container">

<?php if ($view_post_id && mysqli_num_rows($result) > 0): ?>
    <!-- FULL POST VIEW -->
    <?php
    $post_found = false;
    mysqli_data_seek($result, 0);

    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['post_id'] == $view_post_id) {
            $post_found = true;

            // Require login to view
            if (!$is_logged_in) {
                ?>
                <div class="login-required">
                    <h2>Login Required</h2>
                    <p>Please <a href="web.php">login</a> to view the full post.</p>
                </div>
                <?php
                break;
            }
            
            // Format the date and time
            $post_date = $row['date'] ?? $row['created_at'] ?? date('Y-m-d H:i:s');
            $formatted_date = date('F j, Y', strtotime($post_date));
            $formatted_time = date('g:i A', strtotime($post_date));
            $time_ago = timeAgo($post_date);
            ?>
            
            <a href="displaypost.php" class="back-btn">← Back to All Posts</a>
            
            <article class="post-full-view">
                <div class="post-full-header">
                    <h1 class="post-full-title"><?= htmlspecialchars($row['p_title']); ?></h1>
                    <div class="post-full-meta">
                        <div class="post-meta-item">
                            <i class="fa-solid fa-user"></i>
                            <span>Posted by <strong><?= htmlspecialchars($row['author_name'] ?? 'Unknown'); ?></strong></span>
                        </div>
                        <div class="post-meta-item">
                            <i class="fa-solid fa-calendar"></i>
                            <span><?= $formatted_date; ?> at <?= $formatted_time; ?></span>
                        </div>
                        <div class="post-meta-item time-ago">
                            <i class="fa-solid fa-clock"></i>
                            <span><?= $time_ago; ?></span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($row['image'])): ?>
                    <div class="post-full-image">
                        <img src="image/<?= htmlspecialchars($row['image']); ?>" alt="Post Image">
                    </div>
                <?php endif; ?>

                <div class="post-full-content">
                    <?= nl2br(htmlspecialchars($row['content'])); ?>
                </div>

                <!-- ✅ Post Actions -->
                <?php if ($is_logged_in): ?>
                    <?php if ($user_role === 'admin' || $current_user_id == $row['auth_id']): ?>
                        <div class="post-actions">
                            <?php if ($current_user_id == $row['auth_id']): ?>
                                <!-- Author can edit -->
                                <a href="updatepost.php?post_id=<?= $row['post_id']; ?>" class="btn btn-edit">
                                    <i class="fa-solid fa-pen-to-square"></i> Update Post
                                </a>
                            <?php endif; ?>

                            <!-- Admin or author can delete -->
                            <a href="deletepost.php?post_id=<?= $row['post_id']; ?>" 
                               class="btn btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this post?');">
                                <i class="fa-solid fa-trash"></i> Delete Post
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Add Comment Form -->
                <?php if ($user_role === 'user'): ?>
                <div class="add-comment-box">
                    <form action="insertcomment.php?post_id=<?= $row['post_id']; ?>" 
                          method="post" 
                          class="comment-form">
                        <h2>Add a Comment</h2>
                        <label for="comment">Share your thoughts:</label>
                        <textarea id="comment" 
                                  name="comment" 
                                  rows="4" 
                                  placeholder="Write your comment here..." 
                                  required></textarea>
                        <input type="submit" value="Submit Comment" name="submit" class="btn btn-primary">
                    </form>
                </div>
                <?php endif; ?>

                <!-- Display Comments -->
                <div class="comments-section">
                    <h3>Comments</h3>
                    <?php
                    $post_id = $row['post_id'];
                    $sql2 = "SELECT * FROM comments WHERE p_id = '$post_id' ORDER BY com_id DESC";
                    $result2 = mysqli_query($conn, $sql2);

                    if (mysqli_num_rows($result2) > 0):
                        while ($row2 = mysqli_fetch_assoc($result2)):
                            // Format comment time
                            $comment_date = $row2['created_at'] ?? date('Y-m-d H:i:s');
                            $comment_time_ago = timeAgo($comment_date);
                    ?>
                        <div class="comment-box">
                            <div class="comment-header">
                                <h3><?= htmlspecialchars($row2['user_name']); ?></h3>
                                <span class="comment-time">
                                    <i class="fa-solid fa-clock"></i>
                                    <?= $comment_time_ago; ?>
                                </span>
                            </div>
                            <p><?= nl2br(htmlspecialchars($row2['comment'])); ?></p>
                        </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                        <p class="no-comments">No comments yet. Be the first to comment!</p>
                    <?php endif; ?>
                </div>
            </article>
            <?php
            break;
        }
    }

    if (!$post_found) {
        echo '<div class="no-posts"><h2>Post not found</h2></div>';
    }
    ?>

<?php else: ?>
    <!-- PREVIEW MODE - Show all posts -->
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="posts-grid">
            <?php 
            mysqli_data_seek($result, 0);
            while ($row = mysqli_fetch_assoc($result)): 
                $excerpt = substr($row['content'], 0, 150);
                if (strlen($row['content']) > 150) {
                    $excerpt .= '...';
                }
                $post_link = $is_logged_in 
                    ? "displaypost.php?post_id=" . $row['post_id']
                    : "web.php";
                
                // Format date for preview cards
                $post_date = $row['date'] ?? $row['created_at'] ?? date('Y-m-d H:i:s');
                $time_ago = timeAgo($post_date);
                $formatted_date = date('M j, Y', strtotime($post_date));
            ?>
            <article class="post-preview-card" onclick="window.location.href='<?= $post_link; ?>'">
                <div class="post-preview-image">
                    <?php if (!empty($row['image'])): ?>
                        <img src="image/<?= htmlspecialchars($row['image']); ?>" 
                             alt="<?= htmlspecialchars($row['p_title']); ?>">
                    <?php else: ?>
                        <img src="image/default-post.jpg" alt="Default post image">
                    <?php endif; ?>
                </div>

                <div class="post-preview-content">
                    <h2 class="post-preview-title"><?= htmlspecialchars($row['p_title']); ?></h2>
                    <p class="post-preview-excerpt"><?= htmlspecialchars($excerpt); ?></p>
                    
                    <div class="post-preview-meta">
                        <div class="meta-author">
                            <i class="fa-solid fa-user"></i>
                            <span>By <?= htmlspecialchars($row['author_name'] ?? 'Unknown'); ?></span>
                        </div>
                        <div class="meta-time">
                            <i class="fa-solid fa-clock"></i>
                            <span><?= $time_ago; ?></span>
                        </div>
                    </div>
                    
                    <a href="<?= $post_link; ?>" class="read-more-btn" onclick="event.stopPropagation();">
                        <?= $is_logged_in ? 'Read More' : 'Login to Read'; ?>
                    </a>
                </div>
            </article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-posts">
            <h2>No posts available</h2>
            <p>Check back later for new content!</p>
        </div>
    <?php endif; ?>
<?php endif; ?>

</div>

<?php require_once 'footer.php'; ?>