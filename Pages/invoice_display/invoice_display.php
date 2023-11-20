<?php
include '../../connection.php';

$sql = "SELECT i.invoice_no, i.invoice_date, m.customer_name, m.total_wt, m.total_total, i.payment_status 
        FROM invoice i
        JOIN memo m ON i.memo_no = m.memo_no
        AND i.payment_status = 'Received'
        ORDER BY i.invoice_no ASC";
$result = $conn->query($sql);

$data = array();
if ($result === false) {
    echo "Error: " . $conn->error;
} else {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = array(
                'invoice_no' => $row['invoice_no'],
                'invoice_date' => $row['invoice_date'],
                'customer_name' => $row['customer_name'],
                'total_wt' => $row['total_wt'],
                'total_total' => $row['total_total'],
                'payment_status' => $row['payment_status'],
                'source' => 'invoice'
            );
        }
    }
}

$sqlInvoiceWMemo = "SELECT iw.invoice_no, iw.date AS invoice_date, iw.customer_name, iw.total_wt, iw.final_total, iw.payment_status 
                    FROM invoice_wmemo iw
                    WHERE iw.invoice_no NOT IN (SELECT i.invoice_no FROM invoice i)
                    AND iw.payment_status = 'Received'
                    ORDER BY iw.invoice_no ASC";
$resultInvoiceWMemo = $conn->query($sqlInvoiceWMemo);

if ($resultInvoiceWMemo === false) {
    echo "Error: " . $conn->error;
} else {
    if ($resultInvoiceWMemo->num_rows > 0) {
        while ($row = $resultInvoiceWMemo->fetch_assoc()) {
            $data[] = array(
                'invoice_no' => $row['invoice_no'],
                'invoice_date' => $row['invoice_date'],
                'customer_name' => $row['customer_name'],
                'total_wt' => $row['total_wt'],
                'total_total' => $row['final_total'],
                'payment_status' => $row['payment_status'],
                'source' => 'invoice_wmemo'
            );
        }
    }
}

// Sort the $data array by invoice_no in ascending order
usort($data, function ($a, $b) {
    return $a['invoice_no'] - $b['invoice_no'];
});

// Fetch distinct customer names from the invoice table
$sqlInvoiceCustomer = "SELECT DISTINCT m.customer_name 
                       FROM invoice i
                       JOIN memo m ON i.memo_no = m.memo_no
                       AND i.payment_status = 'Received'";
$resultInvoiceCustomer = $conn->query($sqlInvoiceCustomer);

// Fetch distinct customer names from the invoice_wmemo table
$sqlInvoiceWMemoCustomer = "SELECT DISTINCT customer_name FROM invoice_wmemo where payment_status='Received'";
$resultInvoiceWMemoCustomer = $conn->query($sqlInvoiceWMemoCustomer);

// Store customer names in an array
$customerNames = array();

// Fetch and store customer names from the invoice table
while ($rowInvoiceCustomer = $resultInvoiceCustomer->fetch_assoc()) {
    $customerNames[] = $rowInvoiceCustomer['customer_name'];
}

// Fetch and store customer names from the invoice_wmemo table
while ($rowInvoiceWMemoCustomer = $resultInvoiceWMemoCustomer->fetch_assoc()) {
    $customerNames[] = $rowInvoiceWMemoCustomer['customer_name'];
}

// Filter out duplicate customer names and keep only distinct ones
$distinctCustomerNames = array_unique($customerNames);

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
            foreach ($distinctCustomerNames as $customerName) {
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
                <th>Payment Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($data as $row) {
                // Skip rows with total_total equal to 0.0
                if ($row['total_total'] == 0.0) {
                    continue;
                }

                echo '<tr>';
                // Check the source of invoice_no and provide the appropriate link
                $editLink = ($row['source'] === 'invoice_wmemo') ?
                    '../edit_invoice/edit_invoice.html' :
                    '../edit_invoice_memo/edit_invoice_memo.html';

                echo '<td><a class="invoice-link" href="' . $editLink . '?invoice_no=' . $row['invoice_no'] . '">' . $row['invoice_no'] . '</a></td>';
                $invoiceDate = date('F j, Y', strtotime($row['invoice_date']));
                echo '<td>' . $invoiceDate . '</td>';
                echo '<td>' . $row['customer_name'] . '</td>';
                echo '<td>' . $row['total_wt'] . '</td>';
                echo '<td>' . $row['total_total'] . '</td>';
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

    <a href="../landing_page/landing_page.html" class="home-button">
        <i class="fas fa-home"></i>
    </a>

</body>

</html>