<?php
session_start();
$pageTitle = "Privacy Policy - Blogd";
require_once 'header.php';
?>
<br>
<div class="back-to-dashboard">
    <a href="displaypost.php">
        &larr; Back to Dashboard
    </a>
</div>

<main class="privacy-container">
    <h1>Privacy Policy</h1>
    <p>Last updated: October 2025</p>

    <section class="privacy-section">
        <h2>1. Information We Collect</h2>
        <p>We collect information you provide directly to us, such as your name, email address, and any content you submit through our platform.</p>
    </section>

    <section class="privacy-section">
        <h2>2. How We Use Your Information</h2>
        <p>Your information helps us improve the site, provide personalized content, and ensure a safe experience for all users.</p>
    </section>

    <section class="privacy-section">
        <h2>3. Data Sharing</h2>
        <p>We do not sell or rent your personal data. We may share information with trusted service providers for site functionality.</p>
    </section>


    <section class="privacy-section">
        <h2>4. Security</h2>
        <p>We take reasonable measures to protect your data, but no method is completely secure.</p>
    </section>

    <section class="privacy-section">
        <h2>5. Contact Us</h2>
        <p>If you have questions about this Privacy Policy, please contact us at our <a href="mailto:nimsith@gmail.com">Email Address</a>.</p>
    </section>
</main>

<?php require_once 'footer.php'; ?>
