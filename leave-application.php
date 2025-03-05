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

// Get user data including department name
$user_id = $_SESSION['user_id'];
$sql = "SELECT employees.*, departments.name AS department_name 
        FROM employees 
        LEFT JOIN departments ON employees.department_id = departments.id 
        WHERE employees.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc() ?? null;

// Fetch user leave data
$sql_leave_data = "SELECT max_allowed_leaves, remaining_leaves, leaves_taken
                   FROM leave_balances 
                   WHERE employee_id = ?";
$stmt_leave_data = $conn->prepare($sql_leave_data);
$stmt_leave_data->bind_param("i", $user_id);
$stmt_leave_data->execute();
$result_leave_data = $stmt_leave_data->get_result();
$leave_data = $result_leave_data->fetch_assoc() ?? null;

// Handle leave request submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_leave_request'])) {
    $employeeName = $_POST['employeeName'];
    $employeeId = $_POST['employeeId'];
    $department = $_POST['department'];
    $leaveType = $_POST['leaveType'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $reason = $_POST['reason'];
    
    // Calculate the number of leave days requested
    $startDateObj = new DateTime($startDate);
    $endDateObj = new DateTime($endDate);
    $interval = $startDateObj->diff($endDateObj);
    $daysRequested = $interval->days + 1; // Include the end date

    // Check for empty fields
    if (empty($employeeId) || empty($department) || empty($leaveType) || empty($startDate) || empty($endDate) || empty($reason)) {
        $error = "All fields are required.";
    } else {
        // Check if the leave request has already been submitted for today
        $today = date('Y-m-d');
        $check_sql = "SELECT * FROM leave_requests WHERE employee_id = ? AND DATE(start_date) = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("is", $employeeId, $today);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error = "You have already submitted a leave request for today.";
        } else {
            // Check if the leave request already exists
            $existing_request_sql = "SELECT * FROM leave_requests WHERE employee_id = ? AND start_date = ? AND end_date = ?";
            $existing_request_stmt = $conn->prepare($existing_request_sql);
            $existing_request_stmt->bind_param("iss", $employeeId, $startDate, $endDate);
            $existing_request_stmt->execute();
            $existing_request_result = $existing_request_stmt->get_result();

            if ($existing_request_result->num_rows > 0) {
                // Handle existing request case
            } else {
                // Check if start date is not in the past
                if ($startDate < $today) {
                    $error = "Cannot apply for leave on past dates.";
                } else {
                    // Check if the end date is after the start date
                    if ($endDate < $startDate) {
                        $error = "End date cannot be before start date.";
                    } else {
                        // Check if the user has enough remaining leaves
                        if ($leave_data && $leave_data['remaining_leaves'] < $daysRequested) {
                            $error = "You do not have enough remaining leaves.";
                        } else {
                            // Insert leave request
                            $sql = "INSERT INTO leave_requests ( 
                                employee_id, 
                                department, 
                                leave_type, 
                                start_date, 
                                end_date, 
                                reason, 
                                status,
                                leave_days
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                            
                            $stmt = $conn->prepare($sql);
                            if ($stmt === false) {
                                die("Database error: " . htmlspecialchars($conn->error));
                            }
                            $status = 'Pending';
                            $stmt->bind_param(
                                "ssssssss",
                                $employeeId,
                                $department,
                                $leaveType,
                                $startDate,
                                $endDate,
                                $reason,
                                $status,
                                $daysRequested // Store the calculated days here
                            );

                            if ($stmt->execute()) {
                                // Update leave balances
                                // if ($leave_data) {
                                //     $new_remaining = $leave_data['remaining_leaves'] - $daysRequested;
                                //     $new_taken = $leave_data['leaves_taken'] + $daysRequested;
                                    
                                //     $update_sql = "UPDATE leave_requests
                                //                    SET remaining_leaves = ?, 
                                //                        leaves_taken = ? 
                                //                    WHERE employee_id = ? AND leave_type = ?";
                                //     $update_stmt = $conn->prepare($update_sql);
                                //     $update_stmt->bind_param("iiis", $new_remaining, $new_taken, $employeeId, $leaveType);
                                //     $update_stmt->execute();
                                //     $update_stmt->close();
                                // }
                                $success = "Leave request submitted successfully.";
                                header("Location: leave-application.php"); // Redirect to leave same page
                                exit();
                            } else {
                                $error = "Error: " . htmlspecialchars($stmt->error);
                            }
                            $stmt->close();
                        }
                    }
                }
            }
            $check_stmt->close();
        }
    }
}

// Fetch leave request history
$sql_history = "SELECT id, leave_type, start_date, end_date, reason, status, leave_days
                FROM leave_requests 
                WHERE employee_id = ? 
                ORDER BY start_date DESC";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("i", $user_id);
$stmt_history->execute();
$result_history = $stmt_history->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Application</title>
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

        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .leave-balance {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .balance-card {
            background: #fff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn-submit {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .btn-submit:hover {
            background: #0056b3;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .history-table th,
        .history-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .history-table th {
            background: #f8f9fa;
            font-weight: bold;
        }

        .status-pending {
            color: #856404;
            background: #fff3cd;
            padding: 5px 10px;
            border-radius: 3px;
        }

        .status-approved {
            color: #155724;
            background: #d4edda;
            padding: 5px 10px;
            border-radius: 3px;
        }

        .status-rejected {
            color: #721c24;
            background: #f8d7da;
            padding: 5px 10px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Leave Application Form</h1>
        </div>

        <?php if ($user): ?>
            <div class="user-info">
                <h2>Welcome, <?php echo htmlspecialchars($user['first_name']); ?></h2>
                <p>Department: <strong><?php echo htmlspecialchars($user['department_name'] ?? 'Not Assigned'); ?></strong></p>
            </div>

            <div class="leave-balance">
                <div class="balance-card">
                    <h3>Maximum Leaves</h3>
                    <p><?php echo htmlspecialchars($leave_data['max_allowed_leaves'] ?? '0'); ?></p>
                </div>
                <div class="balance-card">
                    <h3>Remaining Leaves</h3>
                    <p><?php echo htmlspecialchars($leave_data['remaining_leaves']- $leave_data['leaves_taken'] ?? '0'); ?></p>
                </div>
                <div class="balance-card">
                    <h3>Leaves Taken</h3>
                    <p><?php echo htmlspecialchars($leave_data['leaves_taken'] ?? '0'); ?></p>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST">
                    <input type="hidden" name="employeeId" value="<?php echo htmlspecialchars($user['id'] ?? ''); ?>">
                    <input type="hidden" name="employeeName" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                    <input type="hidden" name="department" value="<?php echo htmlspecialchars($user['department_name'] ?? ''); ?>">

                    <div class="form-group">
                        <label for="leaveType">Leave Type:</label>
                        <select name="leaveType" id="leaveType" required>
                            <option value="">Select Leave Type</option>
                            <option value="Annual">Annual Leave</option>
                            <option value="Sick">Sick Leave</option>
                            <option value="Personal">Personal Leave</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="startDate">Start Date:</label>
                        <input type="date" id="startDate" name="startDate" required>
                    </div>

                    <div class="form-group">
                        <label for="endDate">End Date:</label>
                        <input type="date" id="endDate" name="endDate" required>
                    </div>

                    <div class="form-group">
                        <label for="reason">Reason:</label>
                        <textarea id="reason" name="reason" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="days">Days:</label>
                        <input type="text" id="days" name="days" readonly>
                    </div>

                    <button type="submit" name="submit_leave_request" class="btn-submit">Submit Leave Request</button>
                </form>
            </div>

            <h2>Leave Request History</h2>
            <?php if ($result_history->num_rows > 0): ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Reason</th>
                            <th>Days</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_history->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                <td><?php echo htmlspecialchars($row['leave_days']); ?></td>
                                <td>
                                    <span class="status-<?php echo strtolower($row['status']); ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No leave requests found.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>User information not available.</p>
        <?php endif; ?>
    </div>
    <script>
        // JavaScript to calculate the number of days between start and end dates
        document.getElementById('startDate').addEventListener('change', calculateDays);
        document.getElementById('endDate').addEventListener('change', calculateDays);

        function calculateDays() {
            const startDate = new Date(document.getElementById('startDate').value);
            const endDate = new Date(document.getElementById('endDate').value);
            const daysInput = document.getElementById('days');

            if (startDate && endDate && endDate >= startDate) {
                const timeDiff = endDate - startDate;
                const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // Include end date
                daysInput.value = daysDiff;
            } else {
                daysInput.value = '';
            }
        }
    </script>
</body>
</html>