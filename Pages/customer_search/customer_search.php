<?php
include '../../connection.php';

// Fetch and display data based on the selected customer and filters (if applicable)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedCustomer = $_POST["customer"];
    $sortBy = $_POST["sortBy"];
    $memoStatus = $_POST["memoStatus"];

    // Construct the base query for memos and invoices
    $query = "SELECT memo_no, memo_date, customer_name, total_wt, total_total, is_open, NULL AS invoice_no, NULL AS date, NULL AS final_total, NULL AS payment_status FROM memo WHERE 1=1";

    // Add filters to the query based on user selection
    if (!empty($selectedCustomer)) {
        $query .= " AND customer_name = '$selectedCustomer'";
    }

    if (!empty($memoStatus)) {
        $query .= " AND is_open = '$memoStatus'";
    }

    // Construct the query for invoices
    $query .= " UNION SELECT NULL AS memo_no, NULL AS memo_date, customer_name, total_wt, final_total, NULL AS is_open, invoice_no, date, final_total, payment_status FROM invoice_wmemo WHERE 1=1";

    // Add filters to the invoice query based on user selection
    if (!empty($selectedCustomer)) {
        $query .= " AND customer_name = '$selectedCustomer'";
    }

    // Add filters to the invoice query based on user selection
    if (!empty($memoStatus)) {
        $query .= " AND payment_status = '$memoStatus'";
    }

    // Combine the queries and add sorting
    $query .= " ORDER BY";

    if ($sortBy == "DateAscending") {
        $query .= " COALESCE(memo_date, date) ASC";
    } elseif ($sortBy == "DateDescending") {
        $query .= " COALESCE(memo_date, date) DESC";
    } else {
        // Default sorting
        $query .= " COALESCE(memo_date, date) DESC";
    }

    // Execute the combined query
    $result = $conn->query($query);

    // Check if the query was successful
    if ($result) {
        // Display the fetched data
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>";

            // Check if it's a memo or invoice
            if (!is_null($row['memo_no'])) {
                echo $row['memo_no'];
            } elseif (!is_null($row['invoice_no'])) {
                echo $row['invoice_no'];
            }

            echo "</td>";

            // Format the date as "Month Day, Year"
            $formattedDate = (!is_null($row['memo_date'])) ? date('F j, Y', strtotime($row['memo_date'])) : date('F j, Y', strtotime($row['date']));

            echo "<td>{$formattedDate}</td>";
            echo "<td>{$row['customer_name']}</td>";
            echo "<td>{$row['total_wt']}</td>";
            echo "<td>{$row['total_total']}</td>";
            echo "<td>{$row['is_open']} {$row['payment_status']}</td>";
            echo "</tr>";
        }

        // Close the result set
        $result->close();
    } else {
        // Handle the case where the query fails (you may want to log or display an error)
        echo "Error: " . $conn->error;
    }

    // Terminate the script after handling the POST request
    exit();
}

// Fetch distinct customer names from the customers table
$query = "SELECT DISTINCT customer_name FROM customers";
$result = $conn->query($query);

// Check if the query was successful
if ($result) {
    // Create an array to store customer names
    $customerNames = array();

    // Fetch each row and store customer names in the array
    while ($row = $result->fetch_assoc()) {
        $customerNames[] = $row['customer_name'];
    }

    // Close the result set
    $result->close();
} else {
    // Handle the case where the query fails (you may want to log or display an error)
    echo "Error: " . $conn->error;
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="customer_search.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>

<body>
    <div class="dropdown-container">
        <select class="dropdown" id="customerDropdown" onchange="fetchAndDisplayData()">
            <option value="">All Customers</option>
            <?php
            // Populate the dropdown with distinct customer names
            foreach ($customerNames as $customerName) {
                echo "<option value=\"$customerName\">$customerName</option>";
            }
            ?>
        </select>

        <!-- Add Sort By dropdown -->
        <select class="dropdown" id="sortBy" onchange="fetchAndDisplayData()">
            <option value="">Sort By</option>
            <option value="DateAscending">Date Ascending</option>
            <option value="DateDescending">Date Descending</option>
        </select>

        <!-- Add Memo Status dropdown -->
        <select class="dropdown" id="memoStatus" onchange="fetchAndDisplayData()">
            <option value="">All Status</option>
            <option value="open">Open</option>
            <option value="close">Close</option>
            <option value="received">Received</option>
            <option value="not received">Not Received</option>
        </select>
    </div>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Memo No. / Invoice No.</th>
                <th>Date</th>
                <th>Name</th>
                <th>Total Wt</th>
                <th>Total Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="dataBody">
        </tbody>
    </table>
    <div class="form-group" style="text-align: center;">
        <button id="removeFilters">Remove Filters</button>
        <input type="button" value="Back" id="goBack" onclick="goBackOneStep()">
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        function fetchAndDisplayData() {
            var selectedCustomer = $("#customerDropdown").val();
            var sortBy = $("#sortBy").val();
            var memoStatus = $("#memoStatus").val();

            // Make an asynchronous request to fetch data based on the selected customer and filters
            $.ajax({
                type: 'POST',
                data: {
                    customer: selectedCustomer,
                    sortBy: sortBy,
                    memoStatus: memoStatus
                },
                success: function (data) {
                    // Update the table body with the fetched data
                    $("#dataBody").html(data);
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching data: " + error);
                }
            });
        }

        // JavaScript for the "Remove Filters" button
        $(document).ready(function () {
            const removeFiltersButton = $("#removeFilters");
            removeFiltersButton.on("click", function () {
                // Reset dropdowns to default values
                $("#customerDropdown").val('');
                $("#sortBy").val('');
                $("#memoStatus").val('');

                // Reload the page to remove filters
                fetchAndDisplayData();
            });
        });
    </script>
    <script>
        document.addEventListener('contextmenu', function (e) {
            e.preventDefault();
        });
    </script>

    <a href="../landing_page/home_landing_page.html" class="home-button">
        <i class="fas fa-home"></i>
    </a>
</body>

</html>