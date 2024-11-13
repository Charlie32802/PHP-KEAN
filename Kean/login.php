<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the timezone to your local timezone (replace 'Asia/Manila' with your timezone if needed)
date_default_timezone_set('Asia/Manila');  // Set the timezone to Asia/Manila

// Database configuration
$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "kean_database";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve login credentials from POST request
$user = $_POST['username'];
$pass = $_POST['password'];

// Prepare SQL query to check if the user exists in the database
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

// Check if a user was found
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Verify the password using password_verify
    if (password_verify($pass, $row['password'])) {
        // Password matches, login successful
        $_SESSION['user_id'] = $row['id'];  // Store user id in session
        echo "success";  // Send success response to JavaScript

        // Log user activity - "Logged in"
        $user_id = $_SESSION['user_id'];  // Get the user ID from session
        $username = $row['username'];  // Get the username
        $activity = $username . " logged in";  // Include the username in the activity message

        // Get current timestamp in 12-hour format 'Y-m-d h:i A' (12-hour format with AM/PM)
        $timestamp = date("Y-m-d H:i:s");  // Use 'H:i:s' for 24-hour format, to match DATETIME format

        // Insert the activity into the user_activity table with timestamp
        $activity_sql = "INSERT INTO user_activity (user_id, activity, timestamp) VALUES (?, ?, ?)";
        $activity_stmt = $conn->prepare($activity_sql);
        $activity_stmt->bind_param("iss", $user_id, $activity, $timestamp);
        $activity_stmt->execute();
        $activity_stmt->close();

    } else {
        // Password does not match
        echo "error";
    }
} else {
    // Username does not exist
    echo "error";
}

// Close the database connection
$stmt->close();
$conn->close();
?>
