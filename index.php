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

// Create users table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    cnic VARCHAR(15) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(!$conn->query($sql)) {
    die("Error creating users table: " . $conn->error);
}

// Create tables if they don't exist
$sql = "CREATE TABLE IF NOT EXISTS missing_persons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    contact VARCHAR(15) NOT NULL,
    location VARCHAR(255) NOT NULL,
    missing_date DATE NOT NULL,
    photo_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(!$conn->query($sql)) {
    die("Error creating missing_persons table: " . $conn->error);
}

$sql = "CREATE TABLE IF NOT EXISTS found_persons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    contact VARCHAR(15) NOT NULL,
    location VARCHAR(255) NOT NULL,
    photo_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(!$conn->query($sql)) {
    die("Error creating found_persons table: " . $conn->error);
}

// Create contact table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(!$conn->query($sql)) {
    die("Error creating contact_messages table: " . $conn->error);
}

// Initialize error messages
$missingPersonMessage = "";
$foundPersonMessage = "";
$searchMessage = "";
$searchResults = array();

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

// Validate description function
function validateDescription($desc) {
    if(empty($desc)) {
        return "Description is required";
    }
    if(strlen($desc) < 10) {
        return "Description must be at least 10 characters long";
    }
    return "";
}

// Validate contact function
function validateContact($contact) {
    if(empty($contact)) {
        return "Contact number is required";
    }
    // Allow +, -, spaces and numbers
    if(!preg_match("/^[\+]?[\d\s-]{10,15}$/", $contact)) {
        return "Please enter a valid contact number (10-15 digits)";
    }
    return "";
}

// Validate location function
function validateLocation($location) {
    if(empty($location)) {
        return "Location is required";
    }
    if(strlen($location) < 3 || strlen($location) > 255) {
        return "Location must be between 3 and 255 characters";
    }
    return "";
}

// Validate date function
function validateDate($date) {
    if(empty($date)) {
        return "Date is required";
    }
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    if(!$dateObj || $dateObj->format('Y-m-d') !== $date) {
        return "Please enter a valid date";
    }
    if($dateObj > new DateTime()) {
        return "Date cannot be in the future";
    }
    return "";
}

// Validate photo function
function validatePhoto($photo) {
    if(!isset($photo) || !isset($photo["tmp_name"]) || empty($photo["tmp_name"])) {
        return "Photo is required";
    }

    $check = getimagesize($photo["tmp_name"]);
    if($check === false) {
        return "File is not a valid image";
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if(!in_array($check["mime"], $allowedTypes)) {
        return "Only JPG, JPEG & PNG files are allowed";
    }

    if($photo["size"] > 5000000) {
        return "File size must be less than 5MB";
    }

    return "";
}

// Process Missing Person Form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_missing'])) {
    try {
        $errors = [];
        
        $missingName = validateInput($_POST['missing_name']);
        $nameError = validateName($missingName);
        if($nameError) $errors[] = $nameError;

        $missingDescription = validateInput($_POST['missing_description']);
        $descError = validateDescription($missingDescription);
        if($descError) $errors[] = $descError;

        $missingContact = validateInput($_POST['missing_contact']);
        $contactError = validateContact($missingContact);
        if($contactError) $errors[] = $contactError;

        $missingLocation = validateInput($_POST['missing_location']);
        $locationError = validateLocation($missingLocation);
        if($locationError) $errors[] = $locationError;

        $missingDate = validateInput($_POST['missing_date']);
        $dateError = validateDate($missingDate);
        if($dateError) $errors[] = $dateError;

        $photoError = validatePhoto($_FILES["missing_photo"]);
        if($photoError) $errors[] = $photoError;

        if(!empty($errors)) {
            throw new Exception(implode("<br>", $errors));
        }

        // Handle file upload
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            if(!mkdir($targetDir, 0777, true)) {
                throw new Exception("Failed to create uploads directory");
            }
            chmod($targetDir, 0777);
        }

        $targetFile = $targetDir . time() . "_" . basename($_FILES["missing_photo"]["name"]);

        if (!move_uploaded_file($_FILES["missing_photo"]["tmp_name"], $targetFile)) {
            throw new Exception("Failed to upload file. Please try again.");
        }

        $sql = "INSERT INTO missing_persons (name, description, contact, location, missing_date, photo_url) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if(!$stmt) {
            throw new Exception("Database error. Please try again later.");
        }

        $stmt->bind_param("ssssss", $missingName, $missingDescription, $missingContact, $missingLocation, $missingDate, $targetFile);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to save data. Please try again.");
        }

        $_SESSION['missing_success'] = "Missing person report submitted successfully!";
        header("Location: " . $_SERVER['PHP_SELF'] . "#report-missing-form");
        exit();

        $stmt->close();

    } catch (Exception $e) {
        $_SESSION['missing_error'] = $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF'] . "#report-missing-form");
        exit();
    }
}

