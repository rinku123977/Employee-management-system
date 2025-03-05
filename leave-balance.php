<?php
session_start();
require_once 'config.php';

// Initialize variables for messages
$error = '';
$success = '';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch user leave data
$sql_leave_data = "SELECT lb.max_allowed_leaves, lb.remaining_leaves, lb.leaves_taken, 
                        e.first_name, e.last_name, d.name AS department_name
                   FROM leave_balances lb
                   LEFT JOIN employees e ON lb.employee_id = e.id
                   LEFT JOIN departments d ON e.department_id = d.id
                   WHERE lb.employee_id = ?";
$stmt_leave_data = $conn->prepare($sql_leave_data);
$stmt_leave_data->bind_param("i", $user_id);
$stmt_leave_data->execute();
$result_leave_data = $stmt_leave_data->get_result();
$leave_data = $result_leave_data->fetch_assoc() ?? null;


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Balance</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .balance-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .balance-card h3 {
            margin: 0;
            color: #333;
        }

        .balance-card p {
            font-size: 24px;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Leave Balance</h1>
        </div>

        <?php if ($leave_data): ?>
            <div class="balance-card">
                <h3>Maximum Allowed Leaves</h3>
                <p><?php echo htmlspecialchars($leave_data['max_allowed_leaves'] ?? '0'); ?></p>
            </div>
            <div class="balance-card">
                <h3>Remaining Leaves</h3>
                <p><?php echo htmlspecialchars($leave_data['remaining_leaves'] - $leave_data['leaves_taken']  ?? '0'); ?></p>
            </div>
            <div class="balance-card">
                <h3>Leaves Taken</h3>
                <p><?php echo htmlspecialchars($leave_data['leaves_taken'] ?? '0'); ?></p>
            </div>
        <?php else: ?>
            <p>No leave balance data available.</p>
        <?php endif; ?>
    </div>
</body>
</html>