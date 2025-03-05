<?php
session_start();
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Employee Management System</title>
    
    <!-- Correct CSS Path -->
    <link rel="stylesheet" href="aboutus.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="navbar">
            <h1><i class="fas fa-briefcase"></i> Employee Management System</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="about-us.php" class="active">About Us</a>
               
                <a href="admin_login.php">Admin Login</a>
            </nav>
        </div>
    </header>

    <section class="about-us">
        <div class="container">
            <h2>About Us</h2>
            <p>Welcome to the <strong>Employee Management System</strong>, your all-in-one platform for managing employees efficiently and effectively.</p>
            
            <div class="content">
                <div class="section">
                    <h3>Our Mission</h3>
                    <p>To provide organizations with a seamless, user-friendly platform for managing employee data, tracking performance, and improving productivity.</p>
                </div>

                <div class="section">
                    <h3>Our Vision</h3>
                    <p>To become the leading employee management solution, empowering businesses to thrive with better workforce management.</p>
                </div>

                <div class="section">
                    <h3>Why Choose Us?</h3>
                    <ul>
                        <li><i class="fas fa-check"></i> User-Friendly Interface</li>
                        <li><i class="fas fa-check"></i> Secure and Reliable</li>
                       
                        <li><i class="fas fa-check"></i> Scalable Solution</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Employee Management System. All Rights Reserved.</p>
    </footer>
</body>
</html>