// Process Found Person Form  
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_found'])) {
    try {
        $errors = [];
        
        $foundName = validateInput($_POST['found_name']);
        $nameError = validateName($foundName);
        if($nameError) $errors[] = $nameError;

        $foundDescription = validateInput($_POST['found_description']);
        $descError = validateDescription($foundDescription);
        if($descError) $errors[] = $descError;

        $foundContact = validateInput($_POST['found_contact']);
        $contactError = validateContact($foundContact);
        if($contactError) $errors[] = $contactError;

        $foundLocation = validateInput($_POST['found_location']);
        $locationError = validateLocation($foundLocation);
        if($locationError) $errors[] = $locationError;

        $photoError = validatePhoto($_FILES["found_photo"]);
        if($photoError) $errors[] = $photoError;

        if(!empty($errors)) {
            throw new Exception(implode("<br>", $errors));
        }

        // Handle file upload
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            if(!mkdir($targetDir, 0777, true)) {
                throw new Exception("Failed to create uploads directory");
            }
            chmod($targetDir, 0777);
        }

        $targetFile = $targetDir . time() . "_" . basename($_FILES["found_photo"]["name"]);

        if (!move_uploaded_file($_FILES["found_photo"]["tmp_name"], $targetFile)) {
            throw new Exception("Failed to upload file. Please try again.");
        }

        $sql = "INSERT INTO found_persons (name, description, contact, location, photo_url) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if(!$stmt) {
            throw new Exception("Database error. Please try again later.");
        }

        $stmt->bind_param("sssss", $foundName, $foundDescription, $foundContact, $foundLocation, $targetFile);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to save data. Please try again.");
        }

        $_SESSION['found_success'] = "Found person report submitted successfully!";
        header("Location: " . $_SERVER['PHP_SELF'] . "#report-found-form");
        exit();

        $stmt->close();

    } catch (Exception $e) {
        $_SESSION['found_error'] = $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF'] . "#report-found-form");
        exit();
    }
}

// Process Search Form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_search'])) {
    try {
        $searchName = validateInput($_POST['search_name']);
        $nameError = validateName($searchName);
        
        if($nameError) {
            throw new Exception($nameError);
        }

        // Search in missing_persons table
        $sql = "SELECT * FROM missing_persons WHERE name LIKE ?";
        $stmt = $conn->prepare($sql);
        if(!$stmt) {
            throw new Exception("Database error. Please try again later.");
        }

        $searchParam = "%" . $searchName . "%";
        $stmt->bind_param("s", $searchParam);
        
        if (!$stmt->execute()) {
            throw new Exception("Search failed. Please try again.");
        }

        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $row['type'] = 'missing';
                $searchResults[] = $row;
            }
        }
        $stmt->close();

        // Search in found_persons table
        $sql = "SELECT * FROM found_persons WHERE name LIKE ?";
        $stmt = $conn->prepare($sql);
        if(!$stmt) {
            throw new Exception("Database error. Please try again later.");
        }

        $stmt->bind_param("s", $searchParam);
        
        if (!$stmt->execute()) {
            throw new Exception("Search failed. Please try again.");
        }

        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $row['type'] = 'found';
                $searchResults[] = $row;
            }
        }
        $stmt->close();

        if (!empty($searchResults)) {
            $_SESSION['search_results'] = $searchResults;
            $_SESSION['search_success'] = "Found " . count($searchResults) . " results";
        } else {
            $_SESSION['search_error'] = "No results found for: " . $searchName;
        }
        
        header("Location: " . $_SERVER['PHP_SELF'] . "#search-form");
        exit();

    } catch (Exception $e) {
        $_SESSION['search_error'] = $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF'] . "#search-form");
        exit();
    }
}

