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
        <button id="add-button">Add</button>
        <button id="edit-button" disabled>Edit</button>
        <button id="duplicate-button" disabled>Duplicate</button>
        <button id="update-button">Update</button>
        <button id="delete-button" disabled>Delete</button>
        <button id="download-button">Download</button>
    </div>
    <div id="success-message" class="hidden">Success Message</div>
    <table class="table_data">
        <thead>
            <tr id="header">
                <!-- <th>Sr No</th> -->
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
        let selectedRow = null; // Define the selectedRow variable in a wider scope

        function showSuccessMessage(message) {
            const successMessage = document.getElementById('success-message');
            successMessage.textContent = message;
            successMessage.style.display = 'block';

            // Hide the message after 3 seconds
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 3000); // 3000 milliseconds (3 seconds)
        }

        // Function to add a new row for data entry
        function addNewRow() {
            const tableBody = document.getElementById('table-body');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
        <td contenteditable="true" data-key="lot_no"></td>
        <td contenteditable="true" data-key="shape"></td>
        <td contenteditable="true" data-key="size"></td>
        <td contenteditable="true" data-key="pcs"></td>
        <td contenteditable="true" data-key="weight"></td>
        <td contenteditable="true" data-key="color"></td>
        <td contenteditable="true" data-key="clarity"></td>
        <td contenteditable="true" data-key="certificate_no"></td>
        <td contenteditable="true" data-key="cut"></td>
        <td contenteditable="true" data-key="pol"></td>
        <td contenteditable="true" data-key="sym"></td>
        <td contenteditable="true" data-key="fl"></td>
        <td contenteditable="true" data-key="m1"></td>
        <td contenteditable="true" data-key="m2"></td>
        <td contenteditable="true" data-key="m3"></td>
        <td contenteditable="true" data-key="tab"></td>
        <td contenteditable="true" data-key="dep"></td>
        <td contenteditable="true" data-key="ratio"></td>
        <td contenteditable="true" data-key="rap"></td>
        <td contenteditable="true" data-key="discount"></td>
        <td contenteditable="true" data-key="total"></td>
        <td contenteditable="true" data-key="price"></td>
        <td contenteditable="true" data-key="name"></td>
        <td contenteditable="true" data-key="avg_Weight"></td>
    `;
            // Deselect the previously selected row, if any
            if (selectedRow) {
                selectedRow.classList.remove('selected');
            }

            // Update the selectedRow reference to the new row
            selectedRow = newRow;

            // Insert the new row after the first row (second position)
            const secondRow = tableBody.children[1]; // Note that this is zero-based index
            tableBody.insertBefore(newRow, secondRow);
        }

        // Event listener for the "Add" button
        document.getElementById('add-button').addEventListener('click', () => {
            addNewRow();
            toggleEditing();
        });


        let isEditing = false;

        // Function to toggle the "Edit" and "Duplicate" buttons
        function toggleButtons(enable) {
            const editButton = document.getElementById('edit-button');
            const duplicateButton = document.getElementById('duplicate-button');
            const deleteButton = document.getElementById('delete-button');

            if (enable) {
                editButton.removeAttribute('disabled');
                duplicateButton.removeAttribute('disabled');
                deleteButton.removeAttribute('disabled');

                editButton.classList.add('enabled');
                duplicateButton.classList.add('enabled');
                deleteButton.classList.add('enabled');
            } else {
                editButton.setAttribute('disabled', 'true');
                duplicateButton.setAttribute('disabled', 'true');
                deleteButton.setAttribute('disabled', 'true');
                editButton.classList.remove('enabled'); // Remove the "enabled" class for styling
                duplicateButton.classList.remove('enabled'); // Remove the "enabled" class for styling
                deleteButton.classList.remove('enabled'); // Remove the "enabled" class for styling

            }
        }

        // Function to toggle row selection
        function toggleRowSelection(row) {
            if (row.classList.contains('selected')) {
                return; // Do nothing if the row is already selected
            }

            // Deselect all rows
            const allRows = document.querySelectorAll('.table_data tbody tr');
            allRows.forEach((row) => {
                row.classList.remove('selected');
            });

            // Select the clicked row
            row.classList.add('selected');
            toggleButtons(true);
            selectedRow = row; // Update the selectedRow reference
        }


        // Event listener for row selection
        document.getElementById('table-body').addEventListener('click', (event) => {
            if (event.target.tagName === 'TD') {
                const row = event.target.parentElement;
                toggleRowSelection(row);
            }
        });
        // Function to toggle editing mode
        function toggleEditing() {
            const tableRows = document.querySelectorAll('.table_data tbody tr');

            tableRows.forEach((row) => {
                const cells = row.querySelectorAll('td');

                cells.forEach((cell) => {
                    if (isEditing) {
                        cell.contentEditable = 'false'; // Disable editing
                        cell.classList.remove('editable-cell');
                    } else {
                        cell.contentEditable = 'true'; // Enable editing
                        cell.classList.add('editable-cell');
                    }
                });
            });

            isEditing = !isEditing;
        }

        document.getElementById('edit-button').addEventListener('click', toggleEditing);

        document.getElementById('update-button').addEventListener('click', () => {
            if (isEditing) {
                toggleEditing(); // Disable editing before updating

                // Collect the edited data from the table
                const tableRows = document.querySelectorAll('.table_data tbody tr');
                const updatedData = [];

                tableRows.forEach((row) => {
                    const cells = row.querySelectorAll('td');
                    const rowData = {};

                    cells.forEach((cell) => {
                        rowData[cell.getAttribute('data-key')] = cell.textContent;
                    });

                    updatedData.push(rowData);
                });

                // Send the updated data to the server using fetch
                fetch('update.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(updatedData),
                })
                    .then(response => response.text())
                    .then(data => {
                        // Handle the response from the server, if needed.
                        console.log(data);
                        showSuccessMessage('Update Successful');
                    })
                    .catch(error => {
                        // Handle any errors that occur during the fetch.
                        console.error('Fetch error:', error);
                    });
            }
        });


        // Function to duplicate the selected row
        function duplicateRow() {
            if (selectedRow) {
                // Get the data from the selected row
                const selectedRowData = {};
                const cells = selectedRow.querySelectorAll('td');
                cells.forEach((cell) => {
                    selectedRowData[cell.getAttribute('data-key')] = cell.textContent;
                });

                // Create a new row and populate it with the selected row's data
                const tableBody = document.getElementById('table-body');
                const newRow = document.createElement('tr');
                newRow.innerHTML = `
            <td data-key="lot_no">${selectedRowData.lot_no}</td>
            <td data-key="shape">${selectedRowData.shape}</td>
            <td data-key="size">${selectedRowData.size}</td>
            <td data-key="pcs">${selectedRowData.pcs}</td>
            <td data-key="weight">${selectedRowData.weight}</td>
            <td data-key="color">${selectedRowData.color}</td>
            <td data-key="clarity">${selectedRowData.clarity}</td>
            <td data-key="certificate_no">${selectedRowData.certificate_no}</td>
            <td data-key="cut">${selectedRowData.cut}</td>
            <td data-key="pol">${selectedRowData.pol}</td>
            <td data-key="sym">${selectedRowData.sym}</td>
            <td data-key="fl">${selectedRowData.fl}</td>
            <td data-key="m1">${selectedRowData.m1}</td>
            <td data-key="m2">${selectedRowData.m2}</td>
            <td data-key="m3">${selectedRowData.m3}</td>
            <td data-key="tab">${selectedRowData.tab}</td>
            <td data-key="dep">${selectedRowData.dep}</td>
            <td data-key="ratio">${selectedRowData.ratio}</td>
            <td data-key="rap">${selectedRowData.rap}</td>
            <td data-key="discount">${selectedRowData.discount}</td>
            <td data-key="total">${selectedRowData.total}</td>
            <td data-key="price">${selectedRowData.price}</td>
            <td data-key="name">${selectedRowData.name}</td>
            <td data-key="avg_Weight">${selectedRowData.avg_Weight}</td>
        `;

                // Insert the new row below the selected row
                tableBody.insertBefore(newRow, selectedRow.nextElementSibling);
            }
        }

        // Event listener for the "Duplicate" button
        document.getElementById('duplicate-button').addEventListener('click', () => {
            duplicateRow();
            toggleEditing();
            showSuccessMessage('Duplicate Successful');
        });


        // Function to delete the selected row
        function deleteRow() {
            if (selectedRow) {
                // Get the index of the selected row
                const tableBody = document.getElementById('table-body');
                const rowIndex = Array.from(tableBody.children).indexOf(selectedRow);

                // Get the "Lot No" from the selected row
                const lotNo = selectedRow.querySelector('td[data-key="lot_no"]').textContent;

                // Send an AJAX request to the server to delete the row
                fetch('delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ lotNo }), // Send the "Lot No" to delete
                })
                    .then(response => response.text())
                    .then(data => {
                        // Handle the response from the server, if needed.
                        console.log(data);

                        // Remove the row from the table
                        selectedRow.remove();
                        selectedRow = null; // Clear the selectedRow reference
                    })
                    .catch(error => {
                        // Handle any errors that occur during the fetch.
                        console.error('Fetch error:', error);
                    });
            }
        }

        // Event listener for the "Delete" button
        document.getElementById('delete-button').addEventListener('click', () => {
            deleteRow();
            toggleEditing();
            showSuccessMessage('Delete Successful');
        });

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
                
                <td data-key="lot_no">${rowData.lot_no}</td>
                <td data-key="shape">${rowData.shape}</td>
                <td data-key="size">${rowData.size}</td>
                <td data-key="pcs">${rowData.pcs}</td>
                <td data-key="weight">${rowData.weight}</td>
                <td data-key="color">${rowData.color}</td>
                <td data-key="clarity">${rowData.clarity}</td>
                <td data-key="certificate_no">${rowData.certificate_no}</td>
                <td data-key="cut">${rowData.cut}</td>
                <td data-key="pol">${rowData.pol}</td>
                <td data-key="sym">${rowData.sym}</td>
                <td data-key="fl">${rowData.fl}</td>
                <td data-key="m1">${rowData.m1}</td>
                <td data-key="m2">${rowData.m2}</td>
                <td data-key="m3">${rowData.m3}</td>
                <td data-key="tab">${rowData.tab}</td>
                <td data-key="dep">${rowData.dep}</td>
                <td data-key="ratio">${rowData.ratio}</td>
                <td data-key="rap">${rowData.rap}</td>
                <td data-key="discount">${rowData.discount}</td>
                <td data-key="total">${rowData.total}</td>
                <td data-key="price">${rowData.price}</td>
                <td data-key="name">${rowData.name}</td>
                <td data-key="avg_Weight">${rowData.avg_Weight}</td>
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