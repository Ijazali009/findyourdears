<?php
// Handle actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    switch($action) {
        case 'view':
            $sql = "SELECT * FROM contact_messages WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $message = $result->fetch_assoc();
            break;

        case 'delete':
            if ($is_admin) {
                $sql = "DELETE FROM contact_messages WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    $success_message = "Message deleted successfully";
                } else {
                    $error_message = "Error deleting message";
                }
            } else {
                $error_message = "Access denied";
            }
            break;
    }
}

// Get all messages
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<?php if (isset($message)): ?>
    <div class="table-container">
        <h2>Message Details</h2>
        <div class="message-details">
            <table>
                <tr>
                    <th>From:</th>
                    <td><?php echo htmlspecialchars($message['name']); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo htmlspecialchars($message['email']); ?></td>
                </tr>
                <tr>
                    <th>Subject:</th>
                    <td><?php echo htmlspecialchars($message['subject']); ?></td>
                </tr>
                <tr>
                    <th>Message:</th>
                    <td><?php echo nl2br(htmlspecialchars($message['message'])); ?></td>
                </tr>
                <tr>
                    <th>Received On:</th>
                    <td><?php echo date('F j, Y g:i A', strtotime($message['created_at'])); ?></td>
                </tr>
            </table>
            <div style="margin-top: 20px;">
                <a href="admin.php?page=messages" class="btn">Back to List</a>
                <?php if ($is_admin): ?>
                    <a href="admin.php?page=messages&action=delete&id=<?php echo $message['id']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="table-container">
        <h2>Contact Messages</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Received On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="admin.php?page=messages&action=view&id=<?php echo $row['id']; ?>" class="action-btn edit-btn">View</a>
                            <?php if ($is_admin): ?>
                                <a href="admin.php?page=messages&action=delete&id=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this message?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?> 