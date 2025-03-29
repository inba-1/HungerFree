<?php
// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "my_project";
$port = 3306;

// Establish connection
$connection = mysqli_connect($host, $user, $pass, $dbname, $port);

// Check connection
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

$msg = 0;

// Check if login form is submitted
if (isset($_POST['sign'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Sanitize user input to prevent SQL Injection
    $sanitized_emailid = mysqli_real_escape_string($connection, $email);
    $sanitized_password = mysqli_real_escape_string($connection, $password);

    // Query to check admin credentials
    $sql = "SELECT * FROM admin WHERE email='$sanitized_emailid'";
    $result = mysqli_query($connection, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        // Verify hashed password
        if (password_verify($sanitized_password, $row['password'])) {
            // Store user details in session
            $_SESSION['email'] = $row['email'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['location'] = $row['location'];
            $_SESSION['Aid'] = $row['Aid']; // Store Admin ID

            // Redirect to admin dashboard
            header("Location: admin.php");
            exit();
        } else {
            $msg = 1; // Incorrect password
        }
    } else {
        echo "<h1><center>Account does not exist</center></h1>";
    }
}
?>
