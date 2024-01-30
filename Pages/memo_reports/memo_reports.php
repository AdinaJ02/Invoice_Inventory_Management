<?php
include '../../connection.php';

// Fetch memo_no values from the memo table where is_open is 'close' and not present in the invoice table
$sqlMemo = "SELECT m.memo_no, m.memo_date, m.customer_name 
            FROM memo m
            LEFT JOIN invoice i ON m.memo_no = i.memo_no
            WHERE m.is_open = 'close' AND i.memo_no IS NULL";
$resultMemo = $conn->query($sqlMemo);

// Store memo_no, memo_date, and customer_name values in an array
$memoDetails = array();
if ($resultMemo->num_rows > 0) {
    while ($rowMemo = $resultMemo->fetch_assoc()) {
        $memoDetails[$rowMemo['memo_no']] = array(
            'memo_no' => $rowMemo['memo_no'],
            'memo_date' => $rowMemo['memo_date'],
            'customer_name' => $rowMemo['customer_name']
        );
    }
}

// Fetch shape values from the memo_data table for the retrieved memo_no values
$shapeNames = array();
foreach ($memoDetails as $memoNo => $details) {
    $sqlShape = "SELECT shape FROM memo_data WHERE memo_no = '$memoNo'";
    $resultShape = $conn->query($sqlShape);
    while ($rowShape = $resultShape->fetch_assoc()) {
        $shapeNames[] = $rowShape['shape'];
    }
}

// Fetch data for memo_no, lot_no, shape, size, kept, and final_total from memo_data
$memoData = array();
foreach ($memoDetails as $memoNo => $details) {
    $sqlMemoData = "SELECT memo_no, lot_no, shape, size, kept, final_total FROM memo_data WHERE memo_no = '$memoNo' AND (kept > 0 OR final_total > 0)";
    $resultMemoData = $conn->query($sqlMemoData);
    while ($rowMemoData = $resultMemoData->fetch_assoc()) {
        $memoData[] = array_merge($rowMemoData, $details); // Merge memo data with memo details
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
    <link rel="stylesheet" href="memo_reports.css">
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
            <option value="" selected>All Shapes</option>
            <?php
            $uniqueShapeNames = array_unique($shapeNames);
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
                <th>Memo no.</th>
                <th>Memo Date</th>
                <th>Customer name</th>
                <th>Lot no</th>
                <th>Shape</th>
                <th>Size</th>
                <th>Weight</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($memoData as $row) {
                echo '<tr class="export-row">'; // Add a class to filter rows
                echo '<td>' . $row['memo_no'] . '</td>';
                echo '<td>' . date('F j, Y', strtotime($row['memo_date'])) . '</td>';
                echo '<td>' . $row['customer_name'] . '</td>';
                echo '<td>' . $row['lot_no'] . '</td>';
                echo '<td>' . $row['shape'] . '</td>';
                echo '<td>' . $row['size'] . '</td>';
                echo '<td>' . $row['kept'] . '</td>';
                echo '<td>' . $row['final_total'] . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>

    <div class="button-container" style="text-align: center;">
        <button id="removeFilters">Remove Filters</button>
        <input type="button" value="Download Excel" id="downloadExcel">
        <input type="button" value="Back" id="goBack" onclick="goBackOneStep()">
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script>
        var memoData = <?php echo json_encode($memoData); ?>;

        // JavaScript for the "Back" button
        function goBackOneStep() {
            window.history.back(); // This will go back one step in the browser's history
        }

        // Get references to the dropdown and table
        const shapeDropdown = document.getElementById("shapeDropdown");
        const sortDropdown = document.getElementById("sortDropdown");
        const customerDropdown = document.getElementById("customerDropdown");
        const tableRows = document.querySelectorAll(".table_data tbody tr");
        const exportRows = document.querySelectorAll(".export-row"); // Add a class for export rows

        shapeDropdown.addEventListener("change", filterTable);
        sortDropdown.addEventListener("change", filterTable);
        customerDropdown.addEventListener("change", filterTable);

        function filterTable() {
            const selectedShape = shapeDropdown.value;
            const selectedSort = sortDropdown.value;
            const selectedCustomer = customerDropdown.value;

            // Initially, hide all export rows
            exportRows.forEach(row => {
                row.style.display = "none";
            });

            // Show export rows that match the selected shape, sort, and customer
            exportRows.forEach(row => {
                const shapeCell = row.querySelector("td:nth-child(5)");
                const customerCell = row.querySelector("td:nth-child(3)");

                const shapeMatch = selectedShape === "" || shapeCell.textContent === selectedShape;
                const customerMatch = selectedCustomer === "" || customerCell.textContent === selectedCustomer;

                if (shapeMatch && customerMatch) {
                    row.style.display = "table-row";
                }
            });

            // Sort export rows based on the selected criteria
            const sortedRows = Array.from(exportRows).sort((a, b) => {
                const dateA = new Date(a.querySelector("td:nth-child(2)").textContent);
                const dateB = new Date(b.querySelector("td:nth-child(2)").textContent);

                if (selectedSort === "asc") {
                    return dateA - dateB;
                } else if (selectedSort === "desc") {
                    return dateB - dateA;
                }
            });

            // Update the display order of rows in the table
            const tbody = document.querySelector(".table_data tbody");
            tbody.innerHTML = "";
            sortedRows.forEach(row => {
                tbody.appendChild(row);
            });
        }

        // JavaScript for the "Download Excel" button
        const downloadButton = document.getElementById("downloadExcel");
        downloadButton.addEventListener("click", function () {
            const selectedShape = shapeDropdown.value;
            const selectedCustomer = customerDropdown.value;
            const filteredData = memoData.filter(row =>
                (selectedShape === "" || row['shape'] === selectedShape) &&
                (selectedCustomer === "" || row['customer_name'] === selectedCustomer)
            );

            if (filteredData.length === 0) {
                alert("No data to export.");
                return;
            }

            const ws = XLSX.utils.json_to_sheet(filteredData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

            // Generate a unique file name
            const fileName = "Memo_data.xlsx";

            // Save the Excel file
            XLSX.writeFile(wb, fileName);
        });

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