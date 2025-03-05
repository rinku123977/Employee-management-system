<?php
session_start();
require_once 'config.php';

// Check admin login
if (!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle employee deletion
if (isset($_POST['delete_employee'])) {
    $id = (int)$_POST['employee_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Employee deleted successfully!";
        } else {
            throw new Exception("Error deleting employee: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: employee.php");
    exit();
}

// Fetch all employees with department names
try {
    $query = "SELECT e.*, d.name as department_name 
              FROM employees e 
              LEFT JOIN departments d ON e.department_id = d.id 
              ORDER BY e.id DESC";
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Error fetching employees: " . $conn->error);
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching employees: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
    <link rel="stylesheet" href="employee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users"></i> Employee Management</h1>
            <a href="add-employee.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Employee
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <?php if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($employee = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($employee['id']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                <td><?php echo htmlspecialchars($employee['phone']); ?></td>
                                <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                <td><?php echo htmlspecialchars($employee['department_name']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($employee['status']); ?>">
                                        <?php echo htmlspecialchars($employee['status']); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="edit-employee.php?id=<?php echo $employee['id']; ?>" 
                                       class="btn btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="deleteEmployee(<?php echo $employee['id']; ?>, '<?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>')" 
                                            class="btn btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <a href="history.php?employee_id=<?php echo $employee['id']; ?>" class="btn btn-info" title="History">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-users"></i>
                    <p>No employees found</p>
                    <a href="add-employee.php" class="btn btn-primary">Add Employee</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function deleteEmployee(id, name) {
        if (confirm(`Are you sure you want to delete ${name}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'employee.php';

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'employee_id';
            idInput.value = id;

            const submitInput = document.createElement('input');
            submitInput.type = 'hidden';
            submitInput.name = 'delete_employee';
            submitInput.value = '1';

            form.appendChild(idInput);
            form.appendChild(submitInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>