// Process Contact Form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_contact'])) {
    try {
        // Debug: Log the POST data
        error_log("Contact Form POST data: " . print_r($_POST, true));
        
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

        // Debug: Log the validated data
        error_log("Contact Form validated data: Name: $contactName, Email: $contactEmail, Subject: $contactSubject");

        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if(!$stmt) {
            // Debug: Log the database error
            error_log("Database prepare error: " . $conn->error);
            throw new Exception("Database error. Please try again later.");
        }

        $stmt->bind_param("ssss", $contactName, $contactEmail, $contactSubject, $contactMessage);
        
        if (!$stmt->execute()) {
            // Debug: Log the execution error
            error_log("Database execute error: " . $stmt->error);
            throw new Exception("Failed to save message. Please try again.");
        }

        // Debug: Log successful insertion
        error_log("Contact message successfully inserted into database");

        $_SESSION['contact_success'] = "Your message has been sent successfully!";
        header("Location: " . $_SERVER['PHP_SELF'] . "#contact-form");
        exit();

        $stmt->close();

    } catch (Exception $e) {
        // Debug: Log any caught exceptions
        error_log("Contact form error: " . $e->getMessage());
        $_SESSION['contact_error'] = $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF'] . "#contact-form");
        exit();
    }
}

// Get messages from session
if(isset($_SESSION['missing_success'])) {
    $missingSuccessMessage = $_SESSION['missing_success'];
    unset($_SESSION['missing_success']);
}

if(isset($_SESSION['missing_error'])) {
    $missingErrorMessage = $_SESSION['missing_error'];
    unset($_SESSION['missing_error']);
}

if(isset($_SESSION['found_success'])) {
    $foundSuccessMessage = $_SESSION['found_success'];
    unset($_SESSION['found_success']);
}

if(isset($_SESSION['found_error'])) {
    $foundErrorMessage = $_SESSION['found_error'];
    unset($_SESSION['found_error']);
}

if(isset($_SESSION['search_success'])) {
    $searchSuccessMessage = $_SESSION['search_success'];
    unset($_SESSION['search_success']);
}

if(isset($_SESSION['search_error'])) {
    $searchErrorMessage = $_SESSION['search_error'];
    unset($_SESSION['search_error']);
}

if(isset($_SESSION['search_results'])) {
    $searchResults = $_SESSION['search_results'];
    unset($_SESSION['search_results']);
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
    <title>Find Your Dears - Missing Persons Platform</title>
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
            padding: 80px 20px 20px;
        }

        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #0f0;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 2.5em;
            text-shadow: 0 0 10px #0f0;
            margin-bottom: 15px;
        }

        /* Navigation Bar Styles */
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

        /* Adjust main content to account for fixed navbar */
        .main-content {
            margin-top: 20px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px;
        }

        .action-card {
            background: #1a1a1a;
            border: 2px solid #0f0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: 0.3s;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.2);
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.4);
        }

        .action-card h2 {
            margin-bottom: 15px;
            color: #0f0;
        }

        .action-card p {
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .action-button {
            display: inline-block;
            padding: 10px 20px;
            background: #0f0;
            color: #000;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }

        .action-button:hover {
            box-shadow: 0 0 15px #0f0;
        }

        .form-container {
            display: none;
            background: #1a1a1a;
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #0f0;
            margin: 20px auto;
            max-width: 800px;
        }

        .form-container.active {
            display: block;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #0f0;
            border-radius: 5px;
            background: #333;
            color: #0f0;
        }

        button {
            padding: 10px 20px;
            background: #0f0;
            color: #000;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            box-shadow: 0 0 10px #0f0;
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

        .search-results {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .search-result {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #0f0;
        }

        .search-result img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin: 10px 0;
        }

        .footer {
            text-align: center;
            padding: 20px;
            margin-top: 40px;
            border-top: 2px solid #0f0;
            position: relative;
            bottom: 0;
            width: 100%;
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

        /* Form field hints */
        .field-hint {
            font-size: 0.8em;
            color: #888;
            margin-bottom: 5px;
        }

        /* Required field marker */
        .required::after {
            content: "*";
            color: #ff0000;
            margin-left: 3px;
        }

        /* Form field validation visual feedback */
        input:valid,
        textarea:valid {
            border-color: #00ff00;
        }

        input:invalid,
        textarea:invalid {
            border-color: #ff0000;
        }

        /* Help text */
        .help-text {
            font-size: 0.9em;
            color: #666;
            margin: 5px 0;
        }

        /* User Profile Styles */
        .user-profile {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .profile-card {
            background: #1a1a1a;
            border: 2px solid #0f0;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.2);
        }

        .profile-card h2 {
            color: #0f0;
            margin-bottom: 20px;
            text-align: center;
            text-shadow: 0 0 10px #0f0;
        }

        .profile-info {
            display: grid;
            gap: 15px;
        }

        .profile-info p {
            margin: 0;
            padding: 10px;
            background: #333;
            border-radius: 5px;
            border: 1px solid #0f0;
        }

        .profile-info strong {
            color: #0f0;
            margin-right: 10px;
        }

        .header-description {
            max-width: 800px;
            margin: 20px auto;
            line-height: 1.6;
        }

        .header-description p {
            margin-bottom: 15px;
        }

        .faq-section {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .faq-section h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #0f0;
            text-shadow: 0 0 10px #0f0;
        }

        .faq-container {
            display: grid;
            gap: 20px;
        }

        .faq-item {
            background: #1a1a1a;
            border: 1px solid #0f0;
            border-radius: 10px;
            padding: 20px;
            transition: 0.3s;
            cursor: pointer;
        }

        .faq-item:hover {
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.3);
        }

        .faq-item h3 {
            color: #0f0;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-item h3::after {
            content: '+';
            font-size: 1.5em;
            transition: transform 0.3s;
        }

        .faq-item.active h3::after {
            transform: rotate(45deg);
        }

        .faq-item p {
            line-height: 1.6;
            display: none;
            margin-top: 10px;
        }

        .faq-item.active p {
            display: block;
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
        <h1>Find Your Dears</h1>
        <p>Connecting families, one search at a time</p>
        <div class="header-description">
            <p>Welcome to Find Your Dears - A global platform dedicated to reuniting missing persons with their families. Our mission is to create a safe, efficient, and compassionate space where families can report missing loved ones and concerned citizens can report found individuals.</p>
            <p>Whether you're searching for a missing family member or have found someone who needs help, our platform provides the tools and support you need to make a difference.</p>
        </div>
    </header>

    <?php if(isset($_SESSION['user_id'])): 
        // Fetch user data
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    ?>
    <div class="user-profile">
        <div class="profile-card">
            <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
            <div class="profile-info">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>CNIC:</strong> <?php echo htmlspecialchars($user['cnic']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <main class="main-content">
        <div class="action-card">
            <h2>Report Missing Person</h2>
            <p>If someone you know is missing, report it here. Our platform will help spread awareness and increase the chances of finding them. We'll guide you through the process of submitting all necessary information, including photos and last known details.</p>
            <button onclick="showForm('report-missing-form')" class="action-button">Report Now</button>
        </div>

        <div class="action-card">
            <h2>Search Database</h2>
            <p>Search through our comprehensive database of reported missing persons and found individuals. Our advanced search system helps you find matches quickly and efficiently. You can search by name, location, or other identifying features.</p>
            <button onclick="showForm('search-form')" class="action-button">Search</button>
        </div>

        <div class="action-card">
            <h2>Report Found Person</h2>
            <p>If you've found someone who might be reported missing, report it here. Your information could be crucial in reuniting them with their family. We'll help you provide all necessary details while maintaining privacy and security.</p>
            <button onclick="showForm('report-found-form')" class="action-button">Report Found</button>
        </div>
    </main>

    <div id="report-missing-form" class="form-container">
        <h2>Report Missing Person</h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <div class="form-field">
                <label class="required">Name of Missing Person</label>
                <div class="help-text">Enter the full name as it appears on official documents</div>
                <input type="text" name="missing_name" placeholder="e.g. John Smith" pattern="[a-zA-Z\s'-]+" required>
            </div>

            <div class="form-field">
                <label class="required">Description</label>
                <div class="help-text">Include physical characteristics, clothing worn when last seen, etc.</div>
                <textarea name="missing_description" placeholder="Height, weight, distinguishing features, last seen wearing..." minlength="10" required></textarea>
            </div>

            <div class="form-field">
                <label class="required">Contact Number</label>
                <div class="help-text">Enter a valid contact number with country code if applicable</div>
                <input type="tel" name="missing_contact" placeholder="e.g. +1234567890" pattern="[\+]?[\d\s-]{10,15}" required>
            </div>

            <div class="form-field">
                <label class="required">Last Known Location</label>
                <div class="help-text">Be as specific as possible with the location</div>
                <input type="text" name="missing_location" placeholder="Street address, city, state, country" required>
            </div>

            <div class="form-field">
                <label class="required">Date Last Seen</label>
                <div class="help-text">Select the date when the person was last seen</div>
                <input type="date" name="missing_date" max="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-field">
                <label class="required">Photo</label>
                <div class="help-text">Upload a clear, recent photo (JPG, JPEG, PNG, max 5MB)</div>
                <input type="file" name="missing_photo" accept="image/jpeg,image/png,image/jpg" required>
            </div>

            <button type="submit" name="submit_missing">Submit Report</button>
        </form>
        <?php 
        if(isset($missingSuccessMessage)) echo "<div class='success'>$missingSuccessMessage</div>";
        if(isset($missingErrorMessage)) echo "<div class='error'>$missingErrorMessage</div>";
        ?>
    </div>

    <div id="report-found-form" class="form-container">
        <h2>Report Found Person</h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <div class="form-field">
                <label class="required">Name of Found Person</label>
                <div class="help-text">Enter the name if known, or description if unknown</div>
                <input type="text" name="found_name" placeholder="e.g. John Smith or Unknown Person" pattern="[a-zA-Z\s'-]+" required>
            </div>

            <div class="form-field">
                <label class="required">Description</label>
                <div class="help-text">Include physical characteristics and any identifying features</div>
                <textarea name="found_description" placeholder="Height, weight, distinguishing features, clothing worn..." minlength="10" required></textarea>
            </div>

            <div class="form-field">
                <label class="required">Contact Number</label>
                <div class="help-text">Your contact number for verification</div>
                <input type="tel" name="found_contact" placeholder="e.g. +1234567890" pattern="[\+]?[\d\s-]{10,15}" required>
            </div>

            <div class="form-field">
                <label class="required">Location Found</label>
                <div class="help-text">Specify where the person was found</div>
                <input type="text" name="found_location" placeholder="Street address, city, state, country" required>
            </div>

            <div class="form-field">
                <label class="required">Photo</label>
                <div class="help-text">Upload a clear photo (JPG, JPEG, PNG, max 5MB)</div>
                <input type="file" name="found_photo" accept="image/jpeg,image/png,image/jpg" required>
            </div>

            <button type="submit" name="submit_found">Submit Report</button>
        </form>
        <?php 
        if(isset($foundSuccessMessage)) echo "<div class='success'>$foundSuccessMessage</div>";
        if(isset($foundErrorMessage)) echo "<div class='error'>$foundErrorMessage</div>";
        ?>
    </div>

    <div id="search-form" class="form-container">
        <h2>Search for Person</h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-field">
                <label class="required">Name to Search</label>
                <div class="help-text">Enter full or partial name to search</div>
                <input type="text" name="search_name" placeholder="Enter name to search" pattern="[a-zA-Z\s'-]+" required>
            </div>
            <button type="submit" name="submit_search">Search</button>
        </form>
        <?php 
        if(isset($searchSuccessMessage)) echo "<div class='success'>$searchSuccessMessage</div>";
        if(isset($searchErrorMessage)) echo "<div class='error'>$searchErrorMessage</div>";
        ?>
        <?php if (!empty($searchResults)): ?>
            <div class="search-results">
                <?php foreach($searchResults as $result): ?>
                    <div class="search-result">
                        <h3><?php echo $result['name']; ?></h3>
                        <p><?php echo $result['description']; ?></p>
                        <p>Location: <?php echo $result['location']; ?></p>
                        <p>Contact: <?php echo $result['contact']; ?></p>
                        <?php if(isset($result['missing_date'])): ?>
                            <p>Missing Date: <?php echo $result['missing_date']; ?></p>
                        <?php endif; ?>
                        <p>Status: <?php echo ucfirst($result['type']); ?></p>
                        <img src="<?php echo $result['photo_url']; ?>" alt="Person photo">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="contact-form" class="form-container">
        <h2>Contact Us</h2>
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
        <?php 
        if(isset($contactSuccessMessage)) echo "<div class='success'>$contactSuccessMessage</div>";
        if(isset($contactErrorMessage)) echo "<div class='error'>$contactErrorMessage</div>";
        ?>
    </div>

    <footer class="footer">
        <div class="faq-section">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-container">
                <div class="faq-item">
                    <h3>What is Find Your Dears?</h3>
                    <p>Find Your Dears is a global platform that helps reunite missing persons with their families. We provide a secure and efficient way to report missing persons and found individuals.</p>
                </div>

                <div class="faq-item">
                    <h3>How does this platform work?</h3>
                    <p>Our platform works in three main ways: 1) Families can report missing persons, 2) People can report found individuals, and 3) Anyone can search our database to find matches. All information is verified and handled with strict privacy measures.</p>
                </div>

                <div class="faq-item">
                    <h3>Is my information safe?</h3>
                    <p>Yes, we take privacy and security very seriously. All personal information is encrypted and only shared with relevant authorities when necessary. We follow strict data protection protocols.</p>
                </div>

                <div class="faq-item">
                    <h3>What should I do if I find someone?</h3>
                    <p>If you find someone who might be missing, first ensure their safety. Then, use our "Report Found Person" feature to submit their details. We'll help connect them with their family while maintaining their privacy.</p>
                </div>

                <div class="faq-item">
                    <h3>How can I help?</h3>
                    <p>You can help by: 1) Reporting any missing persons you know about, 2) Sharing information about missing persons on social media, 3) Keeping an eye out for people matching missing person descriptions, and 4) Reporting found individuals.</p>
                </div>

                <div class="faq-item">
                    <h3>Is this service free?</h3>
                    <p>Yes, all our services are completely free. We believe in making this platform accessible to everyone who needs it.</p>
                </div>
            </div>
        </div>

        <p>Together we can make a difference</p>
        <div class="social-links">
            <i class="fab fa-facebook"></i>
            <i class="fab fa-twitter"></i>
            <i class="fab fa-instagram"></i>
            <i class="fab fa-linkedin"></i>
        </div>
    </footer>

    <script>
        function showForm(formId) {
            // Hide all forms first
            document.querySelectorAll('.form-container').forEach(form => {
                form.classList.remove('active');
            });
            // Show the selected form
            document.getElementById(formId).classList.add('active');
            // Scroll to the form
            document.getElementById(formId).scrollIntoView({ behavior: 'smooth' });
        }

        // Auto-show form if there's an error or success message
        window.onload = function() {
            <?php if(isset($missingErrorMessage) || isset($missingSuccessMessage)): ?>
                showForm('report-missing-form');
            <?php endif; ?>
            <?php if(isset($foundErrorMessage) || isset($foundSuccessMessage)): ?>
                showForm('report-found-form');
            <?php endif; ?>
            <?php if(isset($searchErrorMessage) || isset($searchSuccessMessage)): ?>
                showForm('search-form');
            <?php endif; ?>
            <?php if(isset($contactErrorMessage) || isset($contactSuccessMessage)): ?>
                showForm('contact-form');
            <?php endif; ?>

            // Add click event listeners to FAQ items
            document.querySelectorAll('.faq-item').forEach(item => {
                item.addEventListener('click', function() {
                    this.classList.toggle('active');
                });
            });
        }
    </script>
</body>
</html>
