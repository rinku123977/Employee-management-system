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
$sql = "SELECT * FROM attendance WHERE employee_id = ? ORDER BY date DESC"; // Assuming you have an 'attendance' table
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History</title>
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
    max-width: 800px; /* Increased width for attendance history */
    margin: 0 auto;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px;
}

/* Header */
h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: center;
}

th {
    background: #1a73e8;
    color: white;
}

td {
    background: #f9f9f9;
}

/* Responsive Styles */
@media (max-width: 600px) {
    .container {
        padding: 15px;
    }
}
th:hover {
    background:rgb(34, 97, 180);
}
</style>
    <div class="container">
        <h1>Attendance History</h1>

        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Check-in Time</th>
                    
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                       
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No attendance records found.</p>
        <?php endif; ?>
    </div>
</body>
</html>