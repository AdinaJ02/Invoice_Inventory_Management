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
        <div class="box top-box" onclick="window.location.href='../Memo/memo.php'">Create A New Memo</div>

        <div class="box top-box" onclick="window.location.href='../edit_memo_table/edit_memo_table.php'">Edit A Memo</div>
        
        <div class="box top-box" onclick="window.location.href='../memo_display/memo_display.php'">Reports</div>
    </div>

    <a href="home_landing_page.php" class="home-button">
        <i class="fas fa-home"></i>
    </a>
</body>
</html>