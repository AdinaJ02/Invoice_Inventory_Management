<?php
// Include the database connection
require '../../../vendor/autoload.php';
include '../../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch memo_no from the memo table where is_open is 'close'
$sql = "SELECT memo_no FROM memo WHERE is_open = 'close'";
$result = $conn->query($sql);

if (!$result) {
    die('Error: ' . $conn->error);
}

$data = array();

while ($row = $result->fetch_assoc()) {
    $data[] = $row['memo_no'];
}

// Now, you can fetch lot_no, shape, and size for each memo_no from memo_data table
$combinedData = array();

foreach ($data as $memo_no) {
    $sql = "SELECT lot_no, shape, `size`, final_total FROM memo_data WHERE memo_no = '$memo_no'";
    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $combinedData[$row['lot_no']][] = array(
                'shape' => $row['shape'],
                'size' => $row['size'],
                'final_total' => $row['final_total'],
            );
        }
    }
}

// Fetch invoice_no where payment_status is 'received' from invoice_wmemo
$invoiceSql = "SELECT invoice_no FROM invoice_wmemo WHERE payment_status = 'received'";
$invoiceResult = $conn->query($invoiceSql);

if ($invoiceResult) {
    while ($invoiceRow = $invoiceResult->fetch_assoc()) {
        $invoice_no = $invoiceRow['invoice_no'];

        // Fetch lot_no, shape, and sales from invoice_data for each invoice_no
        $invoiceDataSql = "SELECT lot_no, shape, total FROM invoice_data WHERE invoice_no = '$invoice_no'";
        $invoiceDataResult = $conn->query($invoiceDataSql);

        if ($invoiceDataResult) {
            while ($invoiceDataRow = $invoiceDataResult->fetch_assoc()) {
                $combinedData[$invoiceDataRow['lot_no']][] = array(
                    'shape' => $invoiceDataRow['shape'],
                    'total' => $invoiceDataRow['total'],
                );
            }
        }
    }
}

// Calculate the total sales for each unique lot number
$totalSalesPerLotNo = array();
foreach ($combinedData as $lot_no => $lotData) {
    $totalSalesPerLotNo[$lot_no] = 0;
    foreach ($lotData as $data) {
        if (isset($data['final_total'])) {
            $totalSalesPerLotNo[$lot_no] += $data['final_total'];
        } elseif (isset($data['total'])) {
            $totalSalesPerLotNo[$lot_no] += $data['total'];
        }
    }
}

// Display the results
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock List</title>
    <link rel="stylesheet" href="lot_sales.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

</head>

<body>
    <div class="dropdown-container">
        <select class="dropdown" id="lotNoDropdown">
            <option value="" selected>All Lot No</option>
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
            <option value="" selected>All Shapes</option>
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
        <button id="removeFiltersButton">Remove filters</button>
        <button id="downloadButton">Download Excel</button>
        <button id="backButton">Back</button>
    </div>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Lot No.</th>
                <th>Shape</th>
                <th>Size</th>
                <th>Sales</th>
                <th>Total Sales</th>
            </tr>
        </thead>
        <tbody id="table-body">
            <?php
            $previousLotNo = null;
            $rowspan = 0;
            $totalSales = 0;

            foreach ($combinedData as $lot_no => $lotData) {
                $rowspan = count($lotData);
                $totalSales = $totalSalesPerLotNo[$lot_no];

                if ($lot_no !== $previousLotNo) {
                    echo '<tr>';
                    echo '<td rowspan="' . $rowspan . '">' . $lot_no . '</td>';
                }

                $first = true;

                foreach ($lotData as $data) {
                    if (!$first) {
                        echo '<tr>';
                    }

                    echo '<td>' . $data['shape'] . '</td>';
                    echo '<td>' . ($data['size'] ?? '') . '</td>';
                    echo '<td>' . ($data['final_total'] ?? $data['total']) . '</td>';

                    if ($first && $lot_no !== $previousLotNo) {
                        echo '<td rowspan="' . $rowspan . '">' . $totalSales . '</td>';
                    }

                    echo '</tr>';
                    $first = false;
                }

                $previousLotNo = $lot_no;
            }
            ?>
        </tbody>
    </table>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.3/xlsx.full.min.js"></script>

    <script>
        var combinedData = <?php echo json_encode($combinedData); ?>;

        document.addEventListener("DOMContentLoaded", function () {
            var lotNoDropdown = document.getElementById("lotNoDropdown");
            var shapeDropdown = document.getElementById("ShapeDropdown");

            lotNoDropdown.addEventListener("change", filterTable);
            shapeDropdown.addEventListener("change", filterTable);

            function filterTable() {
                var lotNoDropdown = document.getElementById("lotNoDropdown");
                var shapeDropdown = document.getElementById("ShapeDropdown");

                var selectedLotNo = lotNoDropdown.value;
                var selectedShape = shapeDropdown.value;

                var tableBody = document.getElementById("table-body");

                for (var lot_no in combinedData) {
                    var lotData = combinedData[lot_no];
                    var shouldDisplay = false;

                    for (var i = 0; i < lotData.length; i++) {
                        var data = lotData[i];
                        if (
                            (selectedLotNo === "" || lot_no === selectedLotNo) &&
                            (selectedShape === "" || data.shape === selectedShape)
                        ) {
                            shouldDisplay = true;
                            break; // At least one row matches the filter
                        }
                    }

                    var rows = tableBody.getElementsByTagName("tr");
                    for (var i = 0; i < rows.length; i++) {
                        var row = rows[i];
                        var lotNoCell = row.getElementsByTagName("td")[0];
                        if (lotNoCell.innerHTML === lot_no) {
                            row.style.display = shouldDisplay ? "" : "none";
                        }
                    }
                }
            }
        });

        var removeFiltersButton = document.getElementById("removeFiltersButton");

        removeFiltersButton.addEventListener("click", function () {
            location.reload();
        });

        function exportToExcel() {
            const table = document.querySelector('.table_data');
            const tableData = XLSX.utils.table_to_book(table, { sheet: 'Sheet1' });

            // Generate and download the Excel file
            XLSX.writeFile(tableData, 'exported_data.xlsx');
        }

        // Attach the exportToExcel function to the downloadButton click event
        const downloadButton = document.getElementById("downloadButton");
        downloadButton.addEventListener("click", exportToExcel);

        const tableBody = document.getElementById("table-body");

        var backButton = document.getElementById("backButton");

        backButton.addEventListener("click", function () {
            // Use the browser's history to navigate back to the previous page
            window.history.back();
        });

    </script>
    <a href="../../landing_page/landing_page.html" class="home-button">
                <i class="fas fa-home"></i>
            </a>
</body>

</html>