<?php
include '../../connection.php';

// SQL query to retrieve data from the database
$sql = "SELECT invoice_no, `date`, customer_name, total_wt, final_total FROM invoice_wmemo";
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
    <link rel="stylesheet" href="edit_invoice_table.css">
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
    </div>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>invoice no.</th>
                <th>date</th>
                <th>name</th>
                <th>totalwt</th>
                <th>totalvalue</th>
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
        const tableRows = document.querySelectorAll(".table_data tbody tr");

        customerDropdown.addEventListener("change", filterTable);
        sortDropdown.addEventListener("change", filterTableDate);

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

    </script>
</body>

</html>