<?php
// Handle actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    switch($action) {
        case 'view':
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            break;

        case 'delete':
            if ($is_admin) {
                $sql = "DELETE FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $success_message = "User deleted successfully";
                } else {
                    $error_message = "Error deleting user";
                }
            } else {
                $error_message = "Access denied";
            }
            break;
    }
}

// Get all users
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<?php if (isset($user)): ?>
    <div class="table-container">
        <h2>User Details</h2>
        <div class="user-details">
            <table>
                <tr>
                    <th>Username:</th>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                </tr>
                <tr>
                    <th>Name:</th>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                </tr>
                <tr>
                    <th>CNIC:</th>
                    <td><?php echo htmlspecialchars($user['cnic']); ?></td>
                </tr>
                <tr>
                    <th>Phone:</th>
                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                </tr>
                <tr>
                    <th>Member Since:</th>
                    <td><?php echo date('F j, Y', strtotime($user['created_at'])); ?></td>
                </tr>
            </table>
            <div style="margin-top: 20px;">
                <a href="admin.php?page=users" class="btn">Back to List</a>
                <?php if ($is_admin): ?>
                    <a href="admin.php?page=users&action=delete&id=<?php echo $user['id']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="table-container">
        <h2>Registered Users</h2>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Name</th>
                    <th>CNIC</th>
                    <th>Phone</th>
                    <th>Member Since</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['cnic']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="admin.php?page=users&action=view&id=<?php echo $row['id']; ?>" class="action-btn edit-btn">View</a>
                            <?php if ($is_admin): ?>
                                <a href="admin.php?page=users&action=delete&id=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?> 