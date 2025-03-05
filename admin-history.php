<?php
session_start();
require_once 'config.php';



// Get the selected date (default to today)
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch attendance records for employees who were present on the selected date
$sql_attendance = "SELECT employees.first_name, employees.last_name, attendance.status, attendance.created_at
                   FROM attendance 
                   JOIN employees ON attendance.employee_id = employees.id 
                   WHERE attendance.date = ?";
            

if (!$stmt = $conn->prepare($sql_attendance)) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("s", $selected_date);
$stmt->execute();
$result_attendance = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Present History</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            padding: 50px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }
        h1 {
            color: #333;
        }
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
        select, button {
            padding: 8px;
            margin-top: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1> Employee Attendance History</h1>

        <!-- Date Selection Form -->
        <form method="GET">
            <label for="date">Select Date: </label>
            <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($selected_date); ?>">
            <button type="submit">Check Attendance</button>
        </form>

        <h2>Employees Present on <?php echo htmlspecialchars($selected_date); ?></h2>
        <?php if ($result_attendance->num_rows > 0): ?>
            <table class="history-table">
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Status</th>
                    <th>Check-in time</th>
                </tr>
                <?php while ($row = $result_attendance->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No employees were attendance on this date.</p>
        <?php endif; ?>
    </div>
</body>
</html>