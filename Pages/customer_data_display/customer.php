<?php
include '../../connection.php';

session_start();
// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header('Location: ../../index.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle edits and new data
    if (isset($_POST['action'])) { 
        $action = $_POST['action'];
        if ($action === 'edit') {
            $id = $_POST['id'];
            $customerName = $_POST['customer_name'];
            $phoneNo = $_POST['phone_no'];
            $address = $_POST['address'];

            // Update the database with edited data
            $sql = "UPDATE customers SET customer_name='$customerName', phone_no='$phoneNo', `address`='$address' WHERE id=$id";
            $conn->query($sql);
        } elseif ($action === 'add') {
            $customerName = $_POST['customer_name'];
            $phoneNo = $_POST['phone_no'];
            $address = $_POST['address'];

            // Insert new data into the database
            $sql = "INSERT INTO customers (customer_name, phone_no, `address`) VALUES ('$customerName', '$phoneNo', '$address')";
            $conn->query($sql);
        } elseif ($action === 'delete') {
            $id = $_POST['id'];

            // Delete the row from the database
            $sql = "DELETE FROM customers WHERE id=$id";
            $conn->query($sql);
            // here why it is not reseting the whole column of auto increment is because it deleting row by row
             // After deleting the row, reset the auto-increment value
             $resetSql = "ALTER TABLE customers AUTO_INCREMENT = 1";
             $conn->query($resetSql);
         } elseif ($action === 'reset_auto_increment') {
             // Reset the auto-increment value
             $resetSql = "ALTER TABLE customers AUTO_INCREMENT = 1";
             $conn->query($resetSql);
        }
    }
}

// SQL query to retrieve data from the database
$sql = "SELECT id, customer_name, phone_no, `address` FROM customers";
$result = $conn->query($sql);

// Store the retrieved data in an array
$data = array();
$srNo = 1; // Initialize serial number
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
    <link rel="stylesheet" href="customer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>
<body>
<table class="table_data">
    <thead>
        <tr id="header">
            <th>Sr No.</th>
            <th>Customer Name</th>
            <th>Phone No.</th>
            <th>Address</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // $srNo = 1;
        foreach ($data as $row) {
            ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td class="editable" contenteditable="true" data-id="<?php echo $row['id']; ?>"><?php echo $row['customer_name']; ?></td>
                <td class="editable" contenteditable="true" data-id="<?php echo $row['id']; ?>"><?php echo $row['phone_no']; ?></td>
                <td class="editable" contenteditable="true" data-id="<?php echo $row['id']; ?>"><?php echo $row['address']; ?></td>
                <td>
                    <button class="edit-btn">Edit</button>
                    <button class="save-btn" style="display: none;">Save</button>
                    <button class="delete-btn">Delete</button>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td></td>
            <td contenteditable="true" class="new-data" placeholder="Customer Name"></td>
            <td contenteditable="true" class="new-data" placeholder="Phone No."></td>
            <td contenteditable="true" class="new-data" placeholder="Address"></td>
            <td><button class="add-btn">Add the detail</button></td>
        </tr>
    </tbody>
</table>
<input type="button" value="Back" onclick="window.history.back()" class='btn-back'>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        // Edit button click event
        $('.edit-btn').click(function () {
            var row = $(this).closest('tr');
            var id = row.find('.editable').data('id');
            row.find('.editable').attr('contenteditable', 'true');
            row.find('.edit-btn').hide();
            row.find('.save-btn').show();
        });

        // Save button click event
        $('.save-btn').click(function () {
            var row = $(this).closest('tr');
            var id = row.find('.editable').data('id');
            var customerName = row.find('.editable:eq(0)').text();
            var phoneNo = row.find('.editable:eq(1)').text();
            var address = row.find('.editable:eq(2)').text();

            // Send data to the server to update in the database
            $.post('', { action: 'edit', id: id, customer_name: customerName, phone_no: phoneNo, address: address }, function () {
                row.find('.editable').attr('contenteditable', 'false');
                row.find('.edit-btn').show();
                row.find('.save-btn').hide();
            });
        });

        // Delete button click event
        $('.delete-btn').click(function () {
            var row = $(this).closest('tr');
            var id = row.find('.editable').data('id');

            // Send request to the server to delete the row from the database
            $.post('', { action: 'delete', id: id }, function () {
                row.remove();

                // After deleting the row, reset the auto-increment value
        $.post('', { action: 'reset_auto_increment' }, function () {
            // Optional: You can handle any response here if needed
        });
            });
        });

        // Add button click event
        $('.add-btn').click(function () {
            var newRow = $(this).closest('tr');
            var customerName = newRow.find('.new-data:eq(0)').text();
            var phoneNo = newRow.find('.new-data:eq(1)').text();
            var address = newRow.find('.new-data:eq(2)').text();
            
            // Check for empty values
        if (customerName.trim() === '' || phoneNo.trim() === '' || address.trim() === '') {
        return;
        }

            // Send data to the server to insert into the database
            $.post('', { action: 'add', customer_name: customerName, phone_no: phoneNo, address: address }, function () {
                location.reload();
            });
        });
    });
</script>

<a href="../landing_page/home_landing_page.php" class="home-button">
                <i class="fas fa-home"></i>
            </a>
</body>
</html>