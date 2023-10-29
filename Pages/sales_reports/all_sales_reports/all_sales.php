<?php

// Include the database connection
require '../../../vendor/autoload.php';
include '../../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch data from the database with the "is_open" condition
$sql = "SELECT m.memo_no, m.memo_date, m.customer_name
        FROM memo m
        WHERE m.is_open = 'close'";
$result = $conn->query($sql);

if (!$result) {
    die('Error: ' . $conn->error);
}

$data = array();

while ($row = $result->fetch_assoc()) {
    $data[$row['memo_no']] = [
        'memo_no' => $row['memo_no'],
        'memo_date' => $row['memo_date'],
        'customer_name' => $row['customer_name'],
        'items' => [],
    ];
}

// Fetch lot_no, shape, size, pcs, weight, and final_total from memo_data based on memo_no
foreach ($data as $memoNo => &$memoData) {
    $sql = "SELECT lot_no, shape, `size`, pcs, `weight`, final_total
            FROM memo_data
            WHERE memo_no = '$memoNo'";
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $memoData['items'][] = $row;
        }
    }
}

// Fetch invoice data
$sql = "SELECT iw.invoice_no, iw.date, iw.customer_name
        FROM invoice_wmemo iw
        WHERE iw.payment_status = 'received'";
$result = $conn->query($sql);

$invoiceData = array();

while ($row = $result->fetch_assoc()) {
    $invoiceData[$row['invoice_no']] = [
        'invoice_no' => $row['invoice_no'],
        'date' => $row['date'],
        'customer_name' => $row['customer_name'],
        'items' => [],
    ];
}

// Fetch lot_no, shape, wt, and total from invoice_data based on invoice_no
foreach ($invoiceData as $invoiceNo => &$invoiceInfo) {
    $sql = "SELECT lot_no, shape, wt, total
            FROM invoice_data
            WHERE invoice_no = '$invoiceNo'";
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $invoiceInfo['items'][] = $row;
        }
    }
}

$mergedData = array_merge($data, $invoiceData);
$importedData = json_encode($mergedData);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock List</title>
    <link rel="stylesheet" href="all_sales.css">
</head>

<body>
    <!-- <form id="downloadForm" method="post" action="download_excel.php">
        <input type="hidden" name="excelData" id="excelData">
    </form> -->


    <div class="dropdown-container">
        <select class="dropdown" id="customerDropdown">
            <option value="" disabled selected>Select Customer</option>
            <?php
            // Fetch customer names from the "memo" table
            $sql = "SELECT DISTINCT customer_name FROM memo WHERE is_open = 'close'";
            $result = $conn->query($sql);

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row['customer_name'] . '">' . $row['customer_name'] . '</option>';
                }
            }
            ?>
        </select>

        <select class="dropdown" id="lotNoDropdown">
            <option value="" disabled selected>Select Lot No</option>
            <?php
            // Fetch distinct lot_no values based on memo_no
            $sql = "SELECT DISTINCT lot_no FROM memo_data
        WHERE memo_no IN (SELECT memo_no FROM memo WHERE is_open = 'close')";

            $result = $conn->query($sql);

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row['lot_no'] . '">' . $row['lot_no'] . '</option>';
                }
            }
            ?>
        </select>

        <select class="dropdown" id="ShapeDropdown">
            <option value="" disabled selected>Select Shape</option>
            <?php
            // Fetch distinct lot_no values based on memo_no
            $sql = "SELECT DISTINCT shape FROM memo_data
        WHERE memo_no IN (SELECT memo_no FROM memo WHERE is_open = 'close')";

            $result = $conn->query($sql);

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row['shape'] . '">' . $row['shape'] . '</option>';
                }
            }
            ?>
        </select>

        <select class="dropdown" id="sortDropdown">
            <option value="" disabled selected>Date</option>
            <option value="date-asc">Date Ascending</option>
            <option value="date-desc">Date Descending</option>
        </select>

        <button id="removeFiltersButton">Remove Filters</button>
        <button id="downloadButton">Download as Excel</button>
        <button id="backButton">Back</button>

    </div>


    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Memo/Invoice</th>
                <th>No.</th>
                <th>Date</th>
                <th>Party Name</th>
                <th>Lot No.</th>
                <th>Shape</th>
                <th>Size</th>
                <th>Pcs</th>
                <th>Wt (cts)</th>
                <th>value</th>
            </tr>
        </thead>
        <tbody id="table-body">

            <script>
                var importedData = <?php echo $importedData; ?>; // Parse the JSON data

                var tableBody = document.getElementById("table-body");
                var sortDropdown = document.getElementById("sortDropdown");
                var customerDropdown = document.getElementById("customerDropdown");
                var lotNoDropdown = document.getElementById("lotNoDropdown");
                var ShapeDropdown = document.getElementById("ShapeDropdown");
                var tableRows;
                var downloadButton = document.getElementById("downloadButton");


                function applyFilters() {
                    const selectedCustomer = customerDropdown.value;
                    const selectedLotNo = lotNoDropdown.value;
                    const selectedShape = ShapeDropdown.value;
                    const filteredData = [];

                    Object.values(importedData).forEach(function (item) {
                        item.items.forEach(function (itemData) {
                            if (
                                (selectedCustomer === "" || item.customer_name.toLowerCase() === selectedCustomer) &&
                                (selectedLotNo === "" || itemData.lot_no === selectedLotNo) &&
                                (selectedShape === "" || itemData.shape === selectedShape)
                            ) {
                                filteredData.push({
                                    type: item.memo_no ? "Memo" : "Invoice",
                                    memo_no: item.memo_no ? item.memo_no : item.invoice_no,
                                    memo_date: item.memo_date ? item.memo_date : item.date,
                                    customer_name: item.customer_name,
                                    lot_no: itemData.lot_no,
                                    shape: itemData.shape,
                                    size: itemData.size,
                                    pcs: itemData.pcs,
                                    weight: itemData.weight ? itemData.weight : itemData.wt,
                                    value: itemData.final_total ? itemData.final_total : itemData.total,
                                });
                            }
                        });
                    });

                    return filteredData;
                }


                downloadButton.addEventListener("click", function () {
                    // Extract filtered table data
                    var filteredData = applyFilters();

                    // Convert filtered data to CSV
                    var csvData = "Memo/Invoice,No.,Date,Party Name,Lot No.,Shape,Size,Pcs,Wt (cts),Value\n";
                    filteredData.forEach(function (row) {
                        csvData += Object.values(row).join(',') + '\n';
                    });

                    // Create a data URI for the CSV
                    var csvBlob = new Blob([csvData], { type: "text/csv" });
                    var csvUrl = URL.createObjectURL(csvBlob);

                    // Create a hidden link and trigger the download
                    var downloadLink = document.createElement("a");
                    downloadLink.href = csvUrl;
                    downloadLink.download = "filtered_data.csv";
                    downloadLink.style.display = "none";
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);
                });





                customerDropdown.addEventListener("change", filterTable);
                lotNoDropdown.addEventListener("change", filterTableLotNo);
                ShapeDropdown.addEventListener("change", filterTableShape);
                sortDropdown.addEventListener("change", filterTableDate);

                function filterTable() {
                    const selectedCustomer = customerDropdown.value;
                    const selectedLotNo = lotNoDropdown.value;
                    const selectedShape = ShapeDropdown.value;
                    tableRows = tableBody.querySelectorAll("tr"); // Define tableRows here

                    tableRows.forEach(row => {
                        const customerNameCell = row.querySelector("td:nth-child(4)"); // Select the 4th column (customer name)
                        const lotNoCell = row.querySelector("td:nth-child(5)");
                        const ShapeCell = row.querySelector("td:nth-child(6)");
                        const str = customerNameCell.textContent;
                        const showRow = selectedCustomer === "" || str.toLocaleLowerCase() === selectedCustomer;
                        row.style.display = showRow ? "table-row" : "none";
                    });
                }

                function filterTableLotNo() {
                    const selectedLotNo = lotNoDropdown.value;
                    tableRows = tableBody.querySelectorAll("tr");
                    tableRows.forEach(row => {
                        const lotNoCell = row.querySelector("td:nth-child(5)");
                        const showRow = selectedLotNo === "" || lotNoCell.textContent === selectedLotNo;
                        row.style.display = showRow ? "table-row" : "none";
                    });
                }

                function filterTableShape() {
                    const selectedShape = ShapeDropdown.value;
                    tableRows = tableBody.querySelectorAll("tr");
                    tableRows.forEach(row => {
                        const ShapeCell = row.querySelector("td:nth-child(6)");
                        const showRow = selectedShape === "" || ShapeCell.textContent === selectedShape;
                        row.style.display = showRow ? "table-row" : "none";
                    });
                }

                function filterTableDate() {
                    const sortOption = sortDropdown.value;
                    const tbody = document.querySelector(".table_data tbody");
                    tableRows = tbody.querySelectorAll("tr");

                    if (sortOption === "date-asc") {
                        // Sort the rows in ascending order by date
                        const sortedRows = Array.from(tableRows).sort((a, b) => {
                            const dateA = new Date(a.querySelector("td:nth-child(3)").textContent);
                            const dateB = new Date(b.querySelector("td:nth-child(3)").textContent);
                            return dateA - dateB;
                        });

                        // Append the sorted rows to the tbody
                        sortedRows.forEach(row => tbody.appendChild(row));
                    } else if (sortOption === "date-desc") {
                        // Sort the rows in descending order by date
                        const sortedRows = Array.from(tableRows).sort((a, b) => {
                            const dateA = new Date(a.querySelector("td:nth-child(3)").textContent);
                            const dateB = new Date(b.querySelector("td:nth-child(3)").textContent);
                            return dateB - dateA;
                        });

                        // Append the sorted rows to the tbody
                        sortedRows.forEach(row => tbody.appendChild(row));
                    }
                }

                function formatDate(inputDate) {
                    const date = new Date(inputDate);
                    const options = { day: 'numeric', month: 'short', year: 'numeric' };
                    return date.toLocaleDateString('en-US', options);
                }

                function updateTable() {
                    while (tableBody.firstChild) {
                        tableBody.removeChild(tableBody.firstChild);
                    }

                    var selectedCustomer = customerDropdown.value;

                    Object.values(importedData).forEach(function (item) {
                        item.items.forEach(function (itemData) {
                            var row = document.createElement("tr");
                            row.innerHTML = `
                                <td>${item.memo_no ? 'Memo' : 'Invoice'}</td>
                                <td>${item.memo_no || item.invoice_no}</td>
                                <td>${formatDate(item.memo_date || item.date)}</td>
                                <td>${item.customer_name}</td>
                                <td>${itemData.lot_no}</td>
                                <td>${itemData.shape}</td>
                                <td>${itemData.size ? itemData.size : ''}</td>
                                <td>${itemData.pcs ? itemData.pcs : ''}</td>
                                <td>${itemData.weight ? itemData.weight : itemData.wt}</td>
                                <td>${itemData.final_total || itemData.total}</td>
                            `;
                            tableBody.appendChild(row);
                        });
                    });
                }

                // Initial table update
                updateTable();

                var removeFiltersButton = document.getElementById("removeFiltersButton");

                removeFiltersButton.addEventListener("click", function () {
                    // Reload the page
                    location.reload();
                });

                var backButton = document.getElementById("backButton");

                backButton.addEventListener("click", function () {
                    // Use the browser's history to navigate back to the previous page
                    window.history.back();
                });

            </script>
        </tbody>
    </table>
</body>

</html>