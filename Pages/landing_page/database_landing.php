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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="landing_page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <title>Document</title>
</head>

<body>
    <div class="container">
        <!-- Top 4 boxes -->
        <div class="box top-box" onclick="window.location.href='../company_information/company_info.php'">Update
            Company Details</div>

        <div class="box" onclick="window.location.href='../Stock_list/Stock_list.php'">Upload Stock List</div>

        <div class="box" onclick="window.location.href='../Stock_links/Stock_links.php'">Upload Stock Links</div>

        <div class="box" onclick="window.location.href='../customer_data_display/customer.php'">Customer Data</div>

        <div class="box" onclick="window.location.href='../Stock_database/Stock_database.php'">Stock List Database</div>
    </div>

    <a href="home_landing_page.php" class="home-button">
        <i class="fas fa-home"></i>
    </a>
</body>

</html>