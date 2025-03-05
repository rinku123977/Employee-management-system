<?php
session_start();
require 'config.php'; // Ensure this file contains your database connection settings

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php"); // Redirect to login if not logged in or not an admin
    exit();
}

// Handle accept/reject actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $requestId = $_POST['request_id'];
    $action = $_POST['action'];
    $empid = $_POST['empid'];
    $leavedays = $_POST['leavedays'];
    
    if ($action === 'accept') {
        // Update leave request status
        $sql = "UPDATE leave_requests SET status = 'approved' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Database error: " . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        $stmt->close(); // Close the statement after execution

        // Update leave balances
        $sql_days = "UPDATE leave_balances SET leaves_taken = leaves_taken + ? WHERE employee_id = ?";
        $stmt_days = $conn->prepare($sql_days);
        if ($stmt_days === false) {
            die("Database error: " . htmlspecialchars($conn->error));
        }
        $stmt_days->bind_param("ii", $leavedays, $empid);
        if ($stmt_days->execute()) {
            if ($stmt_days->affected_rows > 0) {
                // Successfully updated
            } else {
                echo "No updates made to leave balances. Check employee ID.";
            }
        } else {
            die("Error updating leave balances: " . htmlspecialchars($stmt_days->error));
        }
        $stmt_days->close(); // Close the statement after execution
    } elseif ($action === 'reject') {
        $sql = "UPDATE leave_requests SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Database error: " . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        $stmt->close(); // Close the statement after execution
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Leave Requests</title>
    <link rel="stylesheet" href="approved.css">
</head>
<body>
    <div class="container">
        <h2>Pending Leave Requests</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee Name</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Reason</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['employee_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['reason']); ?></td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <input type="hidden" name="empid" value="<?php echo htmlspecialchars($row['employee_id']); ?>">
                                <input type="hidden" name="leavedays" value="<?php echo htmlspecialchars($row['leave_days']); ?>">
                                <button type="submit" name="action" value="accept">Accept</button>
                                <button type="submit" name="action" value="reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>