<?php
session_start();
require_once 'config.php'; // Ensure this file contains your database connection settings

// Handle form submission for adding a new department
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $departmentName = $_POST['department_name'];

    // Prepare SQL statement to insert new department
    $insertSql = "INSERT INTO departments (name) VALUES (?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("s", $departmentName);

    if ($stmt->execute()) {
        $successMessage = "Department added successfully!";
    } else {
        $errorMessage = "Error adding department: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
}

// Handle department deletion
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];

    // Prepare SQL statement to delete department
    $deleteSql = "DELETE FROM departments WHERE id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $deleteId);

    if ($stmt->execute()) {
        $successMessage = "Department deleted successfully!";
    } else {
        $errorMessage = "Error deleting department: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
}

// Fetch existing departments from the database
$sql = "SELECT * FROM departments";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Management</title>
    <link rel="stylesheet" href="departments.css"> <!-- Link to your external CSS file -->
</head>
<body>
    <style>
        /* departments.css */

/* Base Styles */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f0f2f5;
    padding: 50px;
}

/* Container */
.container {
    max-width: 400px; /* Width for the department management form */
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

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
}

input[type="text"] {
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

button {
    padding: 10px;
    background-color: #1a73e8;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #1557b0;
}

/* Success and Error Messages */
.success {
    color: green;
    text-align: center;
    margin-top: 10px;
}

.error {
    color: red;
    text-align: center;
    margin-top: 10px;
}

/* List Styles */
ul {
    list-style-type: none;
    padding: 0;
}

li {
    padding: 5px;
    border-bottom: 1px solid #ccc;
    display: flex;
    justify-content: space-between; /* Align items in the list */
    align-items: center; /* Center items vertically */
}

.delete-button {
    background-color:rgb(141, 218, 172); /* Bootstrap danger color */
    color: black;
    border: none;
    border-radius: 3px;
    padding: 5px 10px;
    cursor: pointer;
}

.delete-button:hover {
    background-color:rgb(165, 228, 178); /* Darker red on hover */
}
    </style>
    <div class="container">
        <h1>Department Management</h1>

        <?php if (!empty($successMessage)): ?>
            <p class="success"><?php echo htmlspecialchars($successMessage); ?></p>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="department_name" placeholder="Department Name" required>
            <button type="submit" name="action" value="add">Add Department</button>
        </form>

        <h2>Existing Departments</h2>
        <ul>
            <?php while ($row = $result->fetch_assoc()): ?>
                <li>
                    <?php echo htmlspecialchars($row['name']); ?>
                    <a href="?delete_id=<?php echo $row['id']; ?>" class="delete-button">Delete</a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html> 