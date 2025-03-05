<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "employee";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from AJAX request
$leave_request_id = $_POST['leave_request_id'];
$status = $_POST['status'];
$empid = $_POST['empid'];
$leavedays = $_POST['leavedays'];

// Update only the specific leave request
$sql = "UPDATE leave_requests SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $leave_request_id);

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

if ($stmt->execute()) {
    echo "Success";
} else {
    echo "Error updating status: " . $conn->error;
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
