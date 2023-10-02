// Fetch data from fetch_data.php using JavaScript
fetch('../php_data/fetch_data_company.php')
    .then(response => response.json())
    .then(data => {
        const companyName = document.querySelector('#title_table h2');
        const desc = document.querySelector('#title_table h4');
        const phoneNo = document.querySelector('#title_table b');
        const address = document.querySelector('#title_table #address');
        const email = document.querySelector('#title_table #email');
        const termsInvoice = document.querySelector('#terms b');

        // Set data in HTML elements
        companyName.textContent = data.company_name;
        desc.textContent = data.desc;
        phoneNo.textContent = `Cell: ${data.phone_no}`;
        address.textContent = data.address;
        email.textContent = `E: ${data.email}`;
        termsInvoice.textContent = data.terms_invoice;;
    })
    .catch(error => {
        console.error('Error:', error);
    });

// Fetch memo numbers from the PHP script and populate the dropdown
fetch('../print_memo/fetch_memo_numbers.php')
    .then(response => response.json())
    .then(data => {
        const memoDropdown = document.getElementById('memo_no');
        data.forEach(memoNo => {
            const option = document.createElement('option');
            option.value = memoNo;
            option.textContent = memoNo;
            memoDropdown.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error fetching memo numbers:', error);
    });

// Fetch the next memo number from the PHP script
fetch('generate_invoice.php')
    .then(response => response.json())
    .then(data => {
        document.getElementById('invoice_no').value = data.next_memo_no;
    })
    .catch(error => {
        console.error('Error:', error);
    });

// Get the current date in yyyy-mm-dd format
const today = new Date().toISOString().split('T')[0];

// Set the default value for the date input field
document.getElementById('date').value = today;

// Get references to HTML elements
const memoNoDropdown = document.getElementById('memo_no');
const recipientInput = document.getElementById('recipient');
const addressInput = document.getElementById('addressInput');

// Add an event listener for the memo_no dropdown change event
memoNoDropdown.addEventListener('change', () => {
    const selectedMemoNo = memoNoDropdown.value;
    if (selectedMemoNo) {
        // Perform a fetch to fetch_memo_details.php with the selected memo_no
        fetch(`../print_memo/fetch_memo_details.php?memo_no=${selectedMemoNo}`)
            .then(response => response.json())
            .then(data => {
                // Update the form fields with fetched data
                recipientInput.value = data.customer_name;
                addressInput.value = data.address;
            })
            .catch(error => console.error('Error fetching memo details:', error));
    } else {
        // Clear the form fields if no memo_no is selected
        recipientInput.value = '';
        addressInput.value = '';
    }

    // Make the fields readonly
    recipientInput.readOnly = true;
    addressInput.readOnly = true;

    fetchMemoData(memoNoDropdown.value);
});

// Function to fetch and display memo data
function fetchMemoData(memoNo) {
    console.log(memoNo);
    fetch(`../print_memo/fetch_memo_rows.php?memo_no=${memoNo}`)
        .then((response) => response.json())
        .then((data) => {
            // Loop through the data and add a row for each record
            data.forEach(record => {
                addRow(record);
            });

            totalWeightTotal();
            totalFinalTotal();
        })
        .catch((error) => {
            console.error('Error fetching data:', error);
        });
}

function totalWeightTotal() {
    const totalWtField = document.querySelector('td[name="total_wt"]');
    const wtColumns = document.querySelectorAll('td[name="wt"]');
    let totalWt = 0;

    wtColumns.forEach((wtCell) => {
        const wtValue = parseFloat(wtCell.textContent) || 0;
        totalWt += wtValue;
    });

    // Display the calculated values in the respective cells
    totalWtField.textContent = totalWt.toFixed(2);
}

function totalFinalTotal() {
    const totalTotFinalField = document.querySelectorAll('td[name="final_total"]');
    const finalTotalColumn = document.querySelector('td[name="total_final_tot"]');
    let totalFinal = 0;

    for (let i = 0; i < totalTotFinalField.length; i++) {
        const finalCell = totalTotFinalField[i];
        const finalValue = parseFloat(finalCell.textContent) || 0;
        totalFinal += finalValue;
    }

    finalTotalColumn.textContent = "US$ " + totalFinal.toFixed(2) || 0;
}

// Function to add a new row
function addRow(data) {
    const tableBody = document.getElementById('table-body');
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>${tableBody.children.length + 1}</td>
        <td name="lot_no" class="editable">${data.lot_no}</td>
        <td name="wt" class="editable wt">${data.weight}</td>
        <td name="shape" class="editable">${data.shape}</td>
        <td name="color" class="editable">${data.color}</td>
        <td name="clarity" class="editable">${data.clarity}</td>
        <td name="certificate" class="editable">${data.certificate_no}</td>
        <td name="rap" class="editable rap">${data.rap}</td>
        <td name="disc" class="editable disc">${data.discount}</td>
        <td name="price" class="editable price">${data.price}</td>
        <td name="final_total" class="editable">${data.final_total === null ? '' : data.final_total}</td>
    `;
    tableBody.appendChild(newRow);
}

// JavaScript code to handle the print button click
document.getElementById('printButton').addEventListener('click', function () {
    // Call the printMemo function to initiate printing
    printMemo();
});

// Function to initiate the printing
function printMemo() {
    saveData();

    // Hide the button when printing by applying a media query
    const style = document.createElement('style');
    style.innerHTML = '@media print { #printButton, .select-wrapper { display: none; } }';
    document.head.appendChild(style);

    // Clone the original content
    const originalContent = document.querySelector('.printable-content');
    // Generate the duplicated content here
    const originalMemoNo = document.getElementById('memo_no');
    const selectedMemoNo = originalMemoNo.value;

    const duplicateContent = originalContent.cloneNode(true);

    // Hide the duplicate content in the main display
    duplicateContent.classList.add('spacing');

    // Update the memo_no input field in the duplicated content with the selected value
    const duplicatedMemoNo = duplicateContent.querySelector('#memo_no');
    duplicatedMemoNo.value = selectedMemoNo;

    // Append the duplicate content to the body for printing
    document.body.appendChild(duplicateContent);

    // Use the window.print() method to trigger the print dialog
    window.print();

    // Remove the added style element to reset the styles
    document.head.removeChild(style);

    // Remove the duplicate content after printing
    duplicateContent.remove();
}

function saveData(){
    const data = [];
    const invoice_no = document.getElementById("invoice_no").value;
    const memo_no = document.getElementById("memo_no").value;
    const date = document.getElementById("date").value;

    const requestData = {
        invoice_no: invoice_no,
        memo_no: memo_no,
        date: date,
    };

    console.log(requestData);

    // Send the data to the server using an AJAX request
    fetch('insert_data.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData),
    })
        .then((response) => {
            if (response.ok) {
                // If the response status is OK (HTTP status 200), redirect to another page
                // window.location.href = '../print_memo/print_memo.html';
            } else {
                // Handle other response statuses here if needed
                console.error('Server returned an error:', response.statusText);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
        });
}