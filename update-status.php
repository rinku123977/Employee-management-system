<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leaveRequestId = $_POST['leave_request_id'];
    $status = $_POST['status'];

    // Update the leave request status
    $updateRequestSql = "UPDATE leave_requests SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($updateRequestSql);
    $stmt->bind_param("si", $status, $leaveRequestId);
    if (!$stmt->execute()) {
        echo "Error updating leave request: " . $stmt->error;
        exit();
    }

    // If approved, update leaves_taken in leave_balances
    if ($status === "Approved") {
        // Fetch employee_id and leave_days from the leave request
        $employeeSql = "SELECT employee_id, leave_days FROM leave_requests WHERE id = ?";
        $stmt = $conn->prepare($employeeSql);
        $stmt->bind_param("i", $leaveRequestId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $employeeId = $row['employee_id'];
        $leaveDays = $row['leave_days'];

        // Update leaves_taken with the leave_days value
        $updateBalanceSql = "UPDATE leave_balances SET leaves_taken = leaves_taken + ? WHERE employee_id = ?";
        $stmt = $conn->prepare($updateBalanceSql);
        $stmt->bind_param("ii", $leaveDays, $employeeId);
        if (!$stmt->execute()) {
            echo "Error updating leave_balances: " . $stmt->error;
            exit();
        }
    }

    echo "Status updated successfully.";
} else {
    echo "Invalid request method.";
}

$conn->close();
?>