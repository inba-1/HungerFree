<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include("connect.php");

// Check if admin is logged in
if (!isset($_SESSION['Aid'])) {
    die("Error: Admin ID (Aid) is missing. Please log in again.");
}

// Store Admin ID
$id = $_SESSION['Aid'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <title>Admin Profile</title>
</head>
<body>
    <nav>
        <div class="logo-name">
            <span class="logo_name">ADMIN</span>
        </div>

        <div class="menu-items">
            <ul class="nav-links">
                <li><a href="admin.php"><i class="uil uil-estate"></i><span class="link-name">Dashboard</span></a></li>
                <li><a href="analytics.php"><i class="uil uil-chart"></i><span class="link-name">Analytics</span></a></li>
                <li><a href="donate.php"><i class="uil uil-heart"></i><span class="link-name">Donates</span></a></li>
                <li><a href="feedback.php"><i class="uil uil-comments"></i><span class="link-name">Feedbacks</span></a></li>
                <li><a href="#"><i class="uil uil-user"></i><span class="link-name">Profile</span></a></li>
            </ul>
            
            <ul class="logout-mode">
                <li><a href="../logout.php"><i class="uil uil-signout"></i><span class="link-name">Logout</span></a></li>
                <li class="mode">
                    <a href="#"><i class="uil uil-moon"></i><span class="link-name">Dark Mode</span></a>
                    <div class="mode-toggle"><span class="switch"></span></div>
                </li>
            </ul>
        </div>
    </nav>

    <section class="dashboard">
        <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>
            <p class="logo" style="text-align: center; font-size: 24px; font-weight: bold;">
              Your <b style="color: #06C167;">History</b>
</p>

        </div>

        <br><br><br>

        <div class="activity">
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Food</th>
                                <th>Category</th>
                                <th>Phone No</th>
                                <th>Date/Time</th>
                                <th>Address</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Query to get assigned donations for this admin
                            $sql = "SELECT * FROM food_donations WHERE assigned_to = $id";
                            $result = mysqli_query($connection, $sql);

                            // Error handling
                            if (!$result) {
                                die("Error executing query: " . mysqli_error($connection));
                            }

                            // Display fetched data
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>
                                    <td data-label='name'>{$row['name']}</td>
                                    <td data-label='food'>{$row['food']}</td>
                                    <td data-label='category'>{$row['category']}</td>
                                    <td data-label='phoneno'>{$row['phoneno']}</td>
                                    <td data-label='date'>{$row['date']}</td>
                                    <td data-label='Address'>{$row['address']}</td>
                                    <td data-label='quantity'>{$row['quantity']}</td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>   
        </div>
    </section>

    <script src="admin.js"></script>
</body>
</html>
