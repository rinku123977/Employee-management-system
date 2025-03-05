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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'email', 'phone', 'position', 'department_id', 'status'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All fields are required. Please fill in {$field}.");
            }
        }

        // Validate email
        $email = trim($_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Validate phone number (example: allow only digits and a length of 10-15)
        $phone = trim($_POST['phone']);
        if (!preg_match('/^\d{10,15}$/', $phone)) {
            throw new Exception("Phone number must be between 10 to 15 digits.");
        }

        // Prepare SQL statement
        $sql = "INSERT INTO employees (first_name, last_name, email, phone, position, department_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        // Prepare statement
        if ($stmt = $conn->prepare($sql)) {
            // Get and sanitize form data
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $position = trim($_POST['position']);
            $department_id = trim($_POST['department_id']);
            $status = trim($_POST['status']);

            // Bind parameters
            $stmt->bind_param("sssssss", 
                $first_name,
                $last_name,
                $email,
                $phone,
                $position,
                $department_id,
                $status
            );

            
            // Execute the statement
            if ($stmt->execute()) {
                $employeeId = $stmt->insert_id;

            // Insert into leave_balances for the new employee
            $maxAllowedLeaves = 20; // Example default value
            $remainingLeaves = 20; // Example default value
            $leavesTaken = 0; // Example default value

            $sql_leave_balance = "INSERT INTO leave_balances (employee_id, max_allowed_leaves, remaining_leaves, leaves_taken) VALUES (?, ?, ?, ?)";
            $stmt_leave_balance = $conn->prepare($sql_leave_balance);
            $stmt_leave_balance->bind_param("iiii", $employeeId, $maxAllowedLeaves, $remainingLeaves, $leavesTaken);

            if ($stmt_leave_balance->execute()) {
                $success .= " Leave balance initialized successfully.";
            } else {
                $error = "Error initializing leave balance: " . htmlspecialchars($stmt_leave_balance->error);
            }
                $_SESSION['success'] = "Employee {$first_name} {$last_name} has been added successfully!";
                header("Location: employee.php");
                exit();
    
            } else {
                throw new Exception("Error adding employee: " . $stmt->error);
            }
        } else {
            throw new Exception("Error preparing statement: " . $conn->error);
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
    <title>Add Employee</title>
    <link rel="stylesheet" href="add-employee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .form-group input, 
        .form-group select {
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f9f9f9;
            transition: border-color 0.3s;
        }

        .form-group input:focus, 
        .form-group select:focus {
            border-color: #007bff;
            outline: none;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-primary {
            background-color: #007bff;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }
    
</style>
    <div class="container">
        <div class="form-container">
            <h2><i class="fas fa-user-plus"></i> Add New Employee</h2>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="employee-form" novalidate>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" id="position" name="position" 
                               value="<?php echo isset($_POST['position']) ? htmlspecialchars($_POST['position']) : ''; ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id" required>
                            <option value="">Select Department</option>
                            <?php while ($dept = $dept_result->fetch_assoc()): ?>
                                <option value="<?php echo $dept['id']; ?>" 
                                    <?php echo (isset($_POST['department_id']) && $_POST['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="Active" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="employee.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Form validation
    document.querySelector('.employee-form').addEventListener('submit', function(e) {
        const position = document.getElementById('position').value.trim();
        const department = document.getElementById('department_id').value;
        
        if (!position) {
            e.preventDefault();
            alert('Please enter the employee position');
            document.getElementById('position').focus();
        }
        
        if (!department) {
            e.preventDefault();
            alert('Please select a department');
            document.getElementById('department_id').focus();
        }
    });
    </script>
</body>
</html>

