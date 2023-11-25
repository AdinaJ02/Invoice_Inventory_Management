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
  <link rel="stylesheet" href="home_landing.css" />
  <title>Document</title>
</head>

<body>
  <div class="container">
    <div class="box top-box" onmouseover="showOptions(this)" onmouseout="hideOptions(this)"
      onclick="redirectTo('memo_landing.php')">
      Memo
      <div class="options">
        <div class="option" onclick="handleOptionClick(event, '../Memo/memo.php')">
          Create Memo
        </div>
        <div class="option" onclick="handleOptionClick(event, '../edit_memo_table/edit_memo_table.php')">
          Edit Memo
        </div>
        <div class="option" onclick="handleOptionClick(event, '../memo_display/memo_display.php')">
          Memo Reports
        </div>
      </div>
    </div>

    <div class="box top-box" onmouseover="showOptions(this)" onmouseout="hideOptions(this)"
      onclick="redirectTo('invoice_landing.php')">
      Invoice
      <div class="options">
        <div class="option" onclick="handleOptionClick(event, '../create_invoice/create_invoice.php')">
          Create Invoice
        </div>
        <div class="option" onclick="handleOptionClick(event, '../edit_invoice_table/edit_invoice_table.php')">
          Edit Invoice
        </div>
        <div class="option" onclick="handleOptionClick(event, '../invoice_display/invoice_display.php')">
          Invoice Reports
        </div>
      </div>
    </div>

    <div class="box top-box" onmouseover="showOptions(this)" onmouseout="hideOptions(this)"
      onclick="redirectTo('report_landing.php')">
      Reports

      <div class="options">
        <div class="option" onmouseover="showOptions(this)" onmouseout="hideOptions(this)"
          onclick="handleOptionClick(event, '../landing_page/sales_landing.php')">
          All Sales
          <div class="options">
            <div class="option" onclick="handleOptionClick(event, '../sales_reports/all_sales_reports/all_sales.php')">
              All Sales
            </div>
            <div class="option"
              onclick="handleOptionClick(event, '../sales_reports/sales_customer_reports/Customer_Sales.php')">
              PartyName Sales
            </div>
            <div class="option" onclick="handleOptionClick(event, '../sales_reports/sales_lot_reports/lot_sales.php')">
              Lot No Sales
            </div>
          </div>
        </div>

        <div class="option" onclick="handleOptionClick(event, '../edit_memo_table/edit_memo_table.php')">
          All Sales Invoice
        </div>
        <div class="option" onclick="handleOptionClick(event, '../memo_display/memo_display.php')">
          All Sales Memo
        </div>
      </div>
    </div>

    <div class="box top-box" onmouseover="showOptions(this)" onmouseout="hideOptions(this)"
      onclick="redirectTo('database_landing.php')">
      Database
      <div class="options">
        <div class="option" onclick="handleOptionClick(event, '../company_information/company_info.php')">
          Update Company Details
        </div>
        <div class="option" onclick="handleOptionClick(event, '../Stock_list/Stock_list.php')">
          Upload Stock List
        </div>
        <div class="option" onclick="handleOptionClick(event, '../Stock_links/Stock_links.php')">
          Upload Stock Links
        </div>
        <div class="option" onclick="handleOptionClick(event, '../customer_data_display/customer.php')">
          Customer Data
        </div>
        <div class="option" onclick="handleOptionClick(event, '../Stock_database/Stock_database.php')">
          Stock List Database
        </div>
      </div>
    </div>
  </div>
</body>

<script>
  function showOptions(element) {
    element.querySelector(".options").style.display = "block";
  }

  function hideOptions(element) {
    element.querySelector(".options").style.display = "none";
  }

  function redirectTo(url) {
    window.location.href = url;
  }

  function handleOptionClick(event, url) {
    event.stopPropagation();
    redirectTo(url);
  }
</script>

</html>