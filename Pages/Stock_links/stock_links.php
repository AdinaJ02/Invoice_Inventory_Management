<?php
require '../../vendor/autoload.php';

include '../../connection.php';

// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
} else {
    $message = '';

    if (isset($_POST['import'])) {
        $fileName = $_FILES['fileToUpload']['name'];
        $file_tmp = $_FILES['fileToUpload']['tmp_name'];
        $file_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // Get the file extension

        // Define the allowed file extensions
        $allowed_extensions = array('xls', 'xlsx');

        if (in_array($file_ext, $allowed_extensions)) {
            move_uploaded_file($file_tmp, 'uploads/' . $fileName);

            $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            $spreadSheet = $Reader->load("uploads/" . $fileName);
            $excelSheet = $spreadSheet->getActiveSheet();
            $spreadSheetAry = $excelSheet->toArray();
            $sheetCount = count($spreadSheetAry);

            // Assuming the first row contains headers, you can start from the second row (index 1).
            for ($row = 1; $row < $sheetCount; $row++) {
                $rowData = $spreadSheetAry[$row];

                // Assuming the columns in your Excel file match the database columns in order.
                $lot_no = $rowData[0];
                $certificate = $rowData[1];
                $video = $rowData[2];

                // Define your SQL query using INSERT INTO ... ON DUPLICATE KEY UPDATE
                $sql = "INSERT INTO stock_certificate (lot_no, `certificate`, `video`) 
                VALUES ('$lot_no', '$certificate', '$video') 
                ON DUPLICATE KEY UPDATE 
                certificate = VALUES(certificate), `video` = VALUES(`video`)";

                if ($conn->query($sql) === TRUE) {
                    $message = "<p style='color:green;'>Record inserted successfully</p>";
                } else {
                    $message = "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
                }

                // Store the imported data in an array
                $importedData[] = array(
                    'lot_no' => $lot_no,
                    'certificate' => $certificate,
                    'video' => $video,
                );
            }
        } else {
            $message = "<p style='color:red;'>Invalid file format. Allowed file extensions: xls, xlsx</p>";
        }
    }
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock List</title>
    <link rel="stylesheet" href="stock_links.css">
</head>

<body>
    <form method="post" enctype="multipart/form-data">
        Select Excel file to upload:
        <input type="file" name="fileToUpload" id="fileToUpload" class='uploadbutton'>
        <input type="submit" value="Upload File" name="import" class='btn-login' id='do-login'>
        <input type="button" value="Back" onclick="window.history.back()" class='btn-back'>
        <div id='message'>
            <?php echo $message;
            ?>
        </div>
    </form>
    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Lot No</th>
                <th>Certificate</th>
                <th>video</th>
            </tr>
        </thead>
        <tbody id="table-body">
            <!-- JavaScript will generate rows here -->
        </tbody>
    </table>

    <script>
        // JavaScript function to populate the table with imported data
        function populateTable(importedData) {
            const tableBody = document.getElementById('table-body');

            // Clear the existing table rows
            tableBody.innerHTML = '';

            // Loop through the imported data and create rows
            for (let i = 0; i < importedData.length; i++) {
                const rowData = importedData[i];
                const newRow = document.createElement('tr');

                newRow.innerHTML = `
                <td>${rowData.lot_no}</td>
                <td><a href="${rowData.certificate}" target="_blank">${rowData.certificate}</a></td>
                    <td><a href="${rowData.video}" target="_blank">${rowData.video}</a></td>
            `;

                tableBody.appendChild(newRow);
            }
        }

        // Call the populateTable function with your imported data
        const importedData = <?php echo json_encode($importedData); ?>;
        populateTable(importedData);
    </script>
</body>

</html>