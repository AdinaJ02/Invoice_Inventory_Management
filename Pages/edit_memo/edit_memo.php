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
    <link rel="stylesheet" href="edit_memo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>

<body>
    <form>
        <table class="header_top">
            <tr>
                <td colspan="3" id="header_table">
                    <h3><input type="text" id="memorandum_day" name="memorandum_day" readonly> - DAY MEMORANDUM
                    </h3>
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
                        <input type="text" id="recipient" name="recipient" placeholder="Name">
                        <input type="text" id="addressInput" name="address" placeholder="Address">
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
                    <th class="elements">Sr No</th>
                    <th class="elements">Lot No</th>
                    <th class="elements">Description</th>
                    <th class="elements">Shape</th>
                    <th class="elements">Size</th>
                    <th class="elements">Pcs</th>
                    <th class="elements">Wt (cts)</th>
                    <th class="elements">Color</th>
                    <th class="elements">Clarity</th>
                    <th class="elements">Certificate</th>
                    <th class="elements">Rap ($)</th>
                    <th class="elements">Discount</th>
                    <th class="elements">Price ($)</th>
                    <th class="elements">Total</th>
                    <th class="elements">Return</th>
                    <th class="elements">Kept</th>
                    <th class="elements">Final Total</th>
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
                <td name="total_tot" class="total_tot"></td>
                <td></td>
                <td></td>
                <td id="total_final_tot" name="total_final_tot" class="total_final_tot"></td>
                <td></td>
            </tr>
        </table>

        <div id="successMessage" class="success-message"></div>

        <div class="form-group" style="text-align: center;">
            <div class="button-container">
                <input type="button" value="Add Row" id="addButton">
                <input type="button" value="Save Memo" id="saveButton">
                <input type="button" value="Close Memo" id="closeButton">
                <input type="button" value="Print Memo" id="submitMemo" onclick="printMemo()">
                <input type="button" value="Print Invoice" id="printButton">
                <input type="button" value="Back" id="goBack" onclick="goBackOneStep()">
            </div>
        </div>
    </form>
    <script src="edit_memo.js"></script>
    <a href="../landing_page/home_landing_page.php" class="home-button">
        <i class="fas fa-home"></i>
    </a>

</body>

</html>