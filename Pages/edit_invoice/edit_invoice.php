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
    <title>Invoice</title>
    <link rel="stylesheet" href="edit_invoice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

</head>

<body>
    <div class="printable-content">
        <form>
            <table class="header_top">
                <tr>
                    <td colspan="3" id="header_table">
                        <h3>INVOICE</h3>
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
                    <td colspan="3"><b>Invoice no.<input type="text" name="invoice_no" id="invoice_no"
                                placeholder="Invoice Number" readonly></b></td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div class="date-container">
                            <label for="date"><b>Date</b></label>
                            <input type="date" id="date" name="date" style="display: none;">
                            <!-- Hide the date input -->
                            <span id="formatted-date"></span> <!-- Display the formatted date here -->
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
                    <td colspan="3" id="terms"><b></b>
                    </td>
                </tr>
            </table>

            <table class="table_data">
                <thead>
                    <tr id="header">
                        <th>Sr No</th>
                        <th>Lot No</th>
                        <th>Wt (cts)</th>
                        <th>Shape</th>
                        <th>Color</th>
                        <th>Clarity</th>
                        <th>Certificate</th>
                        <th>Rap ($)</th>
                        <th>Discount</th>
                        <th>Price/CTS ($)</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <!-- JavaScript will generate rows here -->
                </tbody>
                <tr>
                    <td><b>Discount</b></td>
                    <td></td>
                    <td name="disc_wt" id="disc_wt" value="1"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td contenteditable="true" name="disc_price" id="disc_price"></td>
                    <td name="disc_total" id="disc_total"></td>
                    <td></td>
                </tr>
                <tr>
                    <td><b>Total</b></td>
                    <td></td>
                    <td name="total_wt" class="total_wt"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td name="total_final_tot" class="total_final_tot"></td>
                    <td></td>
                </tr>
                <tr style="text-align:center">
                    <td colspan="17"><b>THANK YOU</b></td>
                </tr>
            </table>

            <div id="successMessage" class="success-message"></div>

            <div class="checkbox-container">
                <label>
                    <input type="checkbox" id="receivedCheckbox" name="paymentStatus" value="received"> Received
                </label>
                <label>
                    <input type="checkbox" id="notReceivedCheckbox" name="paymentStatus" value="not_received">
                    Not
                    Received
                </label>
            </div>

            <div class="form-group" style="text-align: center;">
                <div class="button-container">
                    <input type="button" value="Add Row" id="addButton">
                    <input type="button" value="Save" id="saveInvoice">
                    <input type="button" value="Print Invoice" id="printButton">
                    <input type="button" value="Back" id="goBack" onclick="goBackOneStep()">
                </div>
            </div>
        </form>
    </div>
    <script src="edit_invoice.js"></script>
    <a href="../landing_page/home_landing_page.php" class="home-button">
        <i class="fas fa-home"></i>
    </a>
</body>

</html>