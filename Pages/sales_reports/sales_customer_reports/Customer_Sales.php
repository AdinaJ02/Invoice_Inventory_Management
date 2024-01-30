<?php
require '../../../vendor/autoload.php';
include '../../../connection.php';

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch data from the memo table where is_open is 'close'
$sql = "SELECT * FROM memo WHERE is_open = 'close'";
$result = $conn->query($sql);

if (!$result) {
    die('Error: ' . $conn->error);
}

// Initialize an array to store customer data
$customerData = array();

// Step 1: Iterate through rows in the memo table
while ($row = $result->fetch_assoc()) {
    $customerName = $row['customer_name'];
    $memoNo = $row['memo_no'];

    // Step 2: Fetch lot_no, shape, and final_total from memo_data for the current memo
    $memoDataSql = "SELECT lot_no, shape, final_total FROM memo_data WHERE memo_no = '$memoNo'AND (final_total > 0)";
    $memoDataResult = $conn->query($memoDataSql);

    if (!$memoDataResult) {
        die('Error: ' . $conn->error);
    }

    // Initialize variables to store data for the current customer
    $lotNo = array();
    $shape = array();
    $final_total = 0;

    // Calculate the final_total and collect lot_no and shape for the current memo
    while ($memoDataRow = $memoDataResult->fetch_assoc()) {
        $lotNo[] = $memoDataRow['lot_no'];
        $shape[] = $memoDataRow['shape'];
        $final_total += $memoDataRow['final_total'];
    }

    // Add or update the customer data in the array
    if (isset($customerData[strtolower($customerName)])) {
        $customerData[strtolower($customerName)]['lot_no'] = array_merge($customerData[strtolower($customerName)]['lot_no'], $lotNo);
        $customerData[strtolower($customerName)]['shape'] = array_merge($customerData[strtolower($customerName)]['shape'], $shape);
        $customerData[strtolower($customerName)]['final_total'] += $final_total;
    } else {
        $customerData[strtolower($customerName)] = [
            'lot_no' => $lotNo,
            'shape' => $shape,
            'final_total' => $final_total,
        ];
    }
}

// Fetch invoice data
$invoiceSql = "SELECT invoice_no, customer_name
              FROM invoice_wmemo 
              WHERE payment_status = 'Received'";
$invoiceResult = $conn->query($invoiceSql);

if (!$invoiceResult) {
    die('Error: ' . $conn->error);
}

// Step 3: Iterate through rows in the invoice_wmemo table
while ($invoiceRow = $invoiceResult->fetch_assoc()) {
    $customerName = $invoiceRow['customer_name'];
    $invoiceNo = $invoiceRow['invoice_no'];

    // Step 4: Fetch lot_no, shape, and total from invoice_data for the current invoice
    $invoiceDataSql = "SELECT lot_no, shape, total FROM invoice_data WHERE invoice_no = '$invoiceNo' AND (total > 0)";
    $invoiceDataResult = $conn->query($invoiceDataSql);

    if (!$invoiceDataResult) {
        die('Error: ' . $conn->error);
    }

    // Initialize variables to store data for the current customer
    $lotNo = array();
    $shape = array();
    $final_total = 0;

    // Calculate the final_total and collect lot_no and shape for the current invoice
    while ($invoiceDataRow = $invoiceDataResult->fetch_assoc()) {
        $lotNo[] = $invoiceDataRow['lot_no'];
        $shape[] = $invoiceDataRow['shape'];
        $final_total += $invoiceDataRow['total'];
    }

    // Add or update the customer data in the array
    if (isset($customerData[strtolower($customerName)])) {
        $customerData[strtolower($customerName)]['lot_no'] = array_merge($customerData[strtolower($customerName)]['lot_no'], $lotNo);
        $customerData[strtolower($customerName)]['shape'] = array_merge($customerData[strtolower($customerName)]['shape'], $shape);
        $customerData[strtolower($customerName)]['final_total'] += $final_total;
    } else {
        $customerData[strtolower($customerName)] = [
            'lot_no' => $lotNo,
            'shape' => $shape,
            'final_total' => $final_total,
        ];
    }
}

// Fetch memo numbers from the invoice table
$invoiceMemoNos = array();
$invoiceMemoSql = "SELECT DISTINCT memo_no FROM invoice WHERE payment_status = 'Received'";
$invoiceMemoResult = $conn->query($invoiceMemoSql);

if (!$invoiceMemoResult) {
    die('Error: ' . $conn->error);
}

while ($invoiceMemoRow = $invoiceMemoResult->fetch_assoc()) {
    $invoiceMemoNos[] = $invoiceMemoRow['memo_no'];
}

// Fetch memo records not included in the invoice memo numbers
$notIncludedMemoSql = "SELECT * FROM memo WHERE is_open = 'close' AND memo_no NOT IN (" . implode(',', $invoiceMemoNos) . ")";
$notIncludedMemoResult = $conn->query($notIncludedMemoSql);

if (!$notIncludedMemoResult) {
    die('Error: ' . $conn->error);
}

// Initialize an array to store memo data
$memoData = array();

// Iterate through rows in the not included memo records
while ($notIncludedMemoRow = $notIncludedMemoResult->fetch_assoc()) {
    $customerName = $notIncludedMemoRow['customer_name'];
    $memoNo = $notIncludedMemoRow['memo_no'];
    $finalTotal = $notIncludedMemoRow['total_total'];

    // Add or update the memo data in the array
    if (isset($memoData[strtolower($customerName)])) {
        $memoData[strtolower($customerName)]['total_total'] += $finalTotal;
    } else {
        $memoData[strtolower($customerName)] = [
            'total_total' => $finalTotal,
        ];
    }
}

// Fetch memo numbers from the invoice table
$invoiceMemoNos = array();
$invoiceMemoSql = "SELECT DISTINCT memo_no FROM invoice WHERE payment_status = 'Received'";
$invoiceMemoResult = $conn->query($invoiceMemoSql);

if (!$invoiceMemoResult) {
    die('Error: ' . $conn->error);
}

while ($invoiceMemoRow = $invoiceMemoResult->fetch_assoc()) {
    $invoiceMemoNos[] = $invoiceMemoRow['memo_no'];
}

// Fetch memo records for the obtained memo numbers
$invoiceMemoRecords = array();
if (!empty($invoiceMemoNos)) {
    $invoiceMemoRecordsSql = "SELECT * FROM memo WHERE is_open = 'close' AND memo_no IN (" . implode(',', $invoiceMemoNos) . ")";
    $invoiceMemoRecordsResult = $conn->query($invoiceMemoRecordsSql);

    if (!$invoiceMemoRecordsResult) {
        die('Error: ' . $conn->error);
    }

    while ($invoiceMemoRecord = $invoiceMemoRecordsResult->fetch_assoc()) {
        $customerName = $invoiceMemoRecord['customer_name'];
        $finalTotal = $invoiceMemoRecord['total_total'];

        // Add or update the memo data in the array
        if (isset($invoiceData[strtolower($customerName)])) {
            $invoiceData[strtolower($customerName)]['total_invoice_sales'] += $finalTotal;
        } else {
            $invoiceData[strtolower($customerName)] = [
                'total_invoice_sales' => $finalTotal,
            ];
        }
    }
}

// Fetch final_total from invoice_wmemo table for each customer
$invoiceWMemoSql = "SELECT customer_name, final_total FROM invoice_wmemo WHERE payment_status = 'Received'";
$invoiceWMemoResult = $conn->query($invoiceWMemoSql);

if (!$invoiceWMemoResult) {
    die('Error: ' . $conn->error);
}

while ($invoiceWMemoRow = $invoiceWMemoResult->fetch_assoc()) {
    $customerName = $invoiceWMemoRow['customer_name'];
    $finalTotal = $invoiceWMemoRow['final_total'];

    // Add or update the invoice_wmemo data in the array
    if (isset($invoiceData[strtolower($customerName)])) {
        $invoiceData[strtolower($customerName)]['total_invoice_sales'] += $finalTotal;
    } else {
        $invoiceData[strtolower($customerName)] = [
            'total_invoice_sales' => $finalTotal,
        ];
    }
}

// Fetch distinct customer names from the customers table
$query = "SELECT DISTINCT customer_name FROM customer_data";
$result = $conn->query($query);

// Check if the query was successful
if ($result) {
    // Create an array to store customer names
    $customerNames = array();

    // Fetch each row and store customer names in the array
    while ($row = $result->fetch_assoc()) {
        $customerNames[] = $row['customer_name'];
    }

    // Close the result set
    $result->close();
} else {
    // Handle the case where the query fails (you may want to log or display an error)
    echo "Error: " . $conn->error;
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer final_totals</title>
    <link rel="stylesheet" href="customer_Sales.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>

<body>
    <div class="dropdown-container">
        <select class="dropdown" id="customerDropdown">
            <option value="">All Customers</option>
            <?php
            // Populate the dropdown with distinct customer names in lowercase
            foreach ($customerNames as $customerName) {
                $lowercaseCustomerName = strtolower($customerName);
                echo "<option value=\"$lowercaseCustomerName\">$lowercaseCustomerName</option>";
            }
            ?>
        </select>
        <button id="downloadButton">Download Excel</button>
        <button id="backButton">Back</button>
    </div>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Customer Name</th>
                <th>Lot No.</th>
                <th>Total Invoice Sales</th>
                <th>Total Memo Sales</th>
                <th>Final Total Sales</th>
            </tr>
        </thead>
        <tbody id="table-body">
            <?php foreach ($customerData as $customerName => $data) {
                if ($data['final_total'] > 0) { // Check if final_total is greater than 0
                    ?>
                    <tr>
                        <td>
                            <?php echo $customerName; ?>
                        </td>
                        <td>
                            <?php echo implode(', ', $data['lot_no']); ?>
                        </td>
                        <td>
                            <?php echo isset($invoiceData[strtolower($customerName)]['total_invoice_sales']) ? $invoiceData[strtolower($customerName)]['total_invoice_sales'] : 0; ?>
                        </td>
                        <td>
                            <?php echo isset($memoData[strtolower($customerName)]) ? $memoData[strtolower($customerName)]['total_total'] : 0; ?>
                        </td>
                        <td>
                            <?php echo (isset($memoData[strtolower($customerName)]['total_total']) ? $memoData[strtolower($customerName)]['total_total'] : 0) + (isset($invoiceData[strtolower($customerName)]['total_invoice_sales']) ? $invoiceData[strtolower($customerName)]['total_invoice_sales'] : 0); ?>
                        </td>
                    </tr>
                    <?php
                } // End of if condition
            } ?>
        </tbody>
    </table>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.3/xlsx.full.min.js"></script>
    <script>
        const customerData = <?php echo json_encode($customerData); ?>;
        const invoiceData = <?php echo json_encode($invoiceData); ?>;
        const memoData = <?php echo json_encode($memoData); ?>;
        // Get references to the select element and table body
        const customerDropdown = document.getElementById("customerDropdown");
        const tableBody = document.getElementById("table-body");

        function filterTable() {
            const selectedCustomer = customerDropdown.value.toLowerCase().trim();

            // Loop through each row in the table body
            for (const row of tableBody.rows) {
                const customerName = row.cells[0].textContent.toLowerCase().trim();

                // Show or hide the row based on the selected customer
                row.style.display = selectedCustomer === "" || customerName === selectedCustomer ? "table-row" : "none";
            }
        }

        // Attach the filterTable function to the change event of the dropdown
        customerDropdown.addEventListener("change", filterTable);

        function exportToExcel() {
            const table = document.querySelector('.table_data');
            const tableData = XLSX.utils.table_to_book(table, { sheet: 'Sheet1' });

            // Generate and download the Excel file
            XLSX.writeFile(tableData, 'exported_data.xlsx');
        }

        // Attach the exportToExcel function to the downloadButton click event
        const downloadButton = document.getElementById("downloadButton");
        downloadButton.addEventListener("click", exportToExcel);

        var backButton = document.getElementById("backButton");

        backButton.addEventListener("click", function () {
            // Use the browser's history to navigate back to the previous page
            window.history.back();
        });
    </script>
    <script>
        document.addEventListener('contextmenu', function (e) {
            e.preventDefault();
        });

        document.addEventListener('keydown', function (e) {
            // Check if the key combination is Ctrl+U (for viewing page source)
            if ((e.ctrlKey || e.metaKey) && e.keyCode === 85) {
                e.preventDefault();
            }
        });
    </script>
    <a href="../../landing_page/home_landing_page.html" class="home-button">
        <i class="fas fa-home"></i>
    </a>
</body>

</html>