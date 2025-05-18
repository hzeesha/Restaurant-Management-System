# this file is to modify any menu items
<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "cs3319";
$dbname = "assign2db";

$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

$message = "";
$errors = [];

if (isset($_POST['updateItem'])) {
    $menuitemid = trim($_POST['menuitemid'] ?? '');
    $newPrice = trim($_POST['price'] ?? '');
    $newCalories = trim($_POST['caloriecount'] ?? '');

    if ($menuitemid === '') {
        $errors[] = "No menu item selected.";
    }
    if (!is_numeric($newPrice) || $newPrice < 0) {
        $errors[] = "Price must be a positive number.";
    }
    if (!ctype_digit($newCalories) || (int)$newCalories < 0) {
        $errors[] = "Calorie count must be a non-negative integer.";
    }

    if (empty($errors)) {
        $menuitemidEsc = mysqli_real_escape_string($connection, $menuitemid);
        $priceEsc      = mysqli_real_escape_string($connection, $newPrice);
        $caloriesEsc   = mysqli_real_escape_string($connection, $newCalories);

        $sqlUpdate = "
            UPDATE menuitem
            SET price = '$priceEsc', caloriecount = '$caloriesEsc'
            WHERE menuitemid = '$menuitemidEsc'
        ";
        $result = $connection->query($sqlUpdate);
        if ($result) {
            if ($connection->affected_rows > 0) {
                $message = "Menu item updated successfully.";
            } else {
                $message = "No changes made (item not found or data was the same).";
            }
        } else {
            $errors[] = "Error updating menu item: " . $connection->error;
        }
    }
}

$sqlMenuItems = "SELECT menuitemid, dishname, price, caloriecount FROM menuitem ORDER BY dishname";
$resultMenuItems = $connection->query($sqlMenuItems);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Modify Menu Item</title>
    <link rel="stylesheet" href="mainStyle.css?v=1.2">
</head>
<body>
<div class="container">
    <h1>Modify an Existing Menu Item</h1>

    <?php
    if (!empty($errors)) {
        echo '<ul style="color:red;">';
        foreach ($errors as $err) {
            echo "<li>$err</li>";
        }
        echo '</ul>';
    }

    if ($message !== "") {
        echo "<p style='color:green;'>$message</p>";
    }
    ?>

    <form method="POST">
        <p>
            <label for="menuitemid">Select a Menu Item:</label><br>
            <select name="menuitemid" id="menuitemid" required>
                <option value="">--Choose an Item--</option>
                <?php
                if ($resultMenuItems && $resultMenuItems->num_rows > 0) {
                    while ($row = $resultMenuItems->fetch_assoc()) {
                        $id   = htmlspecialchars($row['menuitemid']);
                        $name = htmlspecialchars($row['dishname']);
                        echo "<option value='$id'>$name</option>";
                    }
                }
                ?>
            </select>
        </p>
        <p>
            <label for="price">New Price:</label><br>
            <input type="number" step="0.01" name="price" id="price" required>
        </p>
        <p>
            <label for="caloriecount">New Calorie Count:</label><br>
            <input type="number" name="caloriecount" id="caloriecount" required>
        </p>
        <p><button type="submit" name="updateItem">Update Item</button></p>
    </form>
</div>
</body>
</html>
<?php
$connection->close();
?>

