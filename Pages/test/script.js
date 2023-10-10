$(document).ready(function () {
    // Function to fetch and display data from the server
    function fetchData() {
        $.get('update_status.php', function (data) {
            $('tbody').html(data);
        });
    }

    // Initial data load
    fetchData();

    // Click event handler for the close button
    $('tbody').on('click', '.close-button', function () {
        var memo_no = $(this).data('memo-no');

        // Send an AJAX request to update the "is_open" value to "Close"
        $.post('update_status.php', { action: 'closeMemo', memo_no: memo_no }, function (data) {
            // Handle the response from the server if needed
            console.log(data);

            // Refresh the data after the update
            fetchData();
        });
    });
});
