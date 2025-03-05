<?php
session_start();
require_once 'config.php';

// Check if employee ID is provided
if (!isset($_GET['employee_id']) || empty($_GET['employee_id'])) {
    $_SESSION['error'] = "No employee selected!";
    header("Location: leave_history.php");
    exit();
}

$employee_id = (int)$_GET['employee_id'];

// Fetch employee details
$employee_query = $conn->prepare("SELECT first_name, last_name FROM employees WHERE id = ?");
$employee_query->bind_param("i", $employee_id);
$employee_query->execute();
$employee_result = $employee_query->get_result();
$employee = $employee_result->fetch_assoc();

// Fetch leave history for the employee
$history_query = $conn->prepare("SELECT reason, status, created_at FROM leave_requests WHERE employee_id = ? ORDER BY created_at DESC");
$history_query->bind_param("i", $employee_id);
$history_query->execute();
$history_result = $history_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave History</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #1a73e8;
        }
        .table {
            margin-top: 20px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .status-approved {
            background-color: #28a745;
            color: white;
        }
        .status-pending {
            background-color:rgb(125, 90, 118);
            color: black;
        }
        .status-rejected {
            background-color: #dc3545;
            color: white;
        }
        .btn-container {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Leave History for <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger text-center">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if ($history_result->num_rows > 0): ?>
        <table class="table table-striped">
            <thead class="table-primary">
                <tr>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Requested On</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($history = $history_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($history['reason']); ?></td>
                        <td>
                            <span class="status-badge 
                                <?php echo ($history['status'] == 'Approved') ? 'status-approved' : (($history['status'] == 'Pending') ? 'status-pending' : 'status-rejected'); ?>">
                                <?php echo htmlspecialchars($history['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date("F j, Y", strtotime($history['created_at'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <p>No leave requests yet.</p>
        </div>
    <?php endif; ?>
    
    <div class="btn-container">
        <a href="employee.php" class="btn btn-secondary">Back to Employee List</a>
        <a href="leave-balance.php?employee_id=<?php echo $employee_id; ?>" class="btn btn-primary">View Current Leave Balance</a>
    </div>
</div>

</body>
</html>
