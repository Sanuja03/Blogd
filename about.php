<?php
session_start();
$pageTitle = "About Us - Blogd";
require_once 'header.php';
?>

<div class="about-container">
    <div class="about-content">
        <h1>About <span class="brand-name">Blogd</span></h1>
        <p class="tagline">Where ideas find their voice and stories come to life.</p>
        
        <div class="about-sections">

            <div class="about-card">
                <h2>Our Mission</h2>
                <p>
                    At <strong>Blogd</strong>, our mission is to empower individuals to share their thoughts, ideas, and passions 
                    with the world through an intuitive and engaging blogging experience.
                </p>
            </div>

            <div class="about-card">
                <h2>What We Offer</h2>
                <p>
                    We provide a platform that balances creativity with simplicity — enabling users to create, manage, and explore blogs effortlessly. 
                    Whether you're an aspiring writer or a seasoned storyteller, Blogd helps your words shine.
                </p>
            </div>

            <div class="about-card">
                <h2>Our Vision</h2>
                <p>
                    We envision a global community of creators who inspire one another through authentic content — building meaningful connections, one post at a time.
                </p>
            </div>

            <div class="about-card">
                <h2>About Me – Sanuja Alphonsus</h2>
                <p>
                    Hi, I’m <strong>Sanuja Alphonsus</strong>, the founder of Blogd. With a background in web development and digital storytelling, I’ve built this platform to bring together writers, thinkers, and creators in one space.  
                    On a mission to make blogging accessible and enjoyable, I combine intuitive design, reliable functionality, and a passion for connecting voices.  
                    Outside of Blogd, I’m continually exploring new tech, refining my craft, and building projects that make an impact.  
                </p>
                <p>
                    Feel free to connect with me on <a href="https://www.linkedin.com/in/sanuja-alphonsus-a1a802135/" target="_blank" rel="noopener noreferrer">LinkedIn</a> to see my journey and get in touch.
                </p>
            </div>

        </div>

        <div class="about-footer">
            <p>Join the Blogd community today and start telling your story!</p>
            <a href="web.php" class="btn btn-primary">Get Started</a>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
