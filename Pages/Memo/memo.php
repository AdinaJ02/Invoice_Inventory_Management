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
    <title>Memo</title>
    <link rel="stylesheet" href="memo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>

<body>
    <form id="form-data">
        <table class="header_top">
            <tr>
                <td colspan="3" id="header_table">
                    <h3><select id="dayDropdown" name="memorandum_day"></select> - DAY MEMORANDUM</h3>
                </td>
            </tr>
            <tr>
                <td rowspan="3"><img src="../php_data/getImage.php" id="logo" /></td>
                <td rowspan="3" id="title_table">
                    <h2></h2>
                    <h4></h4>
                    <b></b><br />
                    <p id="address"></p>
                    <p id="email"></p>
                </td>
                <td colspan="3"><b>Memo no.</b> <input type="text" name="memo_no" id="memo_no" placeholder="Memo Number"
                        readonly></td>
            </tr>
            <tr>
                <td colspan="3">
                    <div class="date-container">
                        <label for="date"><b>Date</b></label>
                        <input type="date" id="date" name="date">
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div class="underline-input">
                        <label for="recipient"><b>To,</b></label>
                        <input type="text" id="recipient" name="recipient" placeholder="Name" required>
                        <textarea id="addressInput" name="address" placeholder="Address" required></textarea>
                        <div id="autocomplete-results"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3" id="disclaimer"><b></b>
                </td>
            </tr>
        </table>

        <table class="table_data">
            <thead>
                <tr id="header">
                    <th>Sr No</th>
                    <th>Lot No</th>
                    <th>Description</th>
                    <th>Shape</th>
                    <th>Size</th>
                    <th>Pcs</th>
                    <th>Wt (cts)</th>
                    <th>Color</th>
                    <th>Clarity</th>
                    <th>Certificate</th>
                    <th>Rap ($)</th>
                    <th>Discount</th>
                    <th>Price ($)</th>
                    <th>Total</th>
                    <th>Return</th>
                    <th>Kept</th>
                    <th>Final Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="table-body">
                <!-- JavaScript will generate rows here -->
            </tbody>
            <tr>
                <td><b>Total</b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td id="total_wt" name="total_wt" class="total_wt"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td id="total_tot" name="total_tot" class="total_tot"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </table>

        <div class="form-group" style="text-align: center;">
            <input type="submit" value="Print Memo" id="submitMemo" style="display: inline-block; margin-right: 10px;">
            <input type="button" value="Back" id="goBack" onclick="goBackOneStep()" style="display: inline-block;">
        </div>
    </form>

    <script src="memo.js"></script>
    <a href="../landing_page/home_landing_page.php" class="home-button">
        <i class="fas fa-home"></i>
    </a>
</body>

</html>