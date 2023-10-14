<?php
include '../../connection.php';

// Function to update the "is_open" value in the database
function updateIsOpen($memo_no) {
    global $conn;
    $sql = "UPDATE memo SET is_open = 'Close' WHERE memo_no = '$memo_no'";
    if ($conn->query($sql) === TRUE) {
        return "Updated successfully";
    } else {
        return "Error updating record: " . $conn->error;
    }
}

// Check if the button is clicked and update the "is_open" value
if (isset($_POST['action']) && $_POST['action'] == 'closeMemo') {
    $memo_no = $_POST['memo_no'];
    $result = updateIsOpen($memo_no);
    echo $result;
    exit; // Ensure no other HTML content is sent
}

// SQL query to retrieve data from the database
$sql = "SELECT memo_no, memorandum_day, memo_date, customer_name, `address`, is_open FROM memo";
$result = $conn->query($sql);

// Store the retrieved data in an array
$data = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
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
<table class="table_data">
    <thead>
        <tr id="header">
            <th>Memo_No</th>
            <th>memorandum_day</th>
            <th>memo_date</th>
            <th>customer_name</th>
            <th>address</th>
            <th>is Open</th>
            <th>Action</th> <!-- Add a new column for the button -->
        </tr>
    </thead>
    <tbody>
        <?php
        // Output the data retrieved from the database in the table
        foreach ($data as $row) {
            echo "<tr>";
            echo "<td>" . $row['memo_no'] . "</td>";
            echo "<td>" . $row['memorandum_day'] . "</td>";
            echo "<td>" . $row['memo_date'] . "</td>";
            echo "<td>" . $row['customer_name'] . "</td>";
            echo "<td>" . $row['address'] . "</td>";
            echo "<td>" . $row['is_open'] . "</td>";
            // echo "<td><button class='close-button' data-memo_no='" . $row['memo_no'] . "'>Close</button></td>"; // Add a button with data attribute
            if ($row['is_open'] == 'open') {
                echo "<td><button class='close-button' data-memo_no='" . $row['memo_no'] . "'>Close</button></td>";
            } else {
                // Display an empty cell when the memo is closed
                echo "<td></td>";
            }
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Click event handler for the close button
        $(".close-button").click(function () {
            var memo_no = $(this).data("memo_no");
            
            // Send an AJAX request to update the "is_open" value to "Close"
            $.post('',{ action: 'closeMemo', memo_no: memo_no }, function (data) {
                // Handle the response from the server if needed
                console.log(data);
                
                // Reload the page after the update
                location.reload();
            });
        });
    });
</script>
</body>
</html>