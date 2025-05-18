# this file is to help delete a menu item from the menu item table
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
$message = "";
$step = 1;
$chosenMenuItem = $_POST['menuitemid'] ?? "";

if (isset($_POST['confirmDelete'])) {
    $chosenMenuItemEsc = mysqli_real_escape_string($connection, $chosenMenuItem);

    // Check if the menu item actually exists
    $checkExistsSQL = "SELECT dishname FROM menuitem WHERE menuitemid='$chosenMenuItemEsc'";
    $existsResult = $connection->query($checkExistsSQL);
    if (!$existsResult || $existsResult->num_rows === 0) {
        $errors[] = "Menu item '$chosenMenuItem' does not exist.";
    } else {
        // 1) Delete from overallorder first
        $deleteOverallSQL = "DELETE FROM overallorder WHERE menuitemid='$chosenMenuItemEsc'";
        $deleteOverallResult = $connection->query($deleteOverallSQL);
        if (!$deleteOverallResult) {
            $errors[] = "Error deleting references in overallorder: " . $connection->error;
        } else {
            // 2) Delete from menuitem
            $deleteMenuSQL = "DELETE FROM menuitem WHERE menuitemid='$chosenMenuItemEsc' LIMIT 1";
            $deleteMenuResult = $connection->query($deleteMenuSQL);
            if ($deleteMenuResult) {
                if ($connection->affected_rows > 0) {
                    $message = "Menu item '$chosenMenuItem' and all references in overallorder deleted successfully.";
                } else {
                    $errors[] = "Could not delete item '$chosenMenuItem' from menuitem (already removed?).";
                }
            } else {
                $errors[] = "Error deleting from menuitem: " . $connection->error;
            }
        }
    }
    $step = 1;
} elseif (isset($_POST['pickItem'])) {
    if ($chosenMenuItem === "") {
        $errors[] = "No menu item selected.";
        $step = 1;
    } else {
        $step = 2;
    }
}

$sqlItems = "SELECT menuitemid, dishname FROM menuitem ORDER BY dishname";
$resultItems = $connection->query($sqlItems);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delete a Menu Item</title>
    <link rel="stylesheet" href="mainStyle.css?v=1.2">
</head>
<body>
<div class="container">
    <h1>Delete a Menu Item</h1>
    <?php
    if (!empty($errors)) {
        echo '<ul style="color:red;">';
        foreach ($errors as $e) {
            echo "<li>$e</li>";
        }
        echo '</ul>';
    }
    if ($message !== "") {
        echo "<p style='color:green;'>$message</p>";
    }
    if ($step === 1):
    ?>
    <form method="POST">
        <p>
            <label for="menuitemid">Select a Menu Item to Delete:</label><br>
            <select name="menuitemid" id="menuitemid">
                <option value="">--Choose an Item--</option>
                <?php
                if ($resultItems && $resultItems->num_rows > 0) {
                    while ($row = $resultItems->fetch_assoc()) {
                        $id   = htmlspecialchars($row['menuitemid']);
                        $dish = htmlspecialchars($row['dishname']);
                        $sel  = ($id === $chosenMenuItem) ? "selected" : "";
                        echo "<option value='$id' $sel>$id - $dish</option>";
                    }
                }
                ?>
            </select>
        </p>
        <p><button type="submit" name="pickItem">Proceed</button></p>
    </form>
    <?php
    elseif ($step === 2):
        $chosenSafe = htmlspecialchars($chosenMenuItem);
        $lookup = "SELECT dishname FROM menuitem WHERE menuitemid='$chosenSafe'";
        $res = $connection->query($lookup);
        $dishName = "";
        if ($res && $res->num_rows > 0) {
            $r = $res->fetch_assoc();
            $dishName = htmlspecialchars($r['dishname']);
        }
    ?>
    <p>Are you sure you want to delete this menu item from the system? This will also remove all references to it in orders.</p>
    <ul>
        <li><strong>ID:</strong> <?php echo $chosenSafe; ?></li>
        <li><strong>Dish Name:</strong> <?php echo $dishName; ?></li>
    </ul>
    <form method="POST">
        <input type="hidden" name="menuitemid" value="<?php echo $chosenSafe; ?>">
        <p>
            <button type="submit" name="confirmDelete">Yes, Delete</button>
            <button type="submit" name="cancel">Cancel</button>
        </p>
    </form>
    <?php
    endif;
    ?>
</div>
</body>
</html>
<?php
$connection->close();
?>
