<?php
include '../../connection.php';

// SQL query to retrieve data from the database
$sql = "SELECT invoice_no, `date`, customer_name, total_wt, final_total, payment_status FROM invoice_wmemo";
$result = $conn->query($sql);

// Store the retrieved data in an array
$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Fetch and populate customer names from the customers table
$sqlCustomer = "SELECT distinct customer_name FROM invoice_wmemo";
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
    <link rel="stylesheet" href="invoice_display.css">
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
            <option value="" disabled selected>Payment Status</option>
            <option value="open">Recieved</option>
            <option value="close">Not Recieved</option>
        </select>
    </div>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Invoice no.</th>
                <th>Date</th>
                <th>Customer name</th>
                <th>Total Weight</th>
                <th>Final Total</th>
                <th>Payment Recieved</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($data as $row) {
                echo '<tr>';
                echo '<td><a class="invoice-link" href="../edit_invoice/edit_invoice.html?invoice_no=' . $row['invoice_no'] . '">' . $row['invoice_no'] . '</a></td>';
        $memoDate = date('F j, Y', strtotime($row['date']));
                echo '<td>' . $memoDate . '</td>';
                echo '<td>' . $row['customer_name'] . '</td>';
                echo '<td>' . $row['total_wt'] . '</td>';
                echo '<td>' . $row['final_total'] . '</td>';
                echo '<td>' . $row['payment_status'] . '</td>';
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
        filterDropdown.addEventListener("change", filterTableInvoice);

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

        function filterTableInvoice() {
            const selectedStatus = filterDropdown.value.toLowerCase();
            const tbody = document.querySelector(".table_data tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            rows.sort((rowA, rowB) => {
                const statusCellA = rowA.querySelector("td:nth-child(6)").textContent.trim().toLowerCase();
                const statusCellB = rowB.querySelector("td:nth-child(6)").textContent.trim().toLowerCase();

                if (selectedStatus === "open") {
                    // Sort by "Recieved" first, then "Not Recieved"
                    if (statusCellA === "received" && statusCellB === "not received") {
                        return -1;
                    } else if (statusCellA === "not received" && statusCellB === "received") {
                        return 1;
                    }
                } else if (selectedStatus === "close") {
                    // Sort by "Not Recieved" first, then "Recieved"
                    if (statusCellA === "not received" && statusCellB === "received") {
                        return -1;
                    } else if (statusCellA === "received" && statusCellB === "not received") {
                        return 1;
                    }
                }
                return 0;
            });

            // Clear the table and append the sorted rows
            tbody.innerHTML = "";
            rows.forEach((row) => tbody.appendChild(row));
        }

        // JavaScript for the "Remove Filters" button
        const removeFiltersButton = document.getElementById("removeFilters");
        removeFiltersButton.addEventListener("click", function () {
            // Reload the page to remove filters
            window.location.reload();
        });
    </script>
    <a href="../landing_page/landing_page.html" class="home-button">
                <i class="fas fa-home"></i>
            </a>
</body>

</html>