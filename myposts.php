<?php
session_start();
require_once 'back.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'User') {
    header("Location: web.php");
    exit();
}

$email = $conn->real_escape_string($_SESSION['email']);
$user = $conn->query("SELECT id FROM users WHERE email = '$email'")->fetch_assoc();
$user_id = $user['id'];

$pageTitle = "My Posts - Blog Management System";
require_once 'header.php';

// Fetch only the user's posts
$sql = "SELECT posts.*, users.name AS author_name 
        FROM posts 
        JOIN users ON posts.auth_id = users.id
        WHERE posts.auth_id = '$user_id'
        ORDER BY posts.post_id DESC";
$result = $conn->query($sql);
?>

<div class="posts-container">
    
    <div class="back-to-dashboard">
    <a href="user_dashboard.php">
    &larr; Back to Dashboard
    </a>
    </div>
    <h1>My Posts</h1>
    <?php if ($result->num_rows > 0): ?>
        <div class="posts-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <article class="post-preview-card">
                    <div class="post-preview-image">
                        <img src="image/<?= htmlspecialchars($row['image'] ?: 'default-post.jpg'); ?>" 
                             alt="<?= htmlspecialchars($row['p_title']); ?>">
                    </div>

                    <div class="post-preview-content">
                        <h2><?= htmlspecialchars($row['p_title']); ?></h2>
                        <p><?= htmlspecialchars(substr($row['content'], 0, 200)); ?>...</p>

                        <div class="post-preview-meta">
                            Posted on <?= htmlspecialchars($row['date'] ?? 'Unknown'); ?>
                        </div>

                        <div class="post-actions">
                            <a href="updatepost.php?post_id=<?= $row['post_id']; ?>" class="btn btn-edit">Update</a>
                            <a href="deletepost.php?post_id=<?= $row['post_id']; ?>" 
                               class="btn btn-delete"
                               onclick="return confirm('Are you sure you want to delete this post?');">
                               Delete
                            </a>
                        </div>

                        <!-- Comments section -->
                        <div class="comments-section">
                            <h3>Comments</h3>
                            <?php
                            $pid = $row['post_id'];
                            $comments = $conn->query("SELECT * FROM comments WHERE p_id='$pid' ORDER BY com_id DESC");
                            if ($comments->num_rows > 0):
                                while ($c = $comments->fetch_assoc()):
                            ?>
                                <div class="comment-box">
                                    <h4><?= htmlspecialchars($c['user_name']); ?></h4>
                                    <p><?= nl2br(htmlspecialchars($c['comment'])); ?></p>
                                </div>
                            <?php endwhile; else: ?>
                                <p class="no-comments">No comments yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-posts">
            <h2>You haven’t posted anything yet.</h2>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
