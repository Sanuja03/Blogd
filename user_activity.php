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

$pageTitle = "My Activity - Blog Management System";
require_once 'header.php';

// Fetch user activities
$sql = "SELECT * FROM activities WHERE user_id = '$user_id' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<div class="activity-container">
    <div class="back-to-dashboard">
    <a href="user_dashboard.php">
    &larr; Back to Dashboard
    </a>
</div>
    <h1>My Activity</h1>

    <?php if ($result->num_rows > 0): ?>
        <ul class="activity-list">
            <?php while ($row = $result->fetch_assoc()): ?>
                <li class="activity-item">
                    <span class="activity-action"><?= htmlspecialchars($row['action']); ?></span>
                    <p class="activity-details"><?= htmlspecialchars($row['description']); ?></p>
                    <span class="activity-time"><?= htmlspecialchars($row['created_at']); ?></span>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <div class="no-activity">
            <p>No recent activity to show.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
