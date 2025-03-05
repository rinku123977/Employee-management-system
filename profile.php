<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$sql = "SELECT employees.*, departments.name AS department_name 
        FROM employees 
        LEFT JOIN departments ON employees.department_id = departments.id 
        WHERE employees.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right,rgb(204, 203, 205),rgb(204, 203, 205));
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .profile-container {
            background: white;
            padding: 40px;
            border-radius: 40px;
            box-shadow: 0 4px 10px rgba(34, 52, 213, 0.1);
            text-align: center;
            max-width: 400px;
            height: 400px;
            color: #333;
        }
        .profile-container img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 3px solid #2575fc;
        }
        h2 {
            color: #6a11cb;
            margin-bottom: 10px;
        }
        p {
            font-size: 14px;
            margin: 5px 0;
        }
        .buttons {
            margin-top: 15px;
        }
        .button {
            display: inline-block;
            padding: 10px 15px;
            margin: 5px;
            border-radius: 5px;
            background: #2575fc;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }
        .button:hover {
            background: #6a11cb;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        
        <h2>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>!</h2>
        <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['first_name']); ?></p>
        <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['last_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
        <p><strong>Position:</strong> <?php echo htmlspecialchars($user['position']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($user['status']); ?></p>
        <p><strong>Department:</strong> <?php echo htmlspecialchars($user['department_name'] ?? 'Not Assigned'); ?></p> <!-- Added Department -->

        <div class="buttons">
            <a href="attendanceemp.php" class="button"> Attendance</a>
            <a href="leave-application.php" class="button">Apply for Leave</a>
            <a href="update-p.php" class="button">Update Profile</a>
            <a href="leave history.php" class="button">Leave Request History</a>
            <a href="logout.php" class="button">Logout</a>
        </div>
    </div>
</body>
</html>