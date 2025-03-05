<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$userId = $_SESSION['user_id'];
$sql_history = "SELECT leave_type, start_date, end_date, reason, status FROM leave_requests WHERE employee_id = ?";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("i", $userId);
$stmt_history->execute();
$result_history = $stmt_history->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave History</title>
    <link rel="stylesheet" href="profile.css">
</head>
<body>
<style>
/* Base Styles */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0f2f5;
    padding: 50px;
}

/* Container */
.container {
    max-width: 800px; /* Increased width for leave history */
    margin: 0 auto;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px;
    position: relative; /* Added for positioning the button */
}

/* Header */
h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

/* History Styles */
.history-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.history-table th, .history-table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: center;
}

.history-table th {
    background: #1a73e8;
    color: white;
}

.history-table td {
    background: #f9f9f9;
}

.history-table th:hover {
    background-color: rgb(45, 100, 152); /* Change background color on hover */
    color: #333; /* Change text color on hover */
}

/* Button Styles */
.right-corner-button {
    position: absolute; /* Positioning the button */
    top: 20px; /* Adjust as needed */
    right: 20px; /* Adjust as needed */
    background-color: #1a73e8; /* Button color */
    color: white; /* Text color */
    padding: 10px 15px; /* Padding */
    border: none; /* No border */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Pointer cursor */
    text-decoration: none; /* No underline */
}

.right-corner-button:hover {
    background-color: rgb(45, 100, 152); /* Darker shade on hover */
}

/* Responsive Styles */
@media (max-width: 600px) {
    .container {
        padding: 15px;
    }
}
</style>

    <div class="container">
        <a href="leave-balance.php" class="right-corner-button">View Status</a> <!-- Button inside the container -->
        <h2>Leave History</h2> <!-- Title for Leave History -->

        <?php if ($result_history->num_rows > 0): ?>
            <table class="history-table">
                <tr>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Reason</th>
                    <th>Status</th>
                </tr>
                <?php while ($row = $result_history->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['reason']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No leave history to display.</p> <!-- Placeholder content for leave history -->
        <?php endif; ?>
    </div>
</body>
</html>