<?php
include '../../connection.php';

// SQL query to retrieve data from the database
$sql = "SELECT memo_no, memo_date, customer_name, total_wt, total_total FROM memo where is_open='open'";
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

// // Calculate total values
// $totalWt = 0;
// $totalValue = 0;

// foreach ($data as $row) {
//     $totalWt += $row['total_wt'];
//     $totalValue += $row['total_total'];
// }

// // Add a total row to the data array
// $totalRow = array(
//     'memo_no' => 'Total',
//     'memo_date' => '',
//     'customer_name' => '',
//     'total_wt' => $totalWt,
//     'total_total' => $totalValue
// );

// // Append the total row to the data array
// $data[] = $totalRow;

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="edit_memo_table.css">
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
    </div>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Memo No.</th>
                <th>Date</th>
                <th>Name</th>
                <th>Total Wt</th>
                <th>Total Value</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($data as $row) {
                // Exclude the total row with a random date
                if ($row['memo_no'] === 'Total') {
                    continue;
                }
                echo '<tr>';
                echo '<td><a class="memo-link" href="../edit_memo/edit_memo.html?memo_no=' . $row['memo_no'] . '">' . $row['memo_no'] . '</a></td>';
                $memoDate = date('F j, Y', strtotime($row['memo_date']));
                echo '<td>' . $memoDate . '</td>';
                echo '<td>' . $row['customer_name'] . '</td>';
                echo '<td>' . $row['total_wt'] . '</td>';
                echo '<td>' . $row['total_total'] . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
        <!-- Add a total row at the end of the table -->
        <tfoot>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td>
                    <b id="totalWt">0.00</b>
                </td>
                <td>
                    <b id="totalValue">0.00</b>
                </td>
            </tr>
        </tfoot>
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

        // Reload the page when the history changes (e.g., after using window.history.back())
        window.addEventListener("popstate", function (event) {
            location.reload();
        });

        document.addEventListener("DOMContentLoaded", function () {
            // Get references to the dropdown and table
            const customerDropdown = document.getElementById("customerDropdown");
            const sortDropdown = document.getElementById("sortDropdown");
            const tableRows = document.querySelectorAll(".table_data tbody tr");

            customerDropdown.addEventListener("change", filterTable);
            sortDropdown.addEventListener("change", filterTableDate);

            function calculateTotals() {
                let totalWt = 0;
                let totalValue = 0;

                tableRows.forEach(row => {
                    const wtCell = row.querySelector("td:nth-child(4)"); // Select the 4th column (total weight)
                    const valueCell = row.querySelector("td:nth-child(5)"); // Select the 5th column (total value)

                    // Check if the row is visible
                    if (row.style.display !== "none") {
                        totalWt += parseFloat(wtCell.textContent) || 0;
                        totalValue += parseFloat(valueCell.textContent) || 0;
                    }
                });

                // Display the totals in the footer
                const totalWtCell = document.getElementById("totalWt");
                const totalValueCell = document.getElementById("totalValue");

                totalWtCell.textContent = totalWt.toFixed(2);
                totalValueCell.textContent = totalValue.toFixed(2);
            }


            function filterTable() {
                const selectedCustomer = customerDropdown.value;

                tableRows.forEach(row => {
                    const customerNameCell = row.querySelector("td:nth-child(3)"); // Select the 3rd column (customer name)
                    const showRow = selectedCustomer === "" || customerNameCell.textContent === selectedCustomer || selectedCustomer === "All Customers";
                    row.style.display = showRow ? "table-row" : "none";
                });

                // Recalculate totals after filtering
                calculateTotals();
            }

            function filterTableDate() {
                const sortOption = sortDropdown.value;
                const tbody = document.querySelector(".table_data tbody");

                if (sortOption === "date-asc" || sortOption === "date-desc") {
                    // Sort the rows based on the selected option
                    const sortOrder = (sortOption === "date-asc") ? 1 : -1;

                    const sortedRows = Array.from(tableRows).sort((a, b) => {
                        const dateA = new Date(a.querySelector("td:nth-child(2)").textContent);
                        const dateB = new Date(b.querySelector("td:nth-child(2)").textContent);
                        return sortOrder * (dateA - dateB);
                    });

                    // Append the sorted rows to the tbody
                    sortedRows.forEach(row => tbody.appendChild(row));
                }

                // Recalculate totals after sorting
                calculateTotals();
            }

            // Initial calculation of totals when the page loads
            calculateTotals();
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