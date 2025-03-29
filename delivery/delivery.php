<?php
session_start();
ob_start();

// Include database connection
include("../connection.php");

// Check if the user is logged in
if (!isset($_SESSION['name']) || empty($_SESSION['name'])) {
    header("location: deliverylogin.php");
    exit();
}

// Set session variables with default values to prevent "undefined index" errors
$name = $_SESSION['name'] ?? 'Guest';
$city = $_SESSION['city'] ?? 'Unknown';
$id = $_SESSION['Did'] ?? null;

// Initialize cURL to fetch location details (Optional)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://ip-api.com/json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($ch);
$result = json_decode($result);
curl_close($ch);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Dashboard</title>
    <link rel="stylesheet" href="../home.css">
    <link rel="stylesheet" href="delivery.css">
</head>
<body>

<header>
    <div class="logo">Hunger <b style="color: #06C167;">Free</b></div>
    <div class="hamburger">
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
    </div>
    <nav class="nav-bar">
        <ul>
            <li><a href="#home" class="active">Home</a></li>
            <li><a href="openmap.php">Map</a></li>
            <li><a href="deliverymyord.php">My Orders</a></li>
        </ul>
    </nav>
</header>

<script>
    let hamburger = document.querySelector(".hamburger");
    hamburger.onclick = function () {
        let navBar = document.querySelector(".nav-bar");
        navBar.classList.toggle("active");
    };
</script>

<h2><center>Welcome <?php echo htmlspecialchars($name); ?></center></h2>

<div style="display: flex; flex-direction: column; justify-content: center; align-items: center;  text-align: center;">
    <div style="margin-bottom: 20px;">
        <img src="../img/delivery.gif" alt="Delivery Animation" width="400" height="400">
    </div>

    <div>
        <a href="deliverymyord.php" style="text-decoration: none; background-color: #06C167; color: white; padding: 12px 24px; border-radius: 8px; font-size: 18px; font-weight: bold; transition: 0.3s; display: inline-block;">
            My Orders
        </a>
    </div>
</div>

<?php
// Define SQL query to fetch unassigned orders for the delivery person's city
$sql = "SELECT fd.Fid AS Fid, fd.location AS cure, fd.name, fd.phoneno, fd.date, fd.delivery_by, 
               fd.address AS From_address, ad.name AS delivery_person_name, ad.address AS To_address
        FROM food_donations fd
        LEFT JOIN admin ad ON fd.assigned_to = ad.Aid 
        WHERE fd.assigned_to IS NOT NULL 
          AND fd.delivery_by IS NULL 
          AND fd.location = ?";

// Prepare statement to prevent SQL injection
$stmt = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($stmt, "s", $city);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fetch data
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}
mysqli_stmt_close($stmt);

// Handle order assignment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['food']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    // Check if the order is already assigned
    $check_sql = "SELECT * FROM food_donations WHERE Fid = ? AND delivery_by IS NOT NULL";
    $check_stmt = mysqli_prepare($connection, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $order_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        echo "<p style='color: red; text-align: center;'>Sorry, this order has already been assigned to someone else.</p>";
    } else {
        // Assign order
        $assign_sql = "UPDATE food_donations SET delivery_by = ? WHERE Fid = ?";
        $assign_stmt = mysqli_prepare($connection, $assign_sql);
        mysqli_stmt_bind_param($assign_stmt, "ii", $id, $order_id);
        $assign_result = mysqli_stmt_execute($assign_stmt);

        if ($assign_result) {
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            echo "<p style='color: red; text-align: center;'>Error assigning order: " . mysqli_error($connection) . "</p>";
        }

        mysqli_stmt_close($assign_stmt);
    }
    mysqli_stmt_close($check_stmt);
}

mysqli_close($connection);
?>

<!-- Display the orders in an HTML table -->
<div class="table-container">
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone No</th>
                    <th>Date/Time</th>
                    <th>Pickup Address</th>
                    <th>Delivery Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row) { ?>
                    <tr>
                        <td data-label="Name"><?php echo htmlspecialchars($row['name']); ?></td>
                        <td data-label="Phone No"><?php echo htmlspecialchars($row['phoneno']); ?></td>
                        <td data-label="Date/Time"><?php echo htmlspecialchars($row['date']); ?></td>
                        <td data-label="Pickup Address"><?php echo htmlspecialchars($row['From_address']); ?></td>
                        <td data-label="Delivery Address"><?php echo htmlspecialchars($row['To_address']); ?></td>
                        <td data-label="Action">
                            <?php if ($row['delivery_by'] == null) { ?>
                                <form method="post">
                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['Fid']); ?>">
                                    <button type="submit" name="food">Take Order</button>
                                </form>
                            <?php } elseif ($row['delivery_by'] == $id) { ?>
                                Order assigned to you
                            <?php } else { ?>
                                Order assigned to another delivery person
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
