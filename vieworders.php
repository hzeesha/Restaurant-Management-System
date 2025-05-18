# File to view orders from the database
<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "cs3319";
$dbname = "assign2db";

$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

$selectedOrder = $_POST['orderid'] ?? '';
$orderDetails = [];
$orderHeader = null;

if (isset($_POST['viewOrder'])) {
    $selectedOrderEsc = mysqli_real_escape_string($connection, $selectedOrder);
    $sql = "
        SELECT 
            c.orderid,
            c.dateplaced,
            c.timeplaced,
            c.timedelivered,
            c.deliveryrating,
            d.firstname AS dfname,
            d.lastname AS dlname,
            cu.firstname AS cfname,
            cu.lastname AS clname,
            m.dishname,
            m.price,
            o.quantity
        FROM cusorder c
        JOIN driver d ON c.driverid = d.driverid
        JOIN customer cu ON c.cusid = cu.cusid
        JOIN overallorder o ON c.orderid = o.orderid
        JOIN menuitem m ON o.menuitemid = m.menuitemid
        WHERE c.orderid = '$selectedOrderEsc'
    ";
    $result = $connection->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (!$orderHeader) {
                $orderHeader = [
                    'orderid'        => $row['orderid'],
                    'dateplaced'     => $row['dateplaced'],
                    'timeplaced'     => $row['timeplaced'],
                    'timedelivered'  => $row['timedelivered'],
                    'deliveryrating' => $row['deliveryrating'],
                    'driverName'     => $row['dfname'] . " " . $row['dlname'],
                    'customerName'   => $row['cfname'] . " " . $row['clname']
                ];
            }
            $orderDetails[] = [
                'dishname' => $row['dishname'],
                'price'    => (float)$row['price'],
                'quantity' => (int)$row['quantity']
            ];
        }
    }
}

$sqlOrders = "SELECT orderid FROM cusorder ORDER BY orderid";
$ordersResult = $connection->query($sqlOrders);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>View Order Details</title>
    <link rel="stylesheet" href="mainStyle.css?v=1.2">
</head>
<body>
<div class="container">
    <h1>View Order Details</h1>
    <form method="POST">
        <p>
            <label for="orderid">Select an Order ID:</label><br>
            <select name="orderid" id="orderid" required>
                <option value="">--Choose an Order--</option>
                <?php
                if ($ordersResult && $ordersResult->num_rows > 0) {
                    while ($row = $ordersResult->fetch_assoc()) {
                        $oid = htmlspecialchars($row['orderid']);
                        $sel = ($oid === $selectedOrder) ? "selected" : "";
                        echo "<option value='$oid' $sel>$oid</option>";
                    }
                }
                ?>
            </select>
        </p>
        <p><button type="submit" name="viewOrder">View Order</button></p>
    </form>
    <?php
    if ($selectedOrder && $orderHeader) {
        echo "<h2>Order #{$orderHeader['orderid']}</h2>";
        echo "<p><strong>Date Placed:</strong> {$orderHeader['dateplaced']}</p>";
        echo "<p><strong>Time Placed:</strong> {$orderHeader['timeplaced']}</p>";
        echo "<p><strong>Time Delivered:</strong> {$orderHeader['timedelivered']}</p>";
        echo "<p><strong>Delivery Rating:</strong> " . ($orderHeader['deliveryrating'] ?? 'N/A') . "</p>";
        echo "<p><strong>Driver:</strong> " . htmlspecialchars($orderHeader['driverName']) . "</p>";
        echo "<p><strong>Customer:</strong> " . htmlspecialchars($orderHeader['customerName']) . "</p>";
        echo "<h3>Menu Items</h3>";
        if (!empty($orderDetails)) {
            $total = 0;
            echo "<ul>";
            foreach ($orderDetails as $item) {
                $dish = htmlspecialchars($item['dishname']);
                $price = $item['price'];
                $qty = $item['quantity'];
                $sub = $price * $qty;
                $total += $sub;
                echo "<li>$dish @ \$$price x $qty = \$" . number_format($sub, 2) . "</li>";
            }
            echo "</ul>";
            echo "<p><strong>Total Price: \$" . number_format($total, 2) . "</strong></p>";
        }
    } elseif ($selectedOrder) {
        echo "<p style='color:red;'>No details found for Order #$selectedOrder.</p>";
    }
    ?>
</div>
</body>
</html>
<?php
$connection->close();
?>