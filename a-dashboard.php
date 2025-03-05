<?php
session_start();
require_once 'config.php';

// Check admin login
if (!isset($_SESSION['is_admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Initialize variables
$total_employees = $active_employees = $departments = 0;

// Fetch counts with error handling
try {
    // Total employees
    $result = $conn->query("SELECT COUNT(*) as count FROM employees");
    if ($result === false) {
        throw new Exception("Error counting employees: " . $conn->error);
    }
    $total_employees = $result->fetch_assoc()['count'];

    // Active employees
    $result = $conn->query("SELECT COUNT(*) as count FROM employees WHERE status = 'Active'");
    if ($result === false) {
        throw new Exception("Error counting active employees: " . $conn->error);
    }
    $active_employees = $result->fetch_assoc()['count'];

    // Departments
    $result = $conn->query("SELECT COUNT(*) as count FROM departments");
    if ($result === false) {
        throw new Exception("Error counting departments: " . $conn->error);
    }
    $departments = $result->fetch_assoc()['count'];

    // Fetch employees for table
    $employees_query = "SELECT e.*, d.name as dept_name 
                       FROM employees e 
                       LEFT JOIN departments d ON e.department_id = d.id 
                       ORDER BY e.id DESC LIMIT 5";
    $employees = $conn->query($employees_query);
    if ($employees === false) {
        throw new Exception("Error fetching employees: " . $conn->error);
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    // You might want to show an error message to the admin
    $_SESSION['error'] = "There was an error loading the dashboard data.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <style>
       /* General Styles */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
}

/* Container */
.container {
    display: flex;
}

/* Sidebar Styles */
.sidebar {
    width: 250px;
    background-color: #007bff; /* Blue background */
    color: white;
    padding: 20px;
    height: 100vh;
}

.sidebar .logo {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.sidebar .logo i {
    font-size: 24px;
    margin-right: 10px;
}

.sidebar nav a {
    display: flex;
    align-items: center;
    color: white;
    text-decoration: none;
    padding: 10px;
    margin: 5px 0;
    border-radius: 5px;
    transition: background 0.3s;
}

.sidebar nav a:hover {
    background-color: #0056b3; /* Darker blue on hover */
}

.sidebar nav a.active {
    background-color: #0056b3; /* Active link color */
}

/* Main Content Styles */
.main-content {
    flex: 1;
    padding: 20px;
    background-color: #ffffff; /* White background */
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.header h1 {
    margin: 0;
}

.user-info {
    display: flex;
    align-items: center;
}

.admin-badge {
    background-color: #007bff;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    margin-left: 10px;
}

/* Stats Cards Styles */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-info {
    text-align: left;
}

.stat-info h3 {
    margin: 0;
    font-size: 1.2em;
}

.stat-info .number {
    font-size: 2em;
    font-weight: bold;
}

.stat-icon {
    font-size: 2em;
    color: #007bff;
}

/* Employee Management Section Styles */
.management-section {
    margin-top: 20px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-header a {
    background-color: #007bff;
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    text-decoration: none;
}

.table-container {
    margin-top: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #ccc;
}

th {
    background-color: #f0f2f5;
}

.employee-info {
    display: flex;
    align-items: center;
}

.employee-info img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
}

.status {
    padding: 5px 10px;
    border-radius: 5px;
    color: white;
}

.status.active {
    background-color: #28a745; /* Green for active */
}

.status.inactive {
    background-color: #dc3545; /* Red for inactive */
}

/* Button Styles */
button {
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 10px 15px;
    cursor: pointer;
    transition: background 0.3s;
}

button:hover {
    background-color: #0056b3; /* Darker blue on hover */
}

/* Responsive Styles */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr; /* Stack cards on smaller screens */
    }
}
</style>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-users-cog"></i>
                <span>Admin Panel</span>
            </div>
            <nav>
                <a href="a-dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="employee.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'employee.php') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Employees</span>
                </a>
                
                <a href="departments.php">
                    <i class="fas fa-building"></i>
                    <span>Departments</span>
                </a>
                <a href="attendance.php" >
                    <i class="fas fa-calendar-check"></i>
                    <span>Attendance</span>
                </a>
                
                <a href="leave-request.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'leave-request.php') ? 'active' : ''; ?>">
                    <i class="fas fa-paper-plane"></i>
                    <span>Leave Request</span>
                </a>
                <a href="logout.php" class="logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <div class="admin-badge">Admin</div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Total Employees</h3>
                        <p class="number"><?php echo $total_employees; ?></p>
                    </div>
                    <i class="fas fa-users stat-icon"></i>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Active Employees</h3>
                        <p class="number"><?php echo $active_employees; ?></p>
                    </div>
                    <i class="fas fa-user-check stat-icon"></i>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Departments</h3>
                        <p class="number"><?php echo $departments; ?></p>
                    </div>
                    <i class="fas fa-building stat-icon"></i>
                </div>
            </div>

            <!-- Employee Management Section -->
            <div class="management-section">
                <div class="section-header">
                    <h2>Employee Management</h2>
                    <a href="/Employee/add-employee.php">Add Employee</a>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Department</th>
                                <th>Status</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($employees && $employees->num_rows > 0): ?>
                                <?php while($employee = $employees->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="employee-info">
                                            <img src="<?php echo htmlspecialchars($employee['profile_image'] ?? 'assets/default-avatar.png'); ?>" alt="">
                                            <div>
                                                <p class="name"><?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?></p>
                                                <p class="email"><?php echo htmlspecialchars($employee['email']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($employee['dept_name'] ?? 'Not Assigned'); ?></td>
                                    <td>
                                        <span class="status <?php echo strtolower($employee['status']); ?>">
                                            <?php echo htmlspecialchars($employee['status']); ?>
                                        </span>
                                    </td>
                                    
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No employees found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function deleteEmployee(id) {
        if(confirm('Are you sure you want to delete this employee?')) {
            location.href = 'delete_employee.php?id=' + id;
        }
    }
    </script>
</body>
</html> 