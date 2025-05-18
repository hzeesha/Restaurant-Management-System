# This file is to view drivers who have not yet delivered any orders
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

// Query based on sorting order
$sql = "SELECT d.firstname, d.lastname, d.driverid FROM driver d LEFT JOIN driverdata dd ON dd.firstname = d.firstname WHERE dd.firstname IS NULL;";
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
    <h1>Drivers!</h1>

    <ul>
      <h2>Drivers who have yet to make a delivery:</h2>
      <?php
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo "<li>" . "First Name: ". htmlspecialchars($row["firstname"]) . ", Last Name: " . htmlspecialchars($row["lastname"]) . ", Driver ID: " . htmlspecialchars($row["driverid"]) . "</li>";
          }
      } else {
          echo "<li>No drivers found.</li>";
      }
      $connection->close();
      ?>
    </ul>
  </div>
</body>
</html>