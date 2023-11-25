<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header('Location: ../../index.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="landing_page.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
  <title>Document</title>
</head>

<body>
  <div class="container">
    <div class="box top-box" onclick="window.location.href='../create_invoice/create_invoice.php'">
      Create A New invoice
    </div>

    <div class="box top-box" onclick="window.location.href='../edit_invoice_table/edit_invoice_table.php'">
      Edit An invoice
    </div>

    <div class="box top-box" onclick="window.location.href='../invoice_display/invoice_display.php'">Reports</div>
  </div>

  <a href="home_landing_page.php" class="home-button">
    <i class="fas fa-home"></i>
  </a>
</body>

</html>