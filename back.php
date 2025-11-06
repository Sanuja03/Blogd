<?php
// Function to load .env file into $_ENV
function loadEnv($file) {
    if (!file_exists($file)) return;
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // skip comments
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        $_ENV[$name] = $value;
    }
}

// Load blog.env from the same folder
loadEnv(__DIR__ . '/blog.env');

// Use the environment variables
$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$database = $_ENV['DB_NAME'];

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ===================================
// SESSION TIMEOUT MANAGEMENT
// ===================================

// Only handle session if it's already started by the calling script
if (session_status() === PHP_SESSION_ACTIVE) {
    
    // Session timeout: 30 minutes (1800 seconds)
    // You can change this value as needed
    define('SESSION_TIMEOUT', 600);
    
    // Only check timeout for logged-in users
    if (isset($_SESSION['email'])) {
        
        // Check if LAST_ACTIVITY timestamp exists
        if (isset($_SESSION['LAST_ACTIVITY'])) {
            
            // Calculate elapsed time since last activity
            $elapsed_time = time() - $_SESSION['LAST_ACTIVITY'];
            
            // If session has expired
            if ($elapsed_time > SESSION_TIMEOUT) {
                
                // Clear all session data
                session_unset();
                session_destroy();
                
                // Start new session for timeout message
                session_start();
                $_SESSION['timeout_message'] = "Your session has expired due to inactivity. Please login again.";
                
                // Get current page to avoid redirect loop
                $current_page = basename($_SERVER['PHP_SELF']);
                
                // Only redirect if not already on login page
                if ($current_page !== 'web.php' && $current_page !== 'login_register.php') {
                    header("Location: web.php?timeout=1");
                    exit();
                }
            }
        }
        
        // Update last activity timestamp
        $_SESSION['LAST_ACTIVITY'] = time();
    }
}
?>