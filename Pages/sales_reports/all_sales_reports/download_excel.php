<?php
if (isset($_POST['excelData'])) {
    // Define the filename for the Excel file
    $filename = "customer_final_totals.csv";

    // Set appropriate headers for CSV download
    header("Content-type: text/csv");
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Output the Excel data
    echo $_POST['excelData'];
    exit();
}
?>
