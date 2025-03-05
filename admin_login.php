<?php
session_start();
require_once 'config.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['is_admin'] = true;
            
            header("Location: a-dashboard.php");
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Admin not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <style>
        body {
    font-family: 'Roboto', sans-serif;
    background-color: #f5f5f5;
    margin: 0;
    padding: 0;
}

.login-box {
    width: 400px;
    padding: 40px;
    position: relative;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin: 100px auto;
}

.login-header {
    text-align: center;
    margin-bottom: 20px;
}

.login-header header {
    font-size: 24px;
    color: #333;
}

.input-box {
    position: relative;
    margin-bottom: 30px;
}

.input-box i {
    position: absolute;
    left: 10px;
    top: 10px;
    color: #aaa;
}

.input-field {
    width: 80%;
    padding: 10px 40px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: #f9f9f9;
    transition: border-color 0.3s;
}

.input-field:focus {
    border-color: #007bff;
    outline: none;
}

.input-submit {
    text-align: center;
}

.submit-btn {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 5px;
    background: #007bff;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.submit-btn:hover {
    background: #0056b3;
}

.error-message {
    color: #721c24;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    padding: 10px;
    border-radius: 5px;
    margin-top: 20px;
    text-align: center;
}
</style>
    
    
    <div class="login-box">
        <div class="login-header">
            <header>Admin Login</header>
        </div>
        <form method="POST" action="">
            <div class="input-box">
                <i class="fas fa-user"></i>
                <input type="text" name="username" class="input-field" placeholder="Username or Email" required>
            </div>
            <div class="input-box">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" class="input-field" placeholder="Password" required>
            </div>
            <div class="input-submit">
                <input type="submit" name="login" value="Login" class="submit-btn">
            </div>
        </form>
        <?php if(isset($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 