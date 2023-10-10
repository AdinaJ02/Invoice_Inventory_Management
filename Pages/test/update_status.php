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
?>
