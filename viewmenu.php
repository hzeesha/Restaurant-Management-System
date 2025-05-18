# file to view menu items
<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "cs3319";
$dbname = "assign2db";

// Create connection
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Determine sorting order and direction based on query parameters
$orderBy = isset($_GET['order']) && in_array($_GET['order'], ['dishname', 'price']) ? $_GET['order'] : 'dishname';
$direction = isset($_GET['direction']) && $_GET['direction'] === 'DESC' ? 'DESC' : 'ASC';

// Query based on sorting order and direction
$sql = "SELECT * FROM menuitem ORDER BY $orderBy $direction";
$result = $connection->query($sql);
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
    <h1>Our Menu!</h1>
    
    <button onclick="sortMenu('dishname', 'ASC')">Sort by Name (A-Z)</button>
    <button onclick="sortMenu('dishname', 'DESC')">Sort by Name (Z-A)</button>
    <button onclick="sortMenu('price', 'ASC')">Sort by Price (Low to High)</button>
    <button onclick="sortMenu('price', 'DESC')">Sort by Price (High to Low)</button>

    <ul>
      <h2>Available Dishes:</h2>
      <?php
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo "<li>" . htmlspecialchars($row["dishname"]) . " - $" . htmlspecialchars($row["price"]) . "</li>";
          }
      } else {
          echo "<li>No menu items found.</li>";
      }
      $connection->close();
      ?>
    </ul>
  </div>

  <script>
    function sortMenu(orderBy, direction) {
        window.location.href = 'viewmenu.php?order=' + orderBy + '&direction=' + direction;
    }
  </script>
</body>
</html>