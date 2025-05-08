<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "missing_persons_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all missing persons
$sql = "SELECT * FROM missing_persons ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missing Persons - Find Your Dears</title>
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
            min-height: 100vh;
            padding: 20px;
        }

        .navbar {
            background: #1a1a1a;
            padding: 15px 0;
            border-bottom: 2px solid #0f0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-links a {
            color: #0f0;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .nav-links a:hover {
            background: #0f0;
            color: #000;
        }

        .auth-links {
            display: flex;
            gap: 10px;
        }

        .auth-links a {
            color: #0f0;
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid #0f0;
            border-radius: 5px;
            transition: 0.3s;
        }

        .auth-links a:hover {
            background: #0f0;
            color: #000;
        }

        .header {
            text-align: center;
            padding: 100px 0 40px;
            border-bottom: 2px solid #0f0;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.5em;
            text-shadow: 0 0 10px #0f0;
            margin-bottom: 15px;
        }

        .missing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .missing-card {
            background: #1a1a1a;
            border: 2px solid #0f0;
            border-radius: 10px;
            padding: 20px;
            transition: 0.3s;
        }

        .missing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.4);
        }

        .missing-card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .missing-card h3 {
            color: #0f0;
            margin-bottom: 10px;
        }

        .missing-card p {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .missing-card .contact {
            color: #0f0;
            font-weight: bold;
        }

        .missing-card .date {
            color: #666;
            font-size: 0.9em;
        }

        .footer {
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            border-top: 2px solid #0f0;
        }

        .social-links {
            margin-top: 20px;
        }

        .social-links i {
            margin: 0 10px;
            font-size: 24px;
            cursor: pointer;
            transition: 0.3s;
        }

        .social-links i:hover {
            color: #fff;
            text-shadow: 0 0 10px #0f0;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-links">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="index.php">Home</a>
                    <a href="missing.php">Missing Persons</a>
                    <a href="found.php">Found Persons</a>
                    <a href="contact.php">Contact Us</a>
                <?php endif; ?>
            </div>
            <div class="auth-links">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="header">
        <h1>Missing Persons</h1>
        <p>Help us find our missing loved ones</p>
    </header>

    <div class="missing-grid">
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<div class="missing-card">';
                echo '<img src="' . htmlspecialchars($row['photo_url']) . '" alt="Missing person photo">';
                echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                echo '<p><strong>Last Seen:</strong> ' . htmlspecialchars($row['location']) . '</p>';
                echo '<p><strong>Missing Since:</strong> ' . htmlspecialchars($row['missing_date']) . '</p>';
                echo '<p class="contact">Contact: ' . htmlspecialchars($row['contact']) . '</p>';
                echo '<p class="date">Reported on: ' . htmlspecialchars($row['created_at']) . '</p>';
                echo '</div>';
            }
        } else {
            echo '<p style="text-align: center; grid-column: 1/-1;">No missing persons reported yet.</p>';
        }
        ?>
    </div>

    <footer class="footer">
        <p>Together we can make a difference</p>
        <div class="social-links">
            <i class="fab fa-facebook"></i>
            <i class="fab fa-twitter"></i>
            <i class="fab fa-instagram"></i>
            <i class="fab fa-linkedin"></i>
        </div>
    </footer>
</body>
</html> 