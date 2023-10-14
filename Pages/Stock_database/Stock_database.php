<?php

require '../../vendor/autoload.php';

include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    $message = '';

    // Query to retrieve data from the database
    $sql = "SELECT * FROM stock_list"; // Replace 'your_table_name' with the actual table name
    $result = $conn->query($sql);

    $data = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
}

               
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock List</title>
    <link rel="stylesheet" href="Stock_database.css">
</head>

<body>
    <!-- Add buttons at the top of the table -->
    <div id="table-buttons">
        <button id="download-button">Download</button>
    </div>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Sr No</th>
                <th>Lot No</th>
                <th>Shape</th>
                <th>Size</th>
                <th>Pcs</th>
                <th>Wt (cts)</th>
                <th>Color</th>
                <th>Clarity</th>
                <th>Certificate</th>
                <th>Video</th>
                <th>Cut</th>
                <th>POL</th>
                <th>SYM</th>
                <th>FL</th>
                <th>M1</th>
                <th>M2</th>
                <th>M3</th>
                <th>TAB</th>
                <th>DEP</th>
                <th>Rap ($)</th>
                <th>Dis</th>
                <th>Total</th>
                <th>Price</th>
                <th>Name</th>
                <th>Average weight</th>
            </tr>
        </thead>
        <tbody id="table-body">
            <!-- JavaScript will generate rows here -->
        </tbody>
    </table>

    <script>
        // JavaScript function to populate the table with imported data
        function populateTable(importedData) {
            const tableBody = document.getElementById('table-body');

            // Clear the existing table rows
            tableBody.innerHTML = '';

            // Loop through the imported data and create rows
            for (let i = 0; i < importedData.length; i++) {
                const rowData = importedData[i];
                const newRow = document.createElement('tr');

                newRow.innerHTML = `
                <td>${i + 1}</td>
                <td>${rowData.lot_no}</td>
                <td>${rowData.shape}</td>
                <td>${rowData.size}</td>
                <td>${rowData.pcs}</td>
                <td>${rowData.Weight}</td>
                <td>${rowData.color}</td>
                <td>${rowData.clarity}</td>
                <td>${rowData.certificate_no}</td>
                <td>${rowData.cut}</td>
                <td>${rowData.pol}</td>
                <td>${rowData.sym}</td>
                <td>${rowData.fl}</td>
                <td>${rowData.m1}</td>
                <td>${rowData.m2}</td>
                <td>${rowData.m3}</td>
                <td>${rowData.tab}</td>
                <td>${rowData.dep}</td>
                <td>${rowData.ratio}</td>
                <td>${rowData.rap}</td>
                <td>${rowData.discount}</td>
                <td>${rowData.total}</td>
                <td>${rowData.price}</td>
                <td>${rowData.name}</td>
                <td>${rowData.avg_Weight}</td>
            `;

                tableBody.appendChild(newRow);
            }
        }

        // Call the populateTable function with the data retrieved from PHP
    const importedData = <?php echo json_encode($data); ?>;
    populateTable(importedData);


    document.getElementById('download-button').addEventListener('click', function () {
        window.location.href = 'download.php';
    });

     
    </script>
</body>

</html>