<?php
session_start();
require_once 'config.php'; // Database connection

// Set the correct time zone
date_default_timezone_set('Asia/Kathmandu'); // Correct timezone for Kathmandu, Nepal

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect if not logged in
    exit();
}

$employeeId = $_SESSION['user_id']; // Get logged-in employee ID

// Fetch the employee's first name
$sql = "SELECT first_name FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Database error: " . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$username = $user['first_name'] ?? 'User';
$stmt->close();


// Get the current date and time
$currentDateTime = new DateTime(); // Current date and time
$currentDate = $currentDateTime->format('Y-m-d'); // Format: YYYY-MM-DD
$currentTime = $currentDateTime->format('H:i'); // Format: HH:MM
$cutoffTime = new DateTime('10:15'); // Attendance cut-off time

// Debugging: Log current date and time
error_log("Current Date: " . $currentDate);
error_log("Current Time: " . $currentTime);

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Convert current time into DateTime for accurate comparison
    $currentTimeObj = new DateTime($currentTime);

    // Determine status based on current time
    if ($currentTimeObj < $cutoffTime) {
        $status = 'Present'; // Mark as Present if before 09:15 AM
    } else {
        $status = 'Late'; // Mark as Late if after 09:15 AM
    }

    // Debugging output
    error_log("Status: " . $status);

    // Display or store status as needed
    echo "Attendance Status: " . $status;

    // Check if attendance has already been marked for today
    $checkSql = "SELECT * FROM attendance WHERE employee_id = ? AND date = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("is", $employeeId, $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $errorMessage = "Attendance already marked for today.";
    } else {
        // Insert new attendance record
        $insertSql = "INSERT INTO attendance (employee_id, date, status, name) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("isss", $employeeId, $currentDate, $status, $username);
        if ($stmt->execute()) {
            $successMessage = "Attendance marked successfully!";
        } else {
            $errorMessage = "Error marking attendance: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

// Fetch attendance records for the logged-in employee
$sql = "SELECT * FROM attendance WHERE employee_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
    
</head>
<body>
<style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2, h3 {
            color: #333;
          
        }
        .history-button {
            float: right; /* Aligns the button to the right */
            padding: 10px 15px;
            background-color: rgb(94, 94, 160);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none; 
            margin-left: 10px; 
        }
        .history-button:hover {
            background-color: rgb(116, 91, 170);
        }
        p.success {
            color: green;
        }
        p.error {
            color: red;
        }
        form {
            margin: 20px 0;
        }
        select, button {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
        }
        button {
            background-color:rgb(94, 94, 160);
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color:rgb(116, 91, 170);
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background: #e9ecef;
            margin: 5px 0;
            padding: 10px;
            border-radius: 4px;
        }
    </style>
    <div class="container">
        <h1>Attendance Management</h1>
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <a href="attendance history.php" class="button"> History</a>

        <?php if (isset($successMessage)) echo "<p class='success'>" . htmlspecialchars($successMessage) . "</p>"; ?>
        <?php if (isset($errorMessage)) echo "<p class='error'>" . htmlspecialchars($errorMessage) . "</p>"; ?>

        <h3>Mark Attendance:</h3>
        <form method="POST" action="" id="attendanceForm" onsubmit="return checkLocation();">
           
            <button type="submit">Present</button>
        </form>

        <h2>Your Attendance Records</h2>
        <ul>
            <?php while ($row = $result->fetch_assoc()): ?>
                <li><?php echo htmlspecialchars($row['date']) . " - " . htmlspecialchars($row['status']); ?></li>
            <?php endwhile; ?>
        </ul>
    </div>
    </html>
    
    
