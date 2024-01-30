<?php
include '../../connection.php';

$sql = "SELECT i.invoice_no, i.invoice_date, m.customer_name, m.total_wt, m.total_total, i.payment_status, i.manual_entry
        FROM invoice i
        JOIN memo m ON i.memo_no = m.memo_no
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
                'manual_entry' => $row['manual_entry'],
                'source' => 'invoice'
            );
        }
    }
}

$sqlInvoiceWMemo = "SELECT iw.invoice_no, iw.date AS invoice_date, iw.customer_name, iw.total_wt, iw.final_total, iw.payment_status, iw.manual_entry 
                    FROM invoice_wmemo iw
                    WHERE iw.invoice_no NOT IN (SELECT i.invoice_no FROM invoice i)
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
                'manual_entry' => $row['manual_entry'],
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
                       JOIN memo m ON i.memo_no = m.memo_no";
$resultInvoiceCustomer = $conn->query($sqlInvoiceCustomer);

// Fetch distinct customer names from the invoice_wmemo table
$sqlInvoiceWMemoCustomer = "SELECT DISTINCT customer_name FROM invoice_wmemo";
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

// Convert the PHP $data array to a JSON string
$dataJson = json_encode($data);

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
        <select class="dropdown" id="statusDropdown">
            <option value="">All Status</option>
            <option value="Received">Received</option>
            <option value="Not Received">Not Received</option>
        </select>
    </div>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Invoice no.</th>
                <th>Date</th>
                <th>Name</th>
                <th>Total Wt</th>
                <th>Total Value</th>
                <th>Payment Status</th>
                <th>Manual Entry</th>
                <th>Action</th>
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
                echo '<td contenteditable="true" class="manual-entry" data-invoice-no="' . $row['invoice_no'] . '">' . $row['manual_entry'] . '</td>';
                echo '<td><button class="save-button" data-invoice-no="' . $row['invoice_no'] . '">Save</button></td>';
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
        const statusDropdown = document.getElementById("statusDropdown");
        const tableRows = document.querySelectorAll(".table_data tbody tr");

        customerDropdown.addEventListener("change", filterTable);
        statusDropdown.addEventListener("change", filterTable);
        sortDropdown.addEventListener("change", filterTableDate);

        function filterTable() {
            const selectedCustomer = customerDropdown.value;
            const selectedStatus = statusDropdown.value;

            tableRows.forEach(row => {
                const customerNameCell = row.querySelector("td:nth-child(3)"); // Select the 3rd column (customer name)
                const statusCell = row.querySelector("td:nth-child(6)"); // Select the 6th column (payment status)

                const showRow =
                    (selectedCustomer === "" || customerNameCell.textContent === selectedCustomer || selectedCustomer === "All Customers") &&
                    (selectedStatus === "" || statusCell.textContent === selectedStatus || selectedStatus === "All Status");

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

        const data = <?php echo $dataJson; ?>;

        // Add this script to handle the save button click event
        document.addEventListener('DOMContentLoaded', function () {
            const saveButtons = document.querySelectorAll('.save-button');

            saveButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const invoiceNo = button.getAttribute('data-invoice-no');
                    const manualEntry = document.querySelector(`.manual-entry[data-invoice-no="${invoiceNo}"]`).textContent;
                    const source = getSource(invoiceNo, data);

                    // Send the data to the server using AJAX or fetch API
                    // Example using jQuery AJAX:
                    $.ajax({
                        method: 'POST',
                        url: 'save_data.php', // Replace with the actual server-side script to handle the data
                        data: { invoice_no: invoiceNo, manual_entry: manualEntry, source: source },
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

            function getSource(invoiceNo, data) {
                const foundRow = data.find(row => row.invoice_no == invoiceNo);
                return foundRow ? foundRow.source : '';
            }
        });
    </script>
    <a href="../landing_page/home_landing_page.html" class="home-button">
        <i class="fas fa-home"></i>
    </a>
</body>

</html>