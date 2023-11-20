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
    $memoDataSql = "SELECT lot_no, shape, final_total FROM memo_data WHERE memo_no = '$memoNo'";
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
$invoiceSql = "SELECT iw.invoice_no, iw.customer_name
              FROM invoice_wmemo iw
              WHERE iw.payment_status = 'received'";
$invoiceResult = $conn->query($invoiceSql);

if (!$invoiceResult) {
    die('Error: ' . $conn->error);
}

// Step 3: Iterate through rows in the invoice_wmemo table
while ($invoiceRow = $invoiceResult->fetch_assoc()) {
    $customerName = $invoiceRow['customer_name'];
    $invoiceNo = $invoiceRow['invoice_no'];

    // Step 4: Fetch lot_no, shape, and total from invoice_data for the current invoice
    $invoiceDataSql = "SELECT lot_no, shape, total FROM invoice_data WHERE invoice_no = '$invoiceNo'";
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
        <select id="customerSelect">
            <option value="">All Customers</option>
            <?php foreach (array_keys($customerData) as $customerName) { ?>
                <option value="<?php echo strtolower($customerName); ?>">
                    <?php echo $customerName; ?>
                </option>
            <?php } ?>
        </select>
        <button id="downloadButton">Download Excel</button>
        <button id="backButton">Back</button>
    </div>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Customer Name</th>
                <th>Lot No.</th>
                <th>Shape</th>
                <th>Final Total Sales</th>
            </tr>
        </thead>
        <tbody id="table-body">
            <?php foreach ($customerData as $customerName => $data) { ?>
                <tr>
                    <td>
                        <?php echo $customerName; ?>
                    </td>
                    <td>
                        <?php echo implode(', ', $data['lot_no']); ?>
                    </td>
                    <td>
                        <?php echo implode(', ', $data['shape']); ?>
                    </td>
                    <td>
                        <?php echo $data['final_total']; ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.3/xlsx.full.min.js"></script>
    <script>
        const customerData = <?php echo json_encode($customerData); ?>;
        // Get references to the select element and table body
        const customerSelect = document.getElementById("customerSelect");
        const tableBody = document.getElementById("table-body");

        customerSelect.addEventListener("change", function () {
            // Get the selected customer from the dropdown
            const selectedCustomer = customerSelect.value.toLowerCase();

            // Clear the table body
            tableBody.innerHTML = "";

            // Iterate through customer data and display matching rows
            for (const customerName in customerData) {
                if (selectedCustomer === "" || customerName === selectedCustomer) {
                    const data = customerData[customerName];
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${customerName}</td>
                        <td>${data.lot_no.join(', ')}</td>
                        <td>${data.shape.join(', ')}</td>
                        <td>${data.final_total}</td>
                    `;
                    tableBody.appendChild(row);
                }
            }
        });

        const downloadButton = document.getElementById("downloadButton");

        downloadButton.addEventListener("click", function () {
            // Get the selected customer from the dropdown
            const selectedCustomer = customerSelect.value.toLowerCase();

            // Filter data based on the selected customer
            const filteredData = [];
            for (const customerName in customerData) {
                if (selectedCustomer === "" || customerName === selectedCustomer) {
                    filteredData.push([
                        customerName,
                        customerData[customerName].lot_no.join(', '),
                        customerData[customerName].shape.join(', '),
                        customerData[customerName].final_total
                    ]);
                }
            }

            // Create a worksheet with filtered data
            const ws = XLSX.utils.aoa_to_sheet([["Customer Name", "Lot No.", "Shape", "Final Total Sales"], ...filteredData]);

            // Create a workbook and add the worksheet
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "CustomerData");

            // Generate the Excel file and trigger the download
            XLSX.writeFile(wb, "customer_data.xlsx");
        });

        var backButton = document.getElementById("backButton");

        backButton.addEventListener("click", function () {
            // Use the browser's history to navigate back to the previous page
            window.history.back();
        });
    </script>
    <a href="../../landing_page/home_landing_page.html" class="home-button">
                <i class="fas fa-home"></i>
            </a>
</body>

</html>