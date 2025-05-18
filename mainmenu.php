<!-- mainmenu.php -->
# Welcome to the main menu ! This is the starting point.
<?php
// No PHP logic needed for now
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restaurant Order System</title>
  <link rel="stylesheet" href="mainStyle.css">
</head>
<body>
  <div class="container">
    <h1>Welcome to the Restaurant Order System!</h1>

    <h3>Check out our menu!</h3> 
    <button onclick="window.location.href='viewmenu.php'">View Menu</button>

    <h3>Insert a new order</h3>
    <button onclick="window.location.href='insertorder.php'">Insert Order</button>

    <h3>Delete an existing menu item</h3>
    <button onclick="window.location.href='deletemenuitem.php'">Delete Menu Item</button>

    <h3>Modify an existing menu item</h3>
    <button onclick="window.location.href='modifymenuitem.php'">Modify Menu Item</button>

    <h3>View drivers</h3>
    <button onclick="window.location.href='viewdrivers.php'">View Drivers</button>

    <h3>View all orders</h3>
    <button onclick="window.location.href='vieworders.php'">View Orders</button>
  </div>
</body>
</html>

