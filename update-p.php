<?php
session_start();
require_once 'config.php';

// Check admin login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect if not logged in
    exit();
}

$error = '';
$success = '';

// Fetch employee data based on session user_id
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    try {
        // Prepare SQL query to fetch employee data by user_id without position, department, status
        $sql = "SELECT id, first_name, last_name, email, phone FROM employees WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id); // Use 'i' for integer binding (user_id is assumed to be an integer)
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch the employee data
            $employee = $result->fetch_assoc();
        } else {
            $error = "No employee found with this ID.";
        }
    } catch (Exception $e) {
        $error = "Error fetching employee data: " . $e->getMessage();
    }
} else {
    $error = "User ID is not set in session.";
}

// Handle form submission to update employee data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'email', 'phone'];
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

        // Prepare SQL statement to update employee data (without position, department, status)
        $sql = "UPDATE employees SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?";

        // Prepare statement
        if ($stmt = $conn->prepare($sql)) {
            // Get and sanitize form data
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $employee_id = $_POST['employee_id']; // Fetch employee ID from hidden input

            // Bind parameters
            $stmt->bind_param("ssssi", 
                $first_name,
                $last_name,
                $email,
                $phone,
                $employee_id
            );

            // Execute the statement
            if ($stmt->execute()) {
                $_SESSION['success'] = "Employee {$first_name} {$last_name}'s data has been updated successfully!";
                header("Location: update-p.php");
                exit();
            } else {
                throw new Exception("Error updating employee: " . $stmt->error);
            }
        } else {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <link rel="stylesheet" href="add-employee.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <style>
        /* Styling here remains the same */
        /* General Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

/* Body */
body {
    background-color: #f4f6f9;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* Container */
.container {
    width: 100%;
    max-width: 800px;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Form Container */
.form-container {
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

/* Title */
h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

h2 i {
    margin-right: 8px;
    color: #4CAF50;
}

/* Alert Styles */
.alert {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
    font-size: 14px;
    font-weight: bold;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
}

/* Form */
.employee-form {
    display: grid;
    grid-template-columns: 1fr;
    gap: 15px;
}

/* Form Group */
.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 14px;
    color: #555;
    margin-bottom: 5px;
}

.form-group input,
.form-group select {
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #4CAF50;
    outline: none;
}

/* Form Actions */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
}

.form-actions a,
.form-actions button {
    padding: 12px 20px;
    font-size: 16px;
    border-radius: 5px;
    text-align: center;
    transition: background-color 0.3s ease;
}

.form-actions a {
    background-color: #ddd;
    color: #333;
    text-decoration: none;
}

.form-actions a:hover {
    background-color: #bbb;
}

.form-actions button {
    background-color: #4CAF50;
    color: #fff;
    border: none;
}

.form-actions button:hover {
    background-color: #45a049;
}

/* Input Styling */
input[readonly] {
    background-color: #f1f1f1;
    cursor: not-allowed;
}

/* Focus Input Style */
input:focus,
select:focus {
    border-color: #4CAF50;
}

/* Mobile Responsiveness */
@media (max-width: 600px) {
    .employee-form {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .form-actions a,
    .form-actions button {
        width: 100%;
        margin-top: 10px;
    }
}

    </style>
    <div class="container">
        <div class="form-container">
            <h2><i class="fas fa-user-edit"></i> Update Information </h2>

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
                    <!-- Hidden input for employee ID -->
                    <input type="hidden" name="employee_id" value="<?php echo $employee['id']; ?>">

                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($employee['first_name']); ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($employee['last_name']); ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($employee['email']); ?>" 
                               required >
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($employee['phone']); ?>" 
                               required>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="profile.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Form validation
    document.querySelector('.employee-form').addEventListener('submit', function(e) {
        let isValid = true;

        // Example validation: ensure all fields are filled out
        const inputs = document.querySelectorAll('input[required], select[required]');
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.style.borderColor = 'red'; // Mark invalid fields
            } else {
                input.style.borderColor = ''; // Reset on valid input
            }
        });

        if (!isValid) {
            e.preventDefault(); // Stop form submission if validation fails
        }
    });
    </script>
</body>
</html>
