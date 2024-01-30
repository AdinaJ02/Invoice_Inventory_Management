<?php
include '../../connection.php';

// Fetch memo_no values from the memo table where is_open is 'close'
$sqlInvoice = "SELECT invoice_no FROM invoice_wmemo WHERE payment_status = 'Received'";
$resultInvoice = $conn->query($sqlInvoice);

// Store memo_no values in an array
$closeInvoiceNumbers = array();
if ($resultInvoice->num_rows > 0) {
    while ($rowMemo = $resultInvoice->fetch_assoc()) {
        $closeInvoiceNumbers[] = $rowMemo['invoice_no'];
    }
}

// Fetch shape values from the memo_data table for the retrieved memo_no values
$shapeNames = array();
foreach ($closeInvoiceNumbers as $invoiceNo) {
    $sqlShape = "SELECT distinct shape FROM invoice_data WHERE invoice_no = '$invoiceNo'";
    $resultShape = $conn->query($sqlShape);
    while ($rowShape = $resultShape->fetch_assoc()) {
        $shapeNames[] = $rowShape['shape'];
    }
}

// Fetch data for memo_no, lot_no, shape, size, kept, and final_total from memo_data
$invoiceData = array();
foreach ($closeInvoiceNumbers as $invoiceNo) {
    $sqlInvoiceData = "SELECT id.invoice_no, id.lot_no, id.shape, id.total, iw.date, iw.customer_name FROM invoice_data id INNER JOIN invoice_wmemo iw ON id.invoice_no = iw.invoice_no WHERE id.invoice_no = '$invoiceNo'";
    $resultInvoiceData = $conn->query($sqlInvoiceData);
    while ($rowInvoiceData = $resultInvoiceData->fetch_assoc()) {
        $invoiceData[] = $rowInvoiceData;
    }
}

// Fetch invoice_no values from the invoice table
$sqlInvoiceNo = "SELECT invoice_no FROM invoice";
$resultInvoiceNo = $conn->query($sqlInvoiceNo);

// Store invoice_no values in an array
$invoiceNumbers = array();
if ($resultInvoiceNo->num_rows > 0) {
    while ($rowInvoiceNo = $resultInvoiceNo->fetch_assoc()) {
        $invoiceNumbers[] = $rowInvoiceNo['invoice_no'];
    }
}

// Fetch associated memo_no values from the invoice_wmemo table based on the invoice_no
$associatedMemoNumbers = array();
foreach ($invoiceNumbers as $invoiceNo) {
    $sqlAssociatedMemo = "SELECT memo_no FROM invoice WHERE invoice_no = '$invoiceNo'";
    $resultAssociatedMemo = $conn->query($sqlAssociatedMemo);
    while ($rowAssociatedMemo = $resultAssociatedMemo->fetch_assoc()) {
        $associatedMemoNumbers[] = $rowAssociatedMemo['memo_no'];
    }
}

// Fetch data for lot_no, shape, size, and final_total from memo_data based on associated memo_no values
$additionalInvoiceData = array();
foreach ($associatedMemoNumbers as $memoNo) {
    // Fetch the associated invoice_no
    $sqlAssociatedInvoice = "SELECT invoice_no FROM invoice WHERE memo_no = '$memoNo'";
    $resultAssociatedInvoice = $conn->query($sqlAssociatedInvoice);
    $rowAssociatedInvoice = $resultAssociatedInvoice->fetch_assoc();
    $invoiceNo = $rowAssociatedInvoice['invoice_no'];

    // Fetch memo_date and customer_name from the memo table based on memo_no
    $sqlMemoInfo = "SELECT memo_date, customer_name FROM memo WHERE memo_no = '$memoNo'";
    $resultMemoInfo = $conn->query($sqlMemoInfo);
    $rowMemoInfo = $resultMemoInfo->fetch_assoc();
    $memoDate = $rowMemoInfo['memo_date'];
    $customerName = $rowMemoInfo['customer_name'];

    $sqlMemoData = "SELECT lot_no, shape, `size`, final_total FROM memo_data WHERE memo_no = '$memoNo'";
    $resultMemoData = $conn->query($sqlMemoData);
    while ($rowMemoData = $resultMemoData->fetch_assoc()) {
        // Add the associated invoice_no, memo_date, and customer_name to the result
        $rowMemoData['invoice_no'] = $invoiceNo;
        $rowMemoData['memo_date'] = $memoDate;
        $rowMemoData['customer_name'] = $customerName;
        $additionalInvoiceData[] = $rowMemoData;
    }

    // Fetch shape names from memo_data and add them to the shapeNames array
    $sqlMemoShape = "SELECT DISTINCT shape FROM memo_data WHERE memo_no = '$memoNo'";
    $resultMemoShape = $conn->query($sqlMemoShape);
    while ($rowMemoShape = $resultMemoShape->fetch_assoc()) {
        $shapeNames[] = $rowMemoShape['shape']; // Add shape to the shapeNames array
    }
}

// Filter out duplicate shape names
$uniqueShapeNames = array_unique($shapeNames);

$groupedData = [];
foreach ($additionalInvoiceData as $row) {
    $invoiceNo = $row['invoice_no'];
    if (!isset($groupedData[$invoiceNo])) {
        $groupedData[$invoiceNo] = [];
    }
    $groupedData[$invoiceNo][] = $row;
}

foreach ($invoiceData as $row) {
    $invoiceNo = $row['invoice_no'];
    if (!isset($groupedData[$invoiceNo])) {
        $groupedData[$invoiceNo] = [];
    }
    $groupedData[$invoiceNo][] = $row;
}

$excelData = array_merge($additionalInvoiceData, $invoiceData);

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
    <link rel="stylesheet" href="invoice_reports.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>

<body>
    <div class="dropdown-container">
        <select class="dropdown" id="customerDropdown">
            <option value="">All Customers</option>
            <?php
            // Populate the dropdown with distinct customer names
            foreach ($customerNames as $customerName) {
                echo "<option value=\"$customerName\">$customerName</option>";
            }
            ?>
        </select>

        <select class="dropdown" id="shapeDropdown">
            <option value="">All Shape</option>
            <?php
            foreach ($uniqueShapeNames as $shapeName) {
                echo '<option value="' . $shapeName . '">' . $shapeName . '</option>';
            }
            ?>
        </select>

        <select class="dropdown" id="sortDropdown">
            <option value="" selected>Sort by</option>
            <option value="asc">Date Ascending</option>
            <option value="desc">Date Descending</option>
        </select>
    </div>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Invoice no.</th>
                <th>Date</th>
                <th>Customer Name</th>
                <th>Lot no</th>
                <th>Shape</th>
                <th>Size</th>
                <th>Sale</th>
            </tr>
        </thead>
        <tbody id="filteredTableBody">
            <?php
            // Initialize an array to keep track of rowspan cells
            $rowspanCells = array();

            foreach ($groupedData as $invoiceNo => $rows) {
                // Calculate the number of rows for this invoice
                $rowspan = count($rows);

                echo '<tr class="export-row">';

                if (!in_array($invoiceNo, $rowspanCells)) {
                    // Check if the invoice hasn't been merged yet
                    echo '<td rowspan="' . $rowspan . '">' . $invoiceNo . '</td>';
                    array_push($rowspanCells, $invoiceNo);
                } else {
                    echo '<td></td>'; // Empty cell for merged invoices
                }

                // Initialize a flag to check if the first row has been processed
                $firstRowProcessed = false;

                foreach ($rows as $index => $row) {
                    // Skip the first row, as it's already been processed
                    if ($firstRowProcessed) {
                        echo '<tr class="export-row">';
                    }

                    echo '<td>';
                    if (isset($row['date'])) {
                        $formattedDate = date('F j, Y', strtotime($row['date']));
                        echo $formattedDate;
                    } elseif (isset($row['memo_date'])) {
                        $formattedDate = date('F j, Y', strtotime($row['memo_date']));
                        echo $formattedDate;
                    }
                    echo '</td>';
                    echo '<td>';
                    if (isset($row['customer_name'])) {
                        echo $row['customer_name'];
                    }
                    echo '</td>';
                    echo '<td>' . $row['lot_no'] . '</td>';
                    echo '<td>' . $row['shape'] . '</td>';
                    echo '<td>';
                    if (isset($row['size'])) {
                        echo $row['size'];
                    }
                    echo '</td>';
                    echo '<td>';
                    if (isset($row['final_total'])) {
                        echo $row['final_total'];
                    } elseif (isset($row['total'])) {
                        echo $row['total'];
                    }
                    echo '</td>';
                    echo '</tr>';

                    // Mark that the first row has been processed
                    $firstRowProcessed = true;
                }
            }
            ?>
        </tbody>
    </table>

    <div class="button-container" style="text-align: center;">
        <button id="removeFilters">Remove Filters</button>
        <input type="button" value="Download Excel" id="downloadExcel">
        <input type="button" value="Back" id="goBack" onclick="goBackOneStep()">
    </div>
    <!-- Existing JavaScript code -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.2/xlsx.full.min.js"></script>
    <script>
        var invoiceData = <?php echo json_encode($invoiceData); ?>;
        var additionalInvoiceData = <?php echo json_encode($additionalInvoiceData); ?>;
        var excelData = <?php echo json_encode(array_merge($additionalInvoiceData, $invoiceData)); ?>;

        // JavaScript for the "Back" button
        function goBackOneStep() {
            window.history.back(); // This will go back one step in the browser's history
        }

        // Get references to the dropdown and table body
        const shapeDropdown = $("#shapeDropdown");
        const sortDropdown = $("#sortDropdown");
        const customerDropdown = $("#customerDropdown");
        const filteredTableBody = $("#filteredTableBody");

        shapeDropdown.on("change", filterTable);
        sortDropdown.on("change", filterTable);
        customerDropdown.on("change", filterTable);

        function filterTable() {
            const selectedShape = shapeDropdown.val();
            const selectedSort = sortDropdown.val();
            const selectedCustomer = customerDropdown.val();

            // Filter the data based on the selected shape for invoiceData
            const filteredData = invoiceData.filter(row =>
                (!selectedShape || row.shape === selectedShape) &&
                (!selectedCustomer || row.customer_name === selectedCustomer)
            );

            // Filter the data based on the selected shape for additionalInvoiceData
            const filteredData_add = additionalInvoiceData.filter(row =>
                (!selectedShape || row.shape === selectedShape) &&
                (!selectedCustomer || row.customer_name === selectedCustomer)
            );

            // Merge the filtered data from both sets
            let mergedData = [...filteredData_add, ...filteredData];

            // Sort the merged data by date if "Sort by" is selected
            if (selectedSort) {
                mergedData.sort((a, b) => {
                    const dateA = new Date(a.date || a.memo_date);
                    const dateB = new Date(b.date || b.memo_date);

                    return selectedSort === "asc" ? dateA - dateB : dateB - dateA;
                });
            }

            // Update the displayed table
            displayTableRows(mergedData);
        }

        function displayTableRows(data) {
            // Clear the table body
            filteredTableBody.empty();



            // Populate the table with filtered data
            data.forEach(row => {
                const formattedDate = row.date ? new Date(row.date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) :
                    (row.memo_date ? new Date(row.memo_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : '');

                const newRow = "<tr>" +
                    "<td>" + row.invoice_no + "</td>" +
                    "<td>" + formattedDate + "</td>" +
                    "<td>" + row.customer_name + "</td>" +
                    "<td>" + row.lot_no + "</td>" +
                    "<td>" + row.shape + "</td>" +
                    "<td>" + (row.size ?? '') + "</td>" +
                    "<td>" + (row.final_total ?? row.total ?? '') + "</td>" +
                    "</tr>";

                filteredTableBody.append(newRow);
            });
        }

        // Function to download data as an Excel file
        // Function to download data as an Excel file
        function downloadExcelData() {
            const selectedShape = shapeDropdown.val();
            const selectedCustomer = customerDropdown.val();
            const selectedSort = sortDropdown.val();

            // Filter the data based on the selected shape for invoiceData
            const filteredData = invoiceData.filter(row =>
                (!selectedShape || row.shape === selectedShape) &&
                (!selectedCustomer || row.customer_name === selectedCustomer)
            );

            // Filter the data based on the selected shape for additionalInvoiceData
            const filteredData_add = additionalInvoiceData.filter(row =>
                (!selectedShape || row.shape === selectedShape) &&
                (!selectedCustomer || row.customer_name === selectedCustomer)
            );  

            // Merge the filtered data from both sets
            let mergedData = [...filteredData_add, ...filteredData];

            // Sort the merged data by date if "Sort by" is selected
            if (selectedSort) {
                mergedData.sort((a, b) => {
                    const dateA = new Date(a.date || a.memo_date);
                    const dateB = new Date(b.date || b.memo_date);

                    return selectedSort === "asc" ? dateA - dateB : dateB - dateA;
                });
            }

            // Create a worksheet
            const ws = XLSX.utils.json_to_sheet(mergedData);

            // Create a workbook
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Filtered Data");

            // Generate the Excel file
            XLSX.writeFile(wb, 'filtered_data.xlsx');
        }

        // Attach the downloadExcelData function to the "Download Excel" button
        $("#downloadExcel").on("click", downloadExcelData);

        // JavaScript for the "Remove Filters" button
        const removeFiltersButton = document.getElementById("removeFilters");
        removeFiltersButton.addEventListener("click", function () {
            // Reload the page to remove filters
            window.location.reload();
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
    <a href="../landing_page/home_landing_page.html" class="home-button">
        <i class="fas fa-home"></i>
    </a>
</body>

</html>