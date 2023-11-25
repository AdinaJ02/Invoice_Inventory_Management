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
        <div class="box top-box" onclick="window.location.href='../sales_reports/all_sales_reports/all_sales.php'">All sales</div>

        <div class="box top-box" onclick="window.location.href='../sales_reports/sales_customer_reports/Customer_Sales.php'">Party name sales</div>
        
        <div class="box top-box" onclick="window.location.href='../sales_reports/sales_lot_reports/lot_sales.php'">Lot No sales</div>
    </div>

    <a href="report_landing.php" class="home-button">
        <i class="fas fa-home"></i>
    </a>
</body>
</html>