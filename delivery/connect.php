<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../connection.php';

$msg = 0;
if (isset($_POST['sign'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $sanitized_emailid = mysqli_real_escape_string($connection, $email);
    $sanitized_password = mysqli_real_escape_string($connection, $password);

    // First, check if the user is an admin
    $sql_admin = "SELECT * FROM admin WHERE email='$sanitized_emailid'";
    $result_admin = mysqli_query($connection, $sql_admin);
    $num_admin = mysqli_num_rows($result_admin);

    if ($num_admin == 1) {
        while ($row = mysqli_fetch_assoc($result_admin)) {
            if (password_verify($sanitized_password, $row['password'])) {
                $_SESSION['email'] = $email;
                $_SESSION['name'] = $row['name'];
                $_SESSION['location'] = $row['location'];
                $_SESSION['Aid'] = $row['Aid'];
                header("location:admin.php");
                exit();
            } else {
                $msg = 1;
            }
        }
    } else {
        // If not an admin, check if the user is a delivery person
        $sql_delivery = "SELECT * FROM delivery_persons WHERE email='$sanitized_emailid'";
        $result_delivery = mysqli_query($connection, $sql_delivery);
        $num_delivery = mysqli_num_rows($result_delivery);

        if ($num_delivery == 1) {
            while ($row = mysqli_fetch_assoc($result_delivery)) {
                if (password_verify($sanitized_password, $row['password'])) {
                    $_SESSION['email'] = $email;
                    $_SESSION['name'] = $row['name'];
                    $_SESSION['Did'] = $row['Did']; // Ensure Delivery ID is set
                    header("location:delivery.php");
                    exit();
                } else {
                    $msg = 1;
                }
            }
        } else {
            echo "<h1><center>Account does not exist</center></h1>";
        }
    }
}
?>
