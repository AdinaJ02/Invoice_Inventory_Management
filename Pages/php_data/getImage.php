<?php
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'nfj';

// Create a connection
$conn = new mysqli( $servername, $username, $password, $dbname );

// Check the connection
if ( $conn->connect_error ) {
    die( 'Connection failed: ' . $conn->connect_error );
}

// Retrieve image data from the database ( replace 'your_table' and 'your_id' with your table and ID )
$sql = 'SELECT logo FROM company_info';
$result = $conn->query( $sql );

if ( $result && $result->num_rows > 0 ) {
    $row = $result->fetch_assoc();

    // Set appropriate headers for image display
    header( 'Content-Type: image/jpeg' );
    // Replace with the appropriate image format ( e.g., image/png )

    // Output the image data
    echo $row[ 'logo' ];
} else {
    echo 'Image not found';
}

// Close the database connection
$conn->close();
?>
