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

// ===================================
// PAGINATION SETUP
// ===================================

// Number of records per page
$records_per_page = 15;

// Get current page number from URL, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Ensure page is at least 1

// Calculate offset for SQL query
$offset = ($current_page - 1) * $records_per_page;

// Get total number of activities
$total_result = $conn->query("SELECT COUNT(*) as total FROM activities");
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];

// Calculate total pages
$total_pages = ceil($total_records / $records_per_page);

// Fetch activities for current page
$activities = $conn->query("SELECT a.*, u.name 
                            FROM activities a 
                            JOIN users u ON a.user_id = u.id 
                            ORDER BY a.created_at DESC 
                            LIMIT $records_per_page OFFSET $offset");

$pageTitle = "Activity Log - Admin";
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
    <div class="activity-box">
        <div class="activity-header">
            <h2>Recent Activities</h2>
            <p class="activity-count">
                Showing <?= min($offset + 1, $total_records); ?> - 
                <?= min($offset + $records_per_page, $total_records); ?> 
                of <?= $total_records; ?> activities
            </p>
        </div>

        <?php if ($activities->num_rows > 0): ?>
            <ul class="activity-list">
                <?php while ($a = $activities->fetch_assoc()): ?>
                    <li class="activity-item">
                        <div class="activity-content">
                            <strong><?= htmlspecialchars($a['name']); ?></strong> 
                            <?= htmlspecialchars($a['description']); ?>
                        </div>
                        <em class="activity-time"><?= date('M d, Y - h:i A', strtotime($a['created_at'])); ?></em>
                    </li>
                <?php endwhile; ?>
            </ul>

            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    
                    <!-- Previous Button -->
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?= $current_page - 1; ?>" class="pagination-btn prev-btn">
                            <i class="fa-solid fa-chevron-left"></i> Previous
                        </a>
                    <?php else: ?>
                        <span class="pagination-btn prev-btn disabled">
                            <i class="fa-solid fa-chevron-left"></i> Previous
                        </span>
                    <?php endif; ?>

                    <!-- Page Numbers -->
                    <div class="pagination-numbers">
                        <?php
                        // Show first page
                        if ($current_page > 3) {
                            echo '<a href="?page=1" class="pagination-number">1</a>';
                            if ($current_page > 4) {
                                echo '<span class="pagination-dots">...</span>';
                            }
                        }

                        // Show pages around current page
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);

                        for ($i = $start_page; $i <= $end_page; $i++) {
                            if ($i == $current_page) {
                                echo '<span class="pagination-number active">' . $i . '</span>';
                            } else {
                                echo '<a href="?page=' . $i . '" class="pagination-number">' . $i . '</a>';
                            }
                        }

                        // Show last page
                        if ($current_page < $total_pages - 2) {
                            if ($current_page < $total_pages - 3) {
                                echo '<span class="pagination-dots">...</span>';
                            }
                            echo '<a href="?page=' . $total_pages . '" class="pagination-number">' . $total_pages . '</a>';
                        }
                        ?>
                    </div>

                    <!-- Next Button -->
                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?= $current_page + 1; ?>" class="pagination-btn next-btn">
                            Next <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="pagination-btn next-btn disabled">
                            Next <i class="fa-solid fa-chevron-right"></i>
                        </span>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

        <?php else: ?>
            <p class="no-activities">No activities recorded yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>