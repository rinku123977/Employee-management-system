<?php
session_start();
require_once 'config.php'; // Ensure this file contains database connection settings

// Initialize variables
$error = '';
$step = 1; // Default step: Email input

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Step 1: Check Email in Database
    if (isset($_POST['email_submit'])) {  
        $email = trim($_POST['email']);

        if (empty($email)) {
            $error = "Email is required.";
        } else {
            // Check if email exists in the database
            $sql = "SELECT id, password FROM employees WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $_SESSION['email'] = $email;
                $_SESSION['user_id'] = $user['id']; // Store user ID in session

                if (empty($user['password'])) {
                    $step = 2; // Ask user to set a new password
                } else {
                    $step = 3; // Ask for existing password (Login)
                }
            } else {
                $error = "You are not registered.";
            }

            $stmt->close();
        }
    }

    // Step 2: Set New Password (If Password is Empty)
    if (isset($_POST['set_password'])) {  
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($password) || empty($confirm_password)) {
            $error = "Both password fields are required.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Hash and store the new password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $email = $_SESSION['email'];

            $update_sql = "UPDATE employees SET password = ? WHERE email = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ss", $hashed_password, $email);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Password set successfully. Please log in.";
                $step = 3; // Move to login step
            } else {
                $error = "Error updating password. Try again.";
            }

            $stmt->close();
        }
    }

    // Step 3: Login
    if (isset($_POST['login'])) {  
        $password = $_POST['password'];
        $email = $_SESSION['email'];

        $sql = "SELECT id, password FROM employees WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id']; // Ensure user ID is stored in session
            header("Location: profile.php"); // Redirect to dashboard
            exit();
        } else {
            $error = "Invalid password.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            padding: 50px;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h1 { text-align: center; margin-bottom: 20px; color: #333; }
        form { display: flex; flex-direction: column; }
        input[type="email"],
        input[type="password"] {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            padding: 10px;
            background-color: #1a73e8;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover { background-color: #1557b0; }
        .error { color: red; text-align: center; margin-top: 10px; }
        .success { color: green; text-align: center; margin-top: 10px; }
        p { text-align: center; }
        a { color: #1a73e8; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>

        <!-- Step 1: Email Input -->
        <?php if ($step == 1): ?>
            <form method="POST" action="">
                <input type="email" name="email" placeholder="Enter your email" required>
                <button type="submit" name="email_submit">Next</button>
            </form>
        <?php endif; ?>

        <!-- Step 2: Set Password (If password is empty) -->
        <?php if ($step == 2): ?>
            <form method="POST" action="">
                <input type="password" name="password" placeholder="Enter new password" required>
                <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                <button type="submit" name="set_password">Set Password</button>
                
            </form>
        <?php endif; ?>

        <!-- Step 3: Login Form (If password is already set) -->
        <?php if ($step == 3): ?>
            <form method="POST" action="">
                <input type="password" name="password" placeholder="Enter your password" required>
                <button type="submit" name="login">Login</button>
            </form>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
