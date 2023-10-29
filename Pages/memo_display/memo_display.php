<?php
include '../../connection.php';

// SQL query to retrieve data from the database
$sql = "SELECT memo_no, memo_date, customer_name, total_wt, total_total, is_open FROM memo";
$result = $conn->query($sql);

// Store the retrieved data in an array
$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

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
            <option value="" disabled selected>Memo Status</option>
            <option value="open">Open</option>
            <option value="close">Close</option>
        </select>
    </div>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Memo no.</th>
                <th>Date</th>
                <th>Customer name</th>
                <th>Total Weight</th>
                <th>Final Total</th>
                <th>Is Open</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($data as $row) {
                echo '<tr>';
                echo '<td>' . $row['memo_no'] . '</td>';
                $memoDate = date('F j, Y', strtotime($row['memo_date']));
                echo '<td>' . $memoDate . '</td>';
                echo '<td>' . $row['customer_name'] . '</td>';
                echo '<td>' . $row['total_wt'] . '</td>';
                echo '<td>' . $row['total_total'] . '</td>';
                echo '<td>' . $row['is_open'] . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
    <div class="form-group" style="text-align: center;">
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

            tableRows.forEach(row => {
                const customerNameCell = row.querySelector("td:nth-child(3)"); // Select the 3rd column (customer name)
                const showRow = selectedCustomer === "" || customerNameCell.textContent === selectedCustomer || selectedCustomer === "All Customers";
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

            rows.sort((rowA, rowB) => {
                const statusCellA = rowA.querySelector("td:nth-child(6)").textContent.toLowerCase();
                const statusCellB = rowB.querySelector("td:nth-child(6)").textContent.toLowerCase();

                if (selectedCustomer === "" || rowA.querySelector("td:nth-child(3)").textContent === selectedCustomer) {
                    if (selectedStatus === "open") {
                        if (statusCellA === "open" && statusCellB === "close") {
                            return -1;
                        } else if (statusCellA === "close" && statusCellB === "open") {
                            return 1;
                        }
                    } else if (selectedStatus === "close") {
                        if (statusCellA === "close" && statusCellB === "open") {
                            return -1;
                        } else if (statusCellA === "open" && statusCellB === "close") {
                            return 1;
                        }
                    }
                } else {
                    return 0;
                }
            });

            // Clear the table and append the sorted rows
            tbody.innerHTML = "";
            rows.forEach((row) => tbody.appendChild(row));
        }
    </script>
</body>

</html>