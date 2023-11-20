<?php
include '../../connection.php';

// Fetch memo_no values from the memo table where is_open is 'close'
$sqlMemo = "SELECT memo_no FROM memo WHERE is_open = 'close'";
$resultMemo = $conn->query($sqlMemo);

// Store memo_no values in an array
$closeMemoNumbers = array();
if ($resultMemo->num_rows > 0) {
    while ($rowMemo = $resultMemo->fetch_assoc()) {
        $closeMemoNumbers[] = $rowMemo['memo_no'];
    }
}

// Fetch shape values from the memo_data table for the retrieved memo_no values
$shapeNames = array();
foreach ($closeMemoNumbers as $memoNo) {
    $sqlShape = "SELECT shape FROM memo_data WHERE memo_no = '$memoNo'";
    $resultShape = $conn->query($sqlShape);
    while ($rowShape = $resultShape->fetch_assoc()) {
        $shapeNames[] = $rowShape['shape'];
    }
}

// Fetch data for memo_no, lot_no, shape, size, kept, and final_total from memo_data
$memoData = array();
foreach ($closeMemoNumbers as $memoNo) {
    $sqlMemoData = "SELECT memo_no, lot_no, shape, `size`, kept, final_total FROM memo_data WHERE memo_no = '$memoNo'";
    $resultMemoData = $conn->query($sqlMemoData);
    while ($rowMemoData = $resultMemoData->fetch_assoc()) {
        $memoData[] = $rowMemoData;
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
    <link rel="stylesheet" href="memo_reports.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

</head>

<body>
    <div class="dropdown-container">
        <select class="dropdown" id="shapeDropdown">
            <option value="" selected>All Shapes</option>
            <?php
            $uniqueShapeNames = array_unique($shapeNames);
            foreach ($uniqueShapeNames as $shapeName) {
                echo '<option value="' . $shapeName . '">' . $shapeName . '</option>';
            }
            ?>
        </select>
    </div>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Memo no.</th>
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
        const tableRows = document.querySelectorAll(".table_data tbody tr");
        const exportRows = document.querySelectorAll(".export-row"); // Add a class for export rows

        shapeDropdown.addEventListener("change", filterTable);

        function filterTable() {
            const selectedShape = shapeDropdown.value;

            // Initially, hide all export rows
            exportRows.forEach(row => {
                row.style.display = "none";
            });

            // Show export rows that match the selected shape
            if (selectedShape === "") {
                exportRows.forEach(row => {
                    row.style.display = "table-row";
                });
            } else {
                exportRows.forEach(row => {
                    const shapeCell = row.querySelector("td:nth-child(3)"); // Select the 3rd column (shape)
                    if (shapeCell.textContent === selectedShape) {
                        row.style.display = "table-row";
                    }
                });
            }
        }

        // JavaScript for the "Download Excel" button
        const downloadButton = document.getElementById("downloadExcel");
        downloadButton.addEventListener("click", function () {
            const selectedShape = shapeDropdown.value;
            const filteredData = memoData.filter(row => selectedShape === "" || row['shape'] === selectedShape);

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
    <a href="../landing_page/home_landing_page.html" class="home-button">
                <i class="fas fa-home"></i>
            </a>
</body>

</html>