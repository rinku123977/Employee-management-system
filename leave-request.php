<?php
session_start();
require_once 'config.php';

// Fetch only pending leave requests with start_date and end_date
$sql = "SELECT id, employee_id, employee_name, department, leave_type, start_date, end_date, reason, status 
        FROM leave_requests WHERE status = 'Pending'";
$result = $conn->query($sql);

// Check if there are results and display them in a table
if ($result->num_rows > 0) {
    echo "<style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th, td {
                padding: 12px;
                text-align: left;
                border: 1px solid #ddd;
            }
            th {
                background-color: rgb(97, 116, 214);
                color: white;
            }
            tr:nth-child(even) {
                background-color: #f2f2f2;
            }
            tr:hover {
                background-color: #ddd;
            }
            button {
                padding: 8px 12px;
                margin: 5px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
            }
            .approve {
                background-color: #4CAF50;
                color: white;
            }
            .reject {
                background-color: #f44336;
                color: white;
            }
            button:hover {
                opacity: 0.8;
            }
          </style>";

    echo "<table border='1'>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
            </tr>";
    
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $leaveRequestId = $row["id"]; // Fetching the unique leave request ID
        
        echo "<tr>
                <td>" . htmlspecialchars($row["employee_id"]) . "</td>
                <td>" . htmlspecialchars($row["employee_name"]) . "</td>
                <td>" . htmlspecialchars($row["department"]) . "</td>
                <td>" . htmlspecialchars($row["leave_type"]) . "</td>
                <td>" . htmlspecialchars($row["start_date"]) . "</td>
                <td>" . htmlspecialchars($row["end_date"]) . "</td>
                <td>" . htmlspecialchars($row["reason"]) . "</td>
                <td>Pending</td>
                <td>
                    <button class='approve' onclick='updateLeaveStatus(" . $leaveRequestId . ", \"Approved\")'>Approve</button>
                    <button class='reject' onclick='updateLeaveStatus(" . $leaveRequestId . ", \"Rejected\")'>Reject</button>
                </td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No pending leave requests.</p>";
}

// Close connection
$conn->close();
?>

<script>
function updateLeaveStatus(leaveRequestId, newStatus) {
    if (confirm("Are you sure you want to " + newStatus.toLowerCase() + " this request?")) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "update-status.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                alert("Status updated to " + newStatus + ".");
                location.reload();
            }
        };
        xhr.send("leave_request_id=" + leaveRequestId + "&status=" + newStatus);
    }
}
</script>
