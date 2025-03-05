<?php
session_start();
require_once 'config.php';

// Check admin login
if (!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit();
}

$error = '';
$success = '';

// Fetch employee data
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();

        if (!$employee) {
            $_SESSION['error'] = "Employee not found!";
            header("Location: employee.php");
            exit();
        }
    } catch (Exception $e) {
        $error = "Error fetching employee: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid employee ID!";
    header("Location: employee.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $position = trim($_POST['position']);
        $department_id = (int)$_POST['department_id'];
        $status = trim($_POST['status']);

        if (empty($position) || empty($department_id) || empty($status)) {
            throw new Exception("Please fill in all required fields.");
        }

        $sql = "UPDATE employees SET 
                position = ?, 
                department_id = ?, 
                status = ?
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssi",
            $position,
            $department_id,
            $status,
            $id
        );

        if ($stmt->execute()) {
            $_SESSION['success'] = "Employee updated successfully!";
            header("Location: employee.php");
            exit();
        } else {
            throw new Exception("Failed to update employee record.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch departments for dropdown
try {
    $dept_query = "SELECT id, name FROM departments ORDER BY name";
    $dept_result = $conn->query($dept_query);
} catch (Exception $e) {
    $error = "Error fetching departments: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <link rel="stylesheet" href="edit-employee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2><i class="fas fa-user-edit"></i> Edit Employee</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="employee-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($employee['first_name']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($employee['last_name']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($employee['email']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($employee['phone']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" id="position" name="position" 
                               value="<?php echo htmlspecialchars($employee['position']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id" required>
                            <?php while ($dept = $dept_result->fetch_assoc()): ?>
                                <option value="<?php echo $dept['id']; ?>" 
                                    <?php echo ($dept['id'] == $employee['department_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="Active" <?php echo ($employee['status'] == 'Active') ? 'selected' : ''; ?>>
                                Active
                            </option>
                            <option value="Inactive" <?php echo ($employee['status'] == 'Inactive') ? 'selected' : ''; ?>>
                                Inactive
                            </option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="employee.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>