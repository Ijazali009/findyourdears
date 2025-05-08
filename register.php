<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "missing_persons_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $name = trim($_POST['name']);
    $cnic = trim($_POST['cnic']);
    $phone = trim($_POST['phone']);

    if (empty($username) || empty($password) || empty($confirm_password) || empty($name) || empty($cnic) || empty($phone)) {
        $error = "Please fill in all fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif (!preg_match("/^\d{5}-\d{7}-\d{1}$/", $cnic)) {
        $error = "CNIC must be in format: 12345-1234567-1";
    } elseif (!preg_match("/^[\+]?[\d\s-]{10,15}$/", $phone)) {
        $error = "Please enter a valid phone number (10-15 digits)";
    } else {
        // Check if username already exists
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Username already exists";
            } else {
                // Hash password and insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, password, name, cnic, phone) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("sssss", $username, $hashed_password, $name, $cnic, $phone);
                    if ($stmt->execute()) {
                        $success = "Registration successful! You can now login.";
                    } else {
                        $error = "Registration failed. Please try again.";
                    }
                } else {
                    $error = "Database error";
                }
            }
            $stmt->close();
        } else {
            $error = "Database error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Find Your Dears</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .register-container {
            background: #1a1a1a;
            padding: 30px;
            border-radius: 10px;
            border: 2px solid #0f0;
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 0 0 10px #0f0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 2px solid #0f0;
            border-radius: 5px;
            background: #333;
            color: #0f0;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #0f0;
            color: #000;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }

        button:hover {
            box-shadow: 0 0 15px #0f0;
        }

        .error {
            color: #ff0000;
            background: #330000;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success {
            color: #00ff00;
            background: #003300;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #0f0;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .help-text {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>Register</h1>
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="help-text">Format: Enter Your Username</div>

                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="name">Full Name</label>
                <div class="help-text">Enter Your Full Name</div>

                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="cnic">CNIC Number</label>
                <div class="help-text">Format: 12345-1234567-1</div>
                <input type="text" id="cnic" name="cnic" pattern="\d{5}-\d{7}-\d{1}" placeholder="12345-1234567-1" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <div class="help-text">Enter with country code if applicable</div>
                <input type="tel" id="phone" name="phone" pattern="[\+]?[\d\s-]{10,15}" placeholder="+1234567890" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="help-text">Enter Your Password</div>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="help-text">Enter Your Password Again</div>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html> 