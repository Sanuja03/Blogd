<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'User') {
    header("Location: web.php");
    exit();
}

$pageTitle = "User Dashboard - Blog Management System";
require_once 'header.php';
?>

<!-- Main Dashboard Container -->
<div class="dashboard-container">

    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h1>Welcome Back, <span class="user-name"><?= htmlspecialchars($_SESSION['name']); ?></span>!</h1>
            <p class="welcome-message">You're logged in as a <span class="user-role">User</span></p>
        </div>
    </div>

    <!-- Dashboard Actions -->
    <div class="dashboard-actions">
        <h2>Quick Actions</h2>
        <div class="action-grid">

            <!-- Create New Post -->
            <div class="action-card">
                <div class="action-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                </div>
                <h3>Create Post</h3>
                <p>Write and publish a new blog post</p>
                <button onclick="window.location.href='post.php'" class="btn btn-primary">Create New Post</button>
            </div>

            <!-- My Posts -->
            <div class="action-card">
                <div class="action-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <h3>My Posts</h3>
                <p>View, update or delete your posts</p>
                <button onclick="window.location.href='myposts.php'" class="btn btn-secondary">View My Posts</button>
            </div>

            <!-- View Activity -->
            <div class="action-card">
                <div class="action-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <h3>My Activity</h3>
                <p>View your recent post and comment activity</p>
                <button onclick="window.location.href='user_activity.php'" class="btn btn-secondary">View Activity</button>
            </div>

        </div>
    </div>

    <!-- Account Info -->
    <div class="user-info-section">
        <h2>Account Information</h2>
        <div class="info-card">
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value"><?= htmlspecialchars($_SESSION['email']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Role:</span>
                <span class="info-value badge-user"><?= htmlspecialchars($_SESSION['role']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Account Status:</span>
                <span class="info-value badge-active">Active</span>
            </div>
        </div>
    </div>


</div>

<?php require_once 'footer.php'; ?>
