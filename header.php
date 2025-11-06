<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $pageTitle ?? 'Blogd'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="logo1.png">
    
    <link rel="stylesheet" href="web.css">
    <!-- Optionally use Google Fonts + icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="displaypost.php"><h1 class="logo">Blogd</h1></a>
            <nav>
                <ul class="nav-links">
                    <li><a href="displaypost.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <?php if (isset($_SESSION['email'])): ?>
                        <?php if ($_SESSION['role'] === 'Admin'): ?>
                            <li><a href="admin_dashboard.php">Admin Dashboard</a></li>
                        <?php elseif ($_SESSION['role'] === 'User'): ?>
                            <li><a href="user_dashboard.php">User Dashboard</a></li>
                        <?php endif; ?>
                        <li class="logout-btn">
                            <a href="logout.php" title="Logout">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li><a href="web.php">Login/Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>