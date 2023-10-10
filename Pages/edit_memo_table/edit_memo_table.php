<?php
include '../../connection.php';

// SQL query to retrieve data from the database
$sql = "SELECT memo_no, memo_date, customer_name, total_wt, total_total FROM memo";
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
    <link rel="stylesheet" href="edit_memo_table.css">
</head>
<body>
<table class="table_data">
    <thead>
        <tr id="header">
            <th>memo no.</th>
            <th>date</th>
            <th>name</th>
            <th>totalwt</th>
            <th>totalvalue</th>
            <th>select</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($data as $row) {
            ?>
            <tr>
                <td><a class="memo-link" href="../edit_memo/edit_memo.html?memo_no=<?php echo $row['memo_no']; ?>"><?php echo $row['memo_no']; ?></a></td>
                <td><?php echo $row['memo_date']; ?></td>
                <td><?php echo $row['customer_name']; ?></td>
                <td><?php echo $row['total_wt']; ?></td>
                <td><?php echo $row['total_total']; ?></td>
                <td><input type="checkbox" name="select_checkbox[]" value="<?php echo $row['memo_no']; ?>"></td>
                <!-- Added a checkbox input in the last cell -->
            </tr>
        <?php } ?>
    </tbody>
</table>
<div class="form-group" style="text-align: center;">
                <button id="printButton" class="no-print">Print Memo</button>
            </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>
</html>