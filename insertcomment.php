<?php
session_start();
include "back.php";

// Check if user is logged in and has the correct role
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'User') {
    header("Location: web.php");
    exit();
}

// Validate post_id
if (!isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
    header("Location: displaypost.php?error=invalid_post");
    exit();
}

$post_id = intval($_GET['post_id']);

if (isset($_POST['submit'])) {

    $email = $_SESSION['email'];
    $user_comment = $_POST['comment'];

    // Get user's info securely
    $user_stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $user_stmt->bind_param("s", $email);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows === 0) {
        header("Location: web.php");
        exit();
    }

    $user_row = $user_result->fetch_assoc();
    $user_id = $user_row['id'];
    $writer_name = $user_row['name'];

    // Insert comment securely
    $comment_stmt = $conn->prepare("INSERT INTO comments (p_id, user_name, com_email, comment) VALUES (?, ?, ?, ?)");
    $comment_stmt->bind_param("isss", $post_id, $writer_name, $email, $user_comment);

    if ($comment_stmt->execute()) {

        // Log activity in the activities table
        $activity_stmt = $conn->prepare("INSERT INTO activities (user_id, post_id, action, description, created_at) VALUES (?, ?, ?, ?, NOW())");
        $action = "Comment";
        $description = "You commented on post ID: $post_id";
        $activity_stmt->bind_param("iiss", $user_id, $post_id, $action, $description);
        $activity_stmt->execute();

        $_SESSION['message'] = "Comment added successfully.";
        $_SESSION['message_type'] = "success";

        header("Location: displaypost.php?post_id=$post_id");
        exit();

    } else {
        $_SESSION['message'] = "Error adding comment: {$conn->error}";
        $_SESSION['message_type'] = "error";
        header("Location: displaypost.php?post_id=$post_id");
        exit();
    }
}
?>
