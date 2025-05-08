<?php
// Handle actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    switch($action) {
        case 'view':
            $sql = "SELECT * FROM found_persons WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $person = $result->fetch_assoc();
            break;

        case 'delete':
            if ($is_admin) {
                // First delete the record
                $sql = "DELETE FROM found_persons WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    // Reset auto increment
                    $conn->query("ALTER TABLE found_persons AUTO_INCREMENT = 1");
                    // Reset IDs to be sequential
                    $conn->query("SET @count = 0");
                    $conn->query("UPDATE found_persons SET id = @count:= @count + 1");
                    $success_message = "Record deleted successfully";
                } else {
                    $error_message = "Error deleting record";
                }
            } else {
                $error_message = "Access denied";
            }
            break;
    }
}

// Get all found persons
$sql = "SELECT * FROM found_persons ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<?php if (isset($person)): ?>
    <div class="table-container">
        <h2>Found Person Details</h2>
        <div class="person-details">
            <img src="<?php echo htmlspecialchars($person['photo_url']); ?>" alt="Person photo" style="max-width: 300px; margin-bottom: 20px;">
            <table>
                <tr>
                    <th>Name:</th>
                    <td><?php echo htmlspecialchars($person['name']); ?></td>
                </tr>
                <tr>
                    <th>Description:</th>
                    <td><?php echo htmlspecialchars($person['description']); ?></td>
                </tr>
                <tr>
                    <th>Location Found:</th>
                    <td><?php echo htmlspecialchars($person['location']); ?></td>
                </tr>
                <tr>
                    <th>Contact:</th>
                    <td><?php echo htmlspecialchars($person['contact']); ?></td>
                </tr>
                <tr>
                    <th>Reported On:</th>
                    <td><?php echo date('F j, Y', strtotime($person['created_at'])); ?></td>
                </tr>
            </table>
            <div style="margin-top: 20px;">
                <a href="admin.php?page=found" class="btn">Back to List</a>
                <?php if ($is_admin): ?>
                    <a href="admin.php?page=found&action=delete&id=<?php echo $person['id']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="table-container">
        <h2>Found Persons List</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Contact</th>
                    <th>Reported On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="admin.php?page=found&action=view&id=<?php echo $row['id']; ?>" class="action-btn edit-btn">View</a>
                            <?php if ($is_admin): ?>
                                <a href="admin.php?page=found&action=delete&id=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this record?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?> 