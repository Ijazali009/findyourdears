<?php
// Get recent missing persons
$sql = "SELECT * FROM missing_persons ORDER BY created_at DESC LIMIT 5";
$recent_missing = $conn->query($sql);

// Get recent found persons
$sql = "SELECT * FROM found_persons ORDER BY created_at DESC LIMIT 5";
$recent_found = $conn->query($sql);

// Get recent messages
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5";
$recent_messages = $conn->query($sql);
?>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Missing Persons</h3>
        <div class="number"><?php echo $stats['missing_persons']; ?></div>
    </div>
    <div class="stat-card">
        <h3>Found Persons</h3>
        <div class="number"><?php echo $stats['found_persons']; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Users</h3>
        <div class="number"><?php echo $stats['users']; ?></div>
    </div>
    <div class="stat-card">
        <h3>Messages</h3>
        <div class="number"><?php echo $stats['messages']; ?></div>
    </div>
</div>

<div class="table-container">
    <h2>Recent Missing Persons</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Location</th>
                <th>Date Missing</th>
                <th>Contact</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $recent_missing->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                    <td><?php echo htmlspecialchars($row['missing_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['contact']); ?></td>
                    <td>
                        <a href="admin.php?page=missing&action=view&id=<?php echo $row['id']; ?>" class="action-btn edit-btn">View</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="table-container">
    <h2>Recent Found Persons</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Location</th>
                <th>Contact</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $recent_found->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                    <td><?php echo htmlspecialchars($row['contact']); ?></td>
                    <td>
                        <a href="admin.php?page=found&action=view&id=<?php echo $row['id']; ?>" class="action-btn edit-btn">View</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="table-container">
    <h2>Recent Messages</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $recent_messages->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['subject']); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                    <td>
                        <a href="admin.php?page=messages&action=view&id=<?php echo $row['id']; ?>" class="action-btn edit-btn">View</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div> 