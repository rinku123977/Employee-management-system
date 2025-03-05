<?php
session_start();
require_once 'config.php';


// Handle marking an employee as absent
if (isset($_GET['mark_absent_id'])) {
    $markAbsentId = (int)$_GET['mark_absent_id'];

    // Ensure the ID exists before updating
    $checkSql = "SELECT id FROM attendance WHERE id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("i", $markAbsentId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update the attendance status to 'Absent'
        $updateSql = "UPDATE attendance SET status = 'Absent' WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("i", $markAbsentId);

        if ($stmt->execute()) {
            $successMessage = "Attendance marked as absent successfully!";
        } else {
            $errorMessage = "Error marking attendance as absent: " . htmlspecialchars($stmt->error);
        }
    } else {
        $errorMessage = "Invalid attendance ID.";
    }
    $stmt->close();
}

// Fetch attendance records for the current date
$currentDate = date('Y-m-d'); // Get the current date in YYYY-MM-DD format
$sql = "SELECT * FROM attendance WHERE date = ? ORDER BY created_at DESC"; 
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentDate); // Bind the current date to the query

if ($stmt === false) {
    die("Error preparing statement: " . htmlspecialchars($conn->error));
}

$stmt->execute();
$result = $stmt->get_result();

$cutoffTime = '10:15'; // Attendance cut-off time
$currentTime = date('H:i'); // Get the current time
error_log("Current Time: " . $currentTime);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Dashboard</title>
    <link rel="stylesheet" href="attendance.css">
    <style>
        .attendance-table-container { margin-top: 20px; }
        .attendance-table { width: 100%; border-collapse: collapse; }
        .attendance-table th, .attendance-table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        .attendance-table th { background-color: #f0f2f5; }
        .status-badge { padding: 5px 10px; border-radius: 5px; color: white; }
        .status-badge.present { background-color: #28a745; }
        .status-badge.late { background-color: #ffc107; }
        .status-badge.absent { background-color: #dc3545; }
        .mark-absent-button { background-color: #ffc107; color: white; border: none; border-radius: 5px; padding: 5px 10px; cursor: pointer; text-decoration: none; }
        .mark-absent-button:hover { background-color: #e0a800; }

        /* New styles for the button in the right corner */
        .right-corner-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #1a73e8;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
            text-decoration: none;
        }

        .right-corner-button:hover {
            background-color: #1557b0;
        }
    </style>
</head>
<body>
    <a href="admin-history.php" class="right-corner-button">History</a> <!-- Button in the right corner -->
    <div class="dashboard-container">
        <h2>Attendance Records for <?php echo htmlspecialchars($currentDate); ?></h2>
        
        <?php if (isset($successMessage)): ?>
            <p class="success"><?php echo htmlspecialchars($successMessage); ?></p>
        <?php endif; ?>
        <?php if (isset($errorMessage)): ?>
            <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <div class="attendance-table-container">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['employee_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                            <td>
                                <span class="status-badge <?php echo strtolower(htmlspecialchars($row['status'])); ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td>
                                <?php if (strtolower($row['status']) !== 'absent'): ?>
                                    <a href="?mark_absent_id=<?php echo $row['id']; ?>" class="mark-absent-button">Mark Absent</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
