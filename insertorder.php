# This file is to insert a anynew orders
<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "cs3319";
$dbname = "assign2db";

$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

$errors = [];
$newlyInsertedOrder = null;

if (isset($_POST['submitOrder'])) {
    $orderid        = trim($_POST['orderid'] ?? '');
    $deladdress     = trim($_POST['deladdress'] ?? '');
    $dateplaced     = trim($_POST['dateplaced'] ?? '');
    $timeplaced     = trim($_POST['timeplaced'] ?? '');
    $timedelivered  = trim($_POST['timedelivered'] ?? '');
    $pickuporder    = trim($_POST['pickuporder'] ?? 'N');
    $driverid       = trim($_POST['driverid'] ?? '');
    $cusid          = trim($_POST['cusid'] ?? '');

    $orderidSafe       = mysqli_real_escape_string($connection, $orderid);
    $deladdressSafe    = mysqli_real_escape_string($connection, $deladdress);
    $dateplacedSafe    = mysqli_real_escape_string($connection, $dateplaced);
    $timeplacedSafe    = mysqli_real_escape_string($connection, $timeplaced);
    $timedeliveredSafe = mysqli_real_escape_string($connection, $timedelivered);
    $pickuporderSafe   = mysqli_real_escape_string($connection, $pickuporder);
    $driveridSafe      = mysqli_real_escape_string($connection, $driverid);
    $cusidSafe         = mysqli_real_escape_string($connection, $cusid);

    $checkSQL = "SELECT orderid FROM cusorder WHERE orderid='$orderidSafe' LIMIT 1;";
    $checkResult = $connection->query($checkSQL);
    if ($checkResult && $checkResult->num_rows > 0) {
        $errors[] = "Order ID '$orderidSafe' already exists. Please choose a different one.";
    }

    $placedDT = strtotime("$dateplacedSafe $timeplacedSafe");
    $delivDT  = strtotime("$dateplacedSafe $timedeliveredSafe");
    if (!$placedDT || !$delivDT) {
        $errors[] = "Invalid date/time format. Please use valid date and time.";
    } else {
        if ($placedDT >= $delivDT) {
            $errors[] = "Time placed must be strictly before time delivered.";
        }
    }

    $selectedItems = $_POST['menuItems'] ?? [];
    $itemsToInsert = [];
    foreach ($selectedItems as $menuitemid => $qty) {
        $qtyClean = (int)$qty;
        if ($qtyClean > 0) {
            $itemsToInsert[$menuitemid] = $qtyClean;
        }
    }
    if (empty($itemsToInsert)) {
        $errors[] = "Please select at least one menu item with quantity > 0.";
    }

    if (empty($errors)) {
        $insertSQL = "
            INSERT INTO cusorder 
                (orderid, deladdress, dateplaced, timeplaced, timedelivered, pickuporder, driverid, cusid)
            VALUES 
                (
                    '$orderidSafe',
                    '$deladdressSafe',
                    '$dateplacedSafe',
                    '$timeplacedSafe',
                    '$timedeliveredSafe',
                    '$pickuporderSafe',
                    '$driveridSafe',
                    '$cusidSafe'
                )
        ";
        $resultInsert = $connection->query($insertSQL);
        if (!$resultInsert) {
            $errors[] = "Error inserting new order: " . $connection->error;
        } else {
            foreach ($itemsToInsert as $menuitemid => $quantity) {
                $menuitemidSafe = mysqli_real_escape_string($connection, $menuitemid); 
                $qtySafe        = (int)$quantity;
                $detailSQL = "
                    INSERT INTO overallorder (orderid, menuitemid, quantity)
                    VALUES ('$orderidSafe', '$menuitemidSafe', $qtySafe)
                ";
                $resultDetail = $connection->query($detailSQL);
                if (!$resultDetail) {
                    $errors[] = "Error inserting menuitem #$menuitemidSafe: " . $connection->error;
                }
            }
        }
        if (empty($errors)) {
            $newlyInsertedOrder = $orderidSafe;
        }
    }
}

$sqlDrivers = "
    SELECT driverid, firstname, lastname 
    FROM driver
    ORDER BY lastname, firstname
";
$resultDrivers = $connection->query($sqlDrivers);

$sqlCustomers = "
    SELECT cusid, firstname, lastname 
    FROM customer
    ORDER BY lastname, firstname
";
$resultCustomers = $connection->query($sqlCustomers);

$sqlMenuItems = "
    SELECT menuitemid, dishname, price 
    FROM menuitem
    ORDER BY dishname
";
$resultMenuItems = $connection->query($sqlMenuItems);
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Restaurant Order System</title>
  <link rel="stylesheet" href="mainStyle.css?v=1.2">
</head>
<body>
  <div class="container">
    <h1>Insert a New Order</h1>
    <?php
    if (!empty($errors)) {
        echo '<ul style="color:red;">';
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo '</ul>';
    }
    ?>
    <?php if (!$newlyInsertedOrder): ?>
    <form method="POST">
      <p>
        <label for="orderid">Order ID (char(4), must be unique):</label><br>
        <input type="text" name="orderid" id="orderid" maxlength="4" required>
      </p>
      <p>
        <label for="deladdress">Delivery Address (optional):</label><br>
        <input type="text" name="deladdress" id="deladdress" maxlength="20">
      </p>
      <p>
        <label for="dateplaced">Date Placed (YYYY-MM-DD):</label><br>
        <input type="date" name="dateplaced" id="dateplaced" required>
      </p>
      <p>
        <label for="timeplaced">Time Placed (HH:MM:SS):</label><br>
        <input type="time" name="timeplaced" id="timeplaced" required>
      </p>
      <p>
        <label for="timedelivered">Time Delivered (HH:MM:SS):</label><br>
        <input type="time" name="timedelivered" id="timedelivered" required>
      </p>
      <p>
        <label>Pickup Order?</label><br>
        <input type="radio" name="pickuporder" value="Y" id="pickupY"><label for="pickupY">Yes (Pickup)</label><br>
        <input type="radio" name="pickuporder" value="N" id="pickupN" checked><label for="pickupN">No (Delivery)</label>
      </p>
      <p>
        <label for="driverid">Select Driver:</label><br>
        <select name="driverid" id="driverid" required>
          <option value="">--Choose a Driver--</option>
          <?php
          if ($resultDrivers && $resultDrivers->num_rows > 0) {
              while ($row = $resultDrivers->fetch_assoc()) {
                  $did   = htmlspecialchars($row['driverid']);
                  $dname = htmlspecialchars($row['firstname'] . " " . $row['lastname']);
                  echo "<option value='$did'>$dname</option>";
              }
          }
          ?>
        </select>
      </p>
      <p>
        <label for="cusid">Select Customer:</label><br>
        <select name="cusid" id="cusid" required>
          <option value="">--Choose a Customer--</option>
          <?php
          if ($resultCustomers && $resultCustomers->num_rows > 0) {
              while ($row = $resultCustomers->fetch_assoc()) {
                  $cid   = htmlspecialchars($row['cusid']);
                  $cname = htmlspecialchars($row['firstname'] . " " . $row['lastname']);
                  echo "<option value='$cid'>$cname</option>";
              }
          }
          ?>
        </select>
      </p>
      <h2>Select Menu Items</h2>
      <p>Enter a quantity for each item you want (0 or blank = none).</p>
      <ul>
        <?php
        if ($resultMenuItems && $resultMenuItems->num_rows > 0) {
            while ($row = $resultMenuItems->fetch_assoc()) {
                $mid      = htmlspecialchars($row['menuitemid']);
                $dishname = htmlspecialchars($row['dishname']);
                $price    = htmlspecialchars($row['price']);
                echo "<li>
                        <strong>$dishname</strong> (\$$price)
                        <input type='number' name='menuItems[$mid]' value='0' min='0' style='width:60px;'>
                      </li>";
            }
        } else {
            echo "<li>No menu items found.</li>";
        }
        ?>
      </ul>
      <p><button type="submit" name="submitOrder">Submit Order</button></p>
    </form>
    <?php endif; ?>
    <?php
    if ($newlyInsertedOrder) {
        echo "<h2>New Order #$newlyInsertedOrder Created!</h2>";
        $displaySQL = "
        SELECT
          c.orderid,
          c.deladdress,
          c.dateplaced,
          c.timeplaced,
          c.timedelivered,
          c.pickuporder,
          d.firstname AS dfname,
          d.lastname  AS dlname,
          cust.firstname AS cfname,
          cust.lastname  AS clname,
          m.dishname,
          m.price,
          o.quantity
        FROM cusorder c
        JOIN driver d ON c.driverid = d.driverid
        JOIN customer cust ON c.cusid = cust.cusid
        JOIN overallorder o ON c.orderid = o.orderid
        JOIN menuitem m ON o.menuitemid = m.menuitemid
        WHERE c.orderid = '$newlyInsertedOrder'
        ";
        $displayResult = $connection->query($displaySQL);
        $orderHeader = null;
        $lineItems = [];
        $totalPrice = 0.00;
        if ($displayResult && $displayResult->num_rows > 0) {
            while ($row = $displayResult->fetch_assoc()) {
                if (!$orderHeader) {
                    $orderHeader = [
                        'deladdress'     => $row['deladdress'],
                        'dateplaced'     => $row['dateplaced'],
                        'timeplaced'     => $row['timeplaced'],
                        'timedelivered'  => $row['timedelivered'],
                        'pickuporder'    => $row['pickuporder'],
                        'driverName'     => $row['dfname'] . " " . $row['dlname'],
                        'customerName'   => $row['cfname'] . " " . $row['clname']
                    ];
                }
                $dish   = $row['dishname'];
                $price  = (float)$row['price'];
                $qty    = (int)$row['quantity'];
                $subtot = $price * $qty;
                $lineItems[] = [
                    'dishname' => $dish,
                    'price'    => $price,
                    'quantity' => $qty,
                    'subtotal' => $subtot
                ];
                $totalPrice += $subtot;
            }
        }
        if ($orderHeader) {
            echo "<p><strong>Delivery Address:</strong> " . htmlspecialchars($orderHeader['deladdress'] ?? '') . "</p>";
            echo "<p><strong>Date Placed:</strong> " . $orderHeader['dateplaced'] . "</p>";
            echo "<p><strong>Time Placed:</strong> " . $orderHeader['timeplaced'] . "</p>";
            echo "<p><strong>Time Delivered:</strong> " . $orderHeader['timedelivered'] . "</p>";
            echo "<p><strong>Pickup Order?</strong> " . ($orderHeader['pickuporder'] === 'Y' ? 'Yes' : 'No') . "</p>";
            echo "<p><strong>Driver:</strong> " . htmlspecialchars($orderHeader['driverName']) . "</p>";
            echo "<p><strong>Customer:</strong> " . htmlspecialchars($orderHeader['customerName']) . "</p>";
            echo "<h3>Order Items</h3>";
            echo "<ul>";
            foreach ($lineItems as $li) {
                echo "<li>{$li['dishname']} @ \${$li['price']} x {$li['quantity']} = \$" . number_format($li['subtotal'], 2) . "</li>";
            }
            echo "</ul>";
            echo "<p><strong>Total Price: \$" . number_format($totalPrice, 2) . "</strong></p>";
        } else {
            echo "<p style='color:red;'>Could not retrieve details for order #$newlyInsertedOrder.</p>";
        }
    }
    ?>
  </div>
</body>
</html>
<?php
$connection->close();
?>

