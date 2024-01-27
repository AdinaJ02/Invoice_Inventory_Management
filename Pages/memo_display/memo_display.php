<?php
include '../../connection.php';

// SQL query to retrieve data from the database
$sql = "SELECT memo_no, memo_date, customer_name, total_wt, total_total, is_open, manual_entry, `status` FROM memo";
$result = $conn->query($sql);

// Store the retrieved data in an array
$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Sort the data array by customer name in alphabetical order
usort($data, function ($a, $b) {
    return strcmp($a['customer_name'], $b['customer_name']);
});

// Fetch and populate customer names from the customers table
$sqlCustomer = "SELECT distinct customer_name FROM memo";
$resultCustomer = $conn->query($sqlCustomer);

// Store customer names in an array
$customerNames = array();
while ($rowCustomer = $resultCustomer->fetch_assoc()) {
    $customerNames[] = $rowCustomer['customer_name'];
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="memo_display.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>

<body>
    <div class="dropdown-container">
        <select class="dropdown" id="customerDropdown">
            <option value="">All Customers</option>
            <?php
            foreach ($customerNames as $customerName) {
                echo '<option value="' . $customerName . '">' . $customerName . '</option>';
            }
            ?>
        </select>
        <select class="dropdown" id="sortDropdown">
            <option value="" disabled selected>Sort By</option>
            <option value="date-asc">Date Ascending</option>
            <option value="date-desc">Date Descending</option>
        </select>
        <select class="dropdown" id="statusDropdown">
            <option value="" selected>Memo Status</option>
            <option value="open">Open</option>
            <option value="close">Close</option>
        </select>
        <button id="download-button">Download</button>
    </div>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Memo no.</th>
                <th>Date</th>
                <th>Customer Name</th>
                <th>Total Weight</th>
                <th>Final Total</th>
                <th>Is Open</th>
                <th>Manual Entry</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($data as $row) {
                echo '<tr>';
                echo '<td><a class="memo-link" href="../edit_memo/edit_memo.html?memo_no=' . $row['memo_no'] . '">' . $row['memo_no'] . '</a></td>';
                $memoDate = date('F j, Y', strtotime($row['memo_date']));
                echo '<td>' . $memoDate . '</td>';
                echo '<td>' . $row['customer_name'] . '</td>';
                echo '<td>' . $row['total_wt'] . '</td>';
                echo '<td>' . $row['total_total'] . '</td>';
                echo '<td>' . $row['is_open'] . '</td>';
                echo '<td contenteditable="true" class="manual-entry" data-memo-no="' . $row['memo_no'] . '">' . $row['manual_entry'] . '</td>';
                echo '<td><select class="status-dropdown" data-memo-no="' . $row['memo_no'] . '">
                <option value="invoice" ' . ($row['status'] == 'invoice' ? 'selected' : '') . '>Invoice</option>
                <option value="all_return" ' . ($row['status'] == 'all_return' ? 'selected' : '') . '>All Return</option>
                <option value="accounted" ' . ($row['status'] == 'accounted' ? 'selected' : '') . '>Accounted</option>
            </select></td>';
                echo '<td><button class="save-button" data-memo-no="' . $row['memo_no'] . '">Save</button></td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
    <div class="button-container" style="text-align: center;">
        <button id="removeFilters">Remove Filters</button>
        <input type="button" value="Back" id="goBack" onclick="goBackOneStep()">
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // JavaScript for the "Back" button
        function goBackOneStep() {
            window.history.back(); // This will go back one step in the browser's history
        }


        // Get references to the dropdown and table
        const customerDropdown = document.getElementById("customerDropdown");
        const sortDropdown = document.getElementById("sortDropdown");
        const filterDropdown = document.getElementById("statusDropdown");
        const tableRows = document.querySelectorAll(".table_data tbody tr");

        customerDropdown.addEventListener("change", filterTable);
        sortDropdown.addEventListener("change", filterTableDate);
        filterDropdown.addEventListener("change", filterTableMemo);

        function filterTable() {
            const selectedCustomer = customerDropdown.value;
            const selectedStatus = filterDropdown.value.toLowerCase();

            tableRows.forEach(row => {
                const customerNameCell = row.querySelector("td:nth-child(3)"); // Select the 3rd column (customer name)
                const statusCell = row.querySelector("td:nth-child(6)").textContent.toLowerCase();

                const showRow =
                    (selectedCustomer === "" || customerNameCell.textContent === selectedCustomer || selectedCustomer === "All Customers") &&
                    (selectedStatus === "" || statusCell === selectedStatus);

                row.style.display = showRow ? "table-row" : "none";
            });
        }

        function filterTableDate() {
            const sortOption = sortDropdown.value;
            const tbody = document.querySelector(".table_data tbody");

            if (sortOption === "date-asc") {
                // Sort the rows in ascending order by date
                const sortedRows = Array.from(tableRows).sort((a, b) => {
                    const dateA = new Date(a.querySelector("td:nth-child(2)").textContent);
                    const dateB = new Date(b.querySelector("td:nth-child(2)").textContent);
                    return dateA - dateB;
                });

                // Append the sorted rows to the tbody
                sortedRows.forEach(row => tbody.appendChild(row));
            } else if (sortOption === "date-desc") {
                // Sort the rows in descending order by date
                const sortedRows = Array.from(tableRows).sort((a, b) => {
                    const dateA = new Date(a.querySelector("td:nth-child(2)").textContent);
                    const dateB = new Date(b.querySelector("td:nth-child(2)").textContent);
                    return dateB - dateA;
                });

                // Append the sorted rows to the tbody
                sortedRows.forEach(row => tbody.appendChild(row));
            }
        }

        function filterTableMemo() {
            const selectedCustomer = customerDropdown.value;
            const selectedStatus = statusDropdown.value.toLowerCase();

            const tbody = document.querySelector(".table_data tbody");

            const rows = Array.from(tbody.querySelectorAll("tr"));

            rows.forEach(row => {
                const customerName = row.querySelector("td:nth-child(3)").textContent;
                const statusCell = row.querySelector("td:nth-child(6)").textContent.toLowerCase();

                const showRow =
                    (selectedCustomer === "" || customerName === selectedCustomer || selectedCustomer === "All Customers") &&
                    (selectedStatus === "" || statusCell === selectedStatus);

                row.style.display = showRow ? "table-row" : "none";
            });
        }

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



        // Add this script to handle the save button click event
        document.addEventListener('DOMContentLoaded', function () {
            const saveButtons = document.querySelectorAll('.save-button');

            saveButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const memoNo = button.getAttribute('data-memo-no');
                    const manualEntry = document.querySelector(`.manual-entry[data-memo-no="${memoNo}"]`).textContent;
                    const status = document.querySelector(`.status-dropdown[data-memo-no="${memoNo}"]`).value;

                    // Send the data to the server using AJAX or fetch API
                    // Example using jQuery AJAX:
                    $.ajax({
                        method: 'POST',
                        url: 'save_data.php', // Replace with the actual server-side script to handle the data
                        data: { memo_no: memoNo, manual_entry: manualEntry, status: status },
                        success: function (response) {
                            // Handle the server response if needed
                            console.log(response);
                        },
                        error: function (error) {
                            // Handle the error if needed
                            console.error(error);
                        }
                    });
                });
            });
        });

        document.getElementById('download-button').addEventListener('click', function () {
            window.location.href = 'memo_download.php';
        });

    </script>
    <a href="../landing_page/home_landing_page.html" class="home-button">
        <i class="fas fa-home"></i>
    </a>

</body>

</html>