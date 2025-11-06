<?php
/**
 * Login and Registration Page
 * Handles user authentication and new user registration
 * Displays error and success messages from session
 */

// Start session to access session variables
session_start();

// Retrieve error messages from session
$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['message'] ?? ''
];

// Retrieve success messages from session
$successmessages = [
    'login' => $_SESSION['successmessage'] ?? '',
    'register' => $_SESSION['login_success'] ?? ''
];

// Determine which form should be displayed (login or register)
$activeForm = $_SESSION['active_form'] ?? 'login';

// Clear only the message-related session data, NOT the active_form
unset($_SESSION['login_error']);
unset($_SESSION['message']);
unset($_SESSION['successmessage']);
unset($_SESSION['login_success']);
unset($_SESSION['active_form']);

/**
 * Display error message with styling
 * @param string $error - The error message to display
 * @return string - HTML markup for error message or empty string
 */
function showError($error) {
    return !empty($error) ? "<div class='error-message'><p>$error</p></div>" : '';
}

/**
 * Display success message with styling
 * @param string $successmessage - The success message to display
 * @return string - HTML markup for success message or empty string
 */
function showSuccess($successmessage) {
    return !empty($successmessage) ? "<div class='success-message'><p>$successmessage</p></div>" : '';
}

/**
 * Determine if a form should be active/visible
 * @param string $formName - Name of the form to check
 * @param string $activeForm - Currently active form name
 * @return string - 'active' class or empty string
 */
function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}
require_once 'header.php';
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login & Register - Blog Management System</title>
        <link rel="stylesheet" href="web.css">
    </head>
    
    <body class="auth-page">
        <!-- Main Container -->
        <div class="auth-wrapper">
            
            <!-- Brand Header -->
            <div class="brand-header">
                <br><br>
                <h1>Blog Management System</h1>
                <p>Your platform for creating and sharing amazing content</p>
            </div>

            <!-- Forms Container -->
            <div class="container">
                
                <!-- Login Form -->
                <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-box">
                    <h2>Welcome Back</h2>
                    <p class="form-subtitle">Login to your account</p>
                    
                    <form action="login_register.php" method="post" class="auth-form">
                        <!-- Display error/success messages -->
                        <?= showError($errors['login']); ?>
                        <?= showSuccess($successmessages['login']); ?>
                        
                        <!-- Email Input -->
                        <div class="input-group">
                            <label for="login-email">Email</label>
                            <input type="email" 
                                   id="login-email" 
                                   name="email" 
                                   placeholder="Enter your email" 
                                   required>
                        </div>
                        
                        <!-- Password Input -->
                        <div class="input-group">
                            <label for="login-password">Password</label>
                            <input type="password" 
                                   id="login-password" 
                                   name="password" 
                                   placeholder="Enter your password" 
                                   required>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" name="login" class="btn btn-primary">
                            Login
                        </button>
                        
                        <!-- Toggle to Register Form -->
                        <p class="form-toggle">
                            Don't have an account? 
                            <a href="#" onclick="showForm('register-box'); return false;">Register here</a>
                        </p>
                    </form>
                </div>

                <!-- Registration Form -->
                <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-box">
                    <h2>Create Account</h2>
                    <p class="form-subtitle">Join our blogging community</p>
                    
                    <form action="login_register.php" method="post" class="auth-form">
                        <!-- Display error/success messages -->
                        <?= showError($errors['register']); ?>
                        <?= showSuccess($successmessages['register']); ?>
                        
                        <!-- Username Input -->
                        <div class="input-group">
                            <label for="username">Username</label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   placeholder="Choose a username" 
                                   required>
                        </div>
                        
                        <!-- Email Input -->
                        <div class="input-group">
                            <label for="register-email">Email</label>
                            <input type="email" 
                                   id="register-email" 
                                   name="email" 
                                   placeholder="Enter your email" 
                                   required>
                        </div>
                        
                        <!-- Role Selection -->
                        <div class="input-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="" disabled selected>Select your role</option>
                                <option value="User">User</option>
                            </select>
                        </div>
                        
                        <!-- Password Input -->
                        <div class="input-group">
                            <label for="register-password">Password</label>
                            <input type="password" 
                                   id="register-password" 
                                   name="password" 
                                   placeholder="Create a password" 
                                   required>
                        </div>
                        
                        <!-- Confirm Password Input -->
                        <div class="input-group">
                            <label for="confirm-password">Confirm Password</label>
                            <input type="password" 
                                   id="confirm-password" 
                                   name="confirm-password" 
                                   placeholder="Re-enter your password" 
                                   required>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" name="register" class="btn btn-primary">
                            Register
                        </button>
                        
                        <!-- Toggle to Login Form -->
                        <p class="form-toggle">
                            Already have an account? 
                            <a href="#" onclick="showForm('login-box'); return false;">Login here</a>
                        </p>
                    </form>
                </div>
                
            </div>
            
        </div>
        
        <!-- JavaScript for form switching -->
        <script src="front.js"></script>
        <script>
// Password validation for registration form
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.querySelector('#register-box form');
    
    if (registerForm) {
        const password = document.getElementById('register-password');
        const confirmPassword = document.getElementById('confirm-password');
        
        // Create password requirements display
        const requirementsDiv = document.createElement('div');
        requirementsDiv.className = 'password-requirements';
        requirementsDiv.innerHTML = `
            <p class="req-title">Password must contain:</p>
            <ul>
                <li id="req-length"><i class="fa-solid fa-circle-xmark"></i> At least 8 characters</li>
                <li id="req-uppercase"><i class="fa-solid fa-circle-xmark"></i> One uppercase letter</li>
                <li id="req-lowercase"><i class="fa-solid fa-circle-xmark"></i> One lowercase letter</li>
                <li id="req-number"><i class="fa-solid fa-circle-xmark"></i> One number</li>
                <li id="req-special"><i class="fa-solid fa-circle-xmark"></i> One special character (!@#$%^&*)</li>
            </ul>
        `;
        password.parentElement.appendChild(requirementsDiv);
        
        // Real-time password validation
        password.addEventListener('input', function() {
            const value = this.value;
            
            // Check length
            validateRequirement('req-length', value.length >= 8);
            
            // Check uppercase
            validateRequirement('req-uppercase', /[A-Z]/.test(value));
            
            // Check lowercase
            validateRequirement('req-lowercase', /[a-z]/.test(value));
            
            // Check number
            validateRequirement('req-number', /[0-9]/.test(value));
            
            // Check special character
            validateRequirement('req-special', /[!@#$%^&*(),.?":{}|<>]/.test(value));
        });
        
        function validateRequirement(id, isValid) {
            const element = document.getElementById(id);
            const icon = element.querySelector('i');
            
            if (isValid) {
                element.classList.add('valid');
                element.classList.remove('invalid');
                icon.className = 'fa-solid fa-circle-check';
            } else {
                element.classList.add('invalid');
                element.classList.remove('valid');
                icon.className = 'fa-solid fa-circle-xmark';
            }
        }
        
        // Form submission validation
        registerForm.addEventListener('submit', function(e) {
            const passwordValue = password.value;
            const confirmValue = confirmPassword.value;
            
            // Check if passwords match
            if (passwordValue !== confirmValue) {
                e.preventDefault();
                alert('Passwords do not match!');
                confirmPassword.focus();
                return false;
            }
            
            // Check password strength
            if (passwordValue.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long!');
                password.focus();
                return false;
            }
            
            if (!/[A-Z]/.test(passwordValue)) {
                e.preventDefault();
                alert('Password must contain at least one uppercase letter!');
                password.focus();
                return false;
            }
            
            if (!/[a-z]/.test(passwordValue)) {
                e.preventDefault();
                alert('Password must contain at least one lowercase letter!');
                password.focus();
                return false;
            }
            
            if (!/[0-9]/.test(passwordValue)) {
                e.preventDefault();
                alert('Password must contain at least one number!');
                password.focus();
                return false;
            }
            
            if (!/[!@#$%^&*(),.?":{}|<>]/.test(passwordValue)) {
                e.preventDefault();
                alert('Password must contain at least one special character!');
                password.focus();
                return false;
            }
        });
        
        // Confirm password matching validation
        confirmPassword.addEventListener('input', function() {
            if (this.value !== password.value) {
                this.setCustomValidity('Passwords do not match');
                this.style.borderColor = 'var(--error)';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = 'var(--success)';
            }
        });
    }
});
</script>
    </body>
    <?php
    // Include footer
    require_once 'footer.php';
    ?>
</html>