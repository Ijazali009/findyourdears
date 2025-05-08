<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "missing_persons_db");

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process Contact Form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_contact'])) {
    try {
        $errors = [];
        
        $contactName = validateInput($_POST['contact_name']);
        $nameError = validateName($contactName);
        if($nameError) $errors[] = $nameError;

        $contactEmail = validateInput($_POST['contact_email']);
        if(empty($contactEmail)) {
            $errors[] = "Email is required";
        } elseif(!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address";
        }

        $contactSubject = validateInput($_POST['contact_subject']);
        if(empty($contactSubject)) {
            $errors[] = "Subject is required";
        } elseif(strlen($contactSubject) < 5 || strlen($contactSubject) > 200) {
            $errors[] = "Subject must be between 5 and 200 characters";
        }

        $contactMessage = validateInput($_POST['contact_message']);
        if(empty($contactMessage)) {
            $errors[] = "Message is required";
        } elseif(strlen($contactMessage) < 10) {
            $errors[] = "Message must be at least 10 characters long";
        }

        if(!empty($errors)) {
            throw new Exception(implode("<br>", $errors));
        }

        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if(!$stmt) {
            throw new Exception("Database error. Please try again later.");
        }

        $stmt->bind_param("ssss", $contactName, $contactEmail, $contactSubject, $contactMessage);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to save message. Please try again.");
        }

        $_SESSION['contact_success'] = "Your message has been sent successfully!";
        header("Location: contact.php");
        exit();

        $stmt->close();

    } catch (Exception $e) {
        $_SESSION['contact_error'] = $e->getMessage();
        header("Location: contact.php");
        exit();
    }
}

// Validate and sanitize input function
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate name function
function validateName($name) {
    if(empty($name)) {
        return "Name is required";
    }
    if(strlen($name) < 2 || strlen($name) > 100) {
        return "Name must be between 2 and 100 characters";
    }
    if(!preg_match("/^[a-zA-Z\s'-]+$/", $name)) {
        return "Name can only contain letters, spaces, hyphens and apostrophes";
    }
    return "";
}

// Get contact form messages from session
if(isset($_SESSION['contact_success'])) {
    $contactSuccessMessage = $_SESSION['contact_success'];
    unset($_SESSION['contact_success']);
}

if(isset($_SESSION['contact_error'])) {
    $contactErrorMessage = $_SESSION['contact_error'];
    unset($_SESSION['contact_error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Find Your Dears</title>
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

        .contact-container {
            max-width: 800px;
            margin: 100px auto 40px;
            padding: 20px;
        }

        .contact-info {
            background: #1a1a1a;
            border: 2px solid #0f0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.2);
        }

        .contact-info h2 {
            color: #0f0;
            margin-bottom: 20px;
            text-align: center;
            text-shadow: 0 0 10px #0f0;
        }

        .contact-details {
            display: grid;
            gap: 15px;
        }

        .contact-details p {
            margin: 0;
            padding: 10px;
            background: #333;
            border-radius: 5px;
            border: 1px solid #0f0;
        }

        .contact-details i {
            margin-right: 10px;
            color: #0f0;
        }

        .contact-form {
            background: #1a1a1a;
            border: 2px solid #0f0;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.2);
        }

        .contact-form h2 {
            color: #0f0;
            margin-bottom: 20px;
            text-align: center;
            text-shadow: 0 0 10px #0f0;
        }

        .form-field {
            margin-bottom: 20px;
        }

        .form-field label {
            display: block;
            margin-bottom: 5px;
            color: #0f0;
        }

        .form-field input,
        .form-field textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #0f0;
            border-radius: 5px;
            background: #333;
            color: #0f0;
        }

        .form-field textarea {
            min-height: 150px;
            resize: vertical;
        }

        .help-text {
            font-size: 0.9em;
            color: #666;
            margin: 5px 0;
        }

        .required::after {
            content: "*";
            color: #ff0000;
            margin-left: 3px;
        }

        button {
            padding: 10px 20px;
            background: #0f0;
            color: #000;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            transition: 0.3s;
        }

        button:hover {
            box-shadow: 0 0 15px #0f0;
        }

        .error {
            color: #ff0000;
            background: #330000;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .success {
            color: #00ff00;
            background: #003300;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
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

    <div class="contact-container">
        <div class="contact-info">
            <h2>Contact Information</h2>
            <div class="contact-details">
                <p><i class="fas fa-map-marker-alt"></i> Address: 123 Main Street, City, Country</p>
                <p><i class="fas fa-phone"></i> Phone: +1 234 567 8900</p>
                <p><i class="fas fa-envelope"></i> Email: contact@findyourdears.com</p>
                <p><i class="fas fa-clock"></i> Working Hours: Monday - Friday, 9:00 AM - 5:00 PM</p>
            </div>
        </div>

        <div class="contact-form">
            <h2>Send Us a Message</h2>
            <?php 
            if(isset($contactSuccessMessage)) echo "<div class='success'>$contactSuccessMessage</div>";
            if(isset($contactErrorMessage)) echo "<div class='error'>$contactErrorMessage</div>";
            ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-field">
                    <label class="required">Your Name</label>
                    <div class="help-text">Enter your full name</div>
                    <input type="text" name="contact_name" placeholder="e.g. John Smith" pattern="[a-zA-Z\s'-]+" required>
                </div>

                <div class="form-field">
                    <label class="required">Email Address</label>
                    <div class="help-text">Enter a valid email address</div>
                    <input type="email" name="contact_email" placeholder="e.g. john@example.com" required>
                </div>

                <div class="form-field">
                    <label class="required">Subject</label>
                    <div class="help-text">Brief description of your message</div>
                    <input type="text" name="contact_subject" placeholder="e.g. General Inquiry" required>
                </div>

                <div class="form-field">
                    <label class="required">Message</label>
                    <div class="help-text">Please provide details about your inquiry</div>
                    <textarea name="contact_message" placeholder="Type your message here..." minlength="10" required></textarea>
                </div>

                <button type="submit" name="submit_contact">Send Message</button>
            </form>
        </div>
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