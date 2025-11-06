<?php
session_start();
require_once "back.php";

// Check if the user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['role'])) {
    header("Location: web.php");
    exit();
}

$post_id = $_GET['post_id'] ?? null;
if (!$post_id || !is_numeric($post_id)) {
    header("Location: displaypost.php?error=invalid_post");
    exit();
}
$post_id = intval($post_id);

$user_email = $_SESSION['email'];
$user_role  = $_SESSION['role'];

// Step 0: Get the user's ID
$user_sql = "SELECT id FROM users WHERE email = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("s", $user_email);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    header("Location: web.php"); // user doesn't exist
    exit();
}

$user = $user_result->fetch_assoc();
$user_id = $user['id'];

// Step 1: Fetch the post and check permissions
$post_sql = "SELECT * FROM posts WHERE post_id = ?";
$post_stmt = $conn->prepare($post_sql);
$post_stmt->bind_param("i", $post_id);
$post_stmt->execute();
$post_result = $post_stmt->get_result();

if ($post_result->num_rows === 0) {
    header("Location: displaypost.php?error=post_not_found");
    exit();
}

$post = $post_result->fetch_assoc();
$post_author_id = $post['auth_id'];

// Allow delete if user is admin or the post author
if ($user_role !== 'Admin' && $user_id !== $post_author_id) {
    header("Location: displaypost.php?error=unauthorized");
    exit();
}

// Step 2: Log the activity BEFORE deleting the post
$action_type = "Delete Post";
$activity_message = ($user_role === 'Admin') 
    ? "Admin deleted post ID: $post_id"
    : "You deleted your post (Post ID: $post_id)";

$activity_sql = "INSERT INTO activities (user_id, post_id, action, description, created_at) VALUES (?, ?, ?, ?, NOW())";
$activity_stmt = $conn->prepare($activity_sql);
$activity_stmt->bind_param("iiss", $user_id, $post_id, $action_type, $activity_message);
$activity_stmt->execute();

// Step 3a: Delete comments linked to this post
$delete_comments_sql = "DELETE FROM comments WHERE p_id = ?";
$delete_comments_stmt = $conn->prepare($delete_comments_sql);
$delete_comments_stmt->bind_param("i", $post_id);
$delete_comments_stmt->execute();

// Step 3b: Delete the post
$delete_sql = "DELETE FROM posts WHERE post_id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $post_id);

if ($delete_stmt->execute()) {
    header("Location: displaypost.php?success=post_deleted");
    exit();
} else {
    header("Location: displaypost.php?error=delete_failed");
    exit();
}
?>
