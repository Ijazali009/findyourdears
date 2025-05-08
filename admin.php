<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "missing_persons_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create admin_users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'employee') NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(!$conn->query($sql)) {
    die("Error creating admin_users table: " . $conn->error);
}

// Check if admin exists, if not create default admin
$sql = "SELECT * FROM admin_users WHERE role = 'admin' LIMIT 1";
$result = $conn->query($sql);

if($result->num_rows == 0) {
    $default_admin_password = password_hash("admin123", PASSWORD_DEFAULT);
    $sql = "INSERT INTO admin_users (username, password, name, role, email) 
            VALUES ('admin', '$default_admin_password', 'System Admin', 'admin', 'admin@example.com')";
    $conn->query($sql);
}

// Login handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin_users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_name'] = $user['name'];
            header("Location: admin.php");
            exit();
        }
    }
    $login_error = "Invalid username or password";
}

// Logout handling
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['admin_id']);
$is_admin = isset($_SESSION['admin_role']) && $_SESSION['admin_role'] == 'admin';

// Get statistics
$stats = array();
if ($is_logged_in) {
    // Total missing persons
    $sql = "SELECT COUNT(*) as count FROM missing_persons";
    $result = $conn->query($sql);
    $stats['missing_persons'] = $result->fetch_assoc()['count'];

    // Total found persons
    $sql = "SELECT COUNT(*) as count FROM found_persons";
    $result = $conn->query($sql);
    $stats['found_persons'] = $result->fetch_assoc()['count'];

    // Total users
    $sql = "SELECT COUNT(*) as count FROM users";
    $result = $conn->query($sql);
    $stats['users'] = $result->fetch_assoc()['count'];

    // Total contact messages
    $sql = "SELECT COUNT(*) as count FROM contact_messages";
    $result = $conn->query($sql);
    $stats['messages'] = $result->fetch_assoc()['count'];
}

// Handle form submissions
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_employee']) && $is_admin) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];

        // Check if username already exists
        $check_sql = "SELECT id FROM admin_users WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Username '$username' already exists. Please choose a different username.";
        } else {
            $sql = "INSERT INTO admin_users (username, password, name, role, email) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $username, $password, $name, $role, $email);
            
            if ($stmt->execute()) {
                $success_message = "Employee added successfully";
            } else {
                $error_message = "Error adding employee: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Find Your Dears</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', monospace;
        }

        body {
            background: #000;
            color: #0f0;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #111;
            color: #0f0;
            padding: 20px;
            border-right: 1px solid #0f0;
        }

        .sidebar h2 {
            margin-bottom: 30px;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #0f0;
            text-shadow: 0 0 10px #0f0;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-menu li {
            margin-bottom: 10px;
        }

        .nav-menu a {
            color: #0f0;
            text-decoration: none;
            display: block;
            padding: 10px;
            border: 1px solid #0f0;
            border-radius: 5px;
            transition: 0.3s;
        }

        .nav-menu a:hover {
            background: #0f0;
            color: #000;
            box-shadow: 0 0 10px #0f0;
        }

        .nav-menu a.active {
            background: #0f0;
            color: #000;
            box-shadow: 0 0 20px #0f0;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .header {
            background: #111;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #0f0;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 10px #0f0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #111;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #0f0;
            box-shadow: 0 0 10px #0f0;
        }

        .stat-card h3 {
            color: #0f0;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 2em;
            color: #0f0;
            font-weight: bold;
            text-shadow: 0 0 10px #0f0;
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background: #111;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid #0f0;
            box-shadow: 0 0 20px #0f0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #0f0;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            background: #000;
            border: 1px solid #0f0;
            border-radius: 5px;
            color: #0f0;
        }

        .btn {
            background: #000;
            color: #0f0;
            padding: 10px 20px;
            border: 1px solid #0f0;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            background: #0f0;
            color: #000;
            box-shadow: 0 0 10px #0f0;
        }

        .error {
            color: #f00;
            margin-bottom: 20px;
            text-shadow: 0 0 10px #f00;
        }

        .success {
            color: #0f0;
            margin-bottom: 20px;
            text-shadow: 0 0 10px #0f0;
        }

        .table-container {
            background: #111;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #0f0;
            box-shadow: 0 0 10px #0f0;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #0f0;
        }

        th {
            background: #000;
            color: #0f0;
        }

        tr:hover {
            background: #222;
        }

        .action-btn {
            padding: 5px 10px;
            border-radius: 3px;
            color: #000;
            text-decoration: none;
            margin-right: 5px;
            border: 1px solid #0f0;
        }

        .edit-btn {
            background: #0f0;
        }

        .delete-btn {
            background: #f00;
            border-color: #f00;
        }
    </style>
</head>
<body>
    <?php if (!$is_logged_in): ?>
        <div class="login-container">
            <h2>Admin & Employee Login</h2>
            <?php if (isset($login_error)): ?>
                <div class="error"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn">Login</button>
            </form>
        </div>
    <?php else: ?>
        <div class="admin-container">
            <div class="sidebar">
                <h2>Admin Panel</h2>
                <ul class="nav-menu">
                    <li><a href="admin.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="admin.php?page=missing"><i class="fas fa-search"></i> Missing Persons</a></li>
                    <li><a href="admin.php?page=found"><i class="fas fa-user-check"></i> Found Persons</a></li>
                    <li><a href="admin.php?page=messages"><i class="fas fa-envelope"></i> Messages</a></li>
                    <li><a href="admin.php?page=users"><i class="fas fa-users"></i> Users</a></li>
                    <?php if ($is_admin): ?>
                        <li><a href="admin.php?page=employees"><i class="fas fa-user-tie"></i> Employees</a></li>
                    <?php endif; ?>
                    <li><a href="admin.php?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>

            <div class="main-content">
                <div class="header">
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h1>
                    <div>
                        <span>Role: <?php echo ucfirst($_SESSION['admin_role']); ?></span>
                    </div>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <?php
                $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
                
                switch($page) {
                    case 'dashboard':
                        include 'admin/dashboard.php';
                        break;
                    case 'missing':
                        include 'admin/missing.php';
                        break;
                    case 'found':
                        include 'admin/found.php';
                        break;
                    case 'messages':
                        include 'admin/messages.php';
                        break;
                    case 'users':
                        include 'admin/users.php';
                        break;
                    case 'employees':
                        if ($is_admin) {
                            include 'admin/employees.php';
                        } else {
                            echo "<div class='error'>Access denied</div>";
                        }
                        break;
                    default:
                        include 'admin/dashboard.php';
                }
                ?>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>