<?php
// Handle actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    switch($action) {
        case 'view':
            $sql = "SELECT * FROM admin_users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $employee = $result->fetch_assoc();
            break;

        case 'delete':
            if ($is_admin && $id != $_SESSION['admin_id']) {
                $sql = "DELETE FROM admin_users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $success_message = "Employee deleted successfully";
                } else {
                    $error_message = "Error deleting employee";
                }
            } else {
                $error_message = "Access denied or cannot delete yourself";
            }
            break;
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_employee'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];

        $sql = "INSERT INTO admin_users (username, password, name, role, email) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $username, $password, $name, $role, $email);
        
        try {
            if ($stmt->execute()) {
                $success_message = "Employee added successfully";
            } else {
                $error_message = "Error adding employee";
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Duplicate entry error code
                $error_message = "Username already exists. Please choose a different username.";
            } else {
                $error_message = "An error occurred. Please try again.";
            }
        }
    }
}

// Get all employees
$sql = "SELECT * FROM admin_users ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<?php if (isset($employee)): ?>
    <div class="table-container">
        <h2>Employee Details</h2>
        <div class="employee-details">
            <table>
                <tr>
                    <th>Username:</th>
                    <td><?php echo htmlspecialchars($employee['username']); ?></td>
                </tr>
                <tr>
                    <th>Name:</th>
                    <td><?php echo htmlspecialchars($employee['name']); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo htmlspecialchars($employee['email']); ?></td>
                </tr>
                <tr>
                    <th>Role:</th>
                    <td><?php echo ucfirst(htmlspecialchars($employee['role'])); ?></td>
                </tr>
                <tr>
                    <th>Added On:</th>
                    <td><?php echo date('F j, Y', strtotime($employee['created_at'])); ?></td>
                </tr>
            </table>
            <div style="margin-top: 20px;">
                <a href="admin.php?page=employees" class="btn">Back to List</a>
                <?php if ($employee['id'] != $_SESSION['admin_id']): ?>
                    <a href="admin.php?page=employees&action=delete&id=<?php echo $employee['id']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this employee?')">Delete</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="table-container">
        <h2>Add New Employee</h2>
        <form method="POST" class="form-container">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="employee">Employee</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" name="add_employee" class="btn">Add Employee</button>
        </form>
    </div>

    <div class="table-container" style="margin-top: 20px;">
        <h2>Employees List</h2>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Added On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($row['role'])); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="admin.php?page=employees&action=view&id=<?php echo $row['id']; ?>" class="action-btn edit-btn">View</a>
                            <?php if ($row['id'] != $_SESSION['admin_id']): ?>
                                <a href="admin.php?page=employees&action=delete&id=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this employee?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?> 