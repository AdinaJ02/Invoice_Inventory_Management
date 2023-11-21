var currency = "";
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
        const bank_details = document.querySelector('#terms #bank_details');

        // Set data in HTML elements
        companyName.textContent = data.company_name;
        desc.textContent = data.desc;
        phoneNo.textContent = `Cell: ${data.phone_no}`;
        address.textContent = data.address;
        email.textContent = `E: ${data.email}`;
        termsInvoice.textContent = data.terms_invoice;
        currency = data.currency;
        bank_details.textContent = data.bank_details;
    })
    .catch(error => {
        console.error('Error:', error);
    });

// Assuming this code is in a function or script block
function fetchInvoiceData() {
    const memo_no = getQueryParam('memo_no');

    console.log(`generate_invoice.php?memo_no=${memo_no}`);
    fetch(`generate_invoice.php?memo_no=${memo_no}`)
        .then(response => response.json())
        .then(data => {
            console.log(data);
            document.getElementById('invoice_no').value = data.next_invoice_no; 
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Call the function to fetch invoice data
fetchInvoiceData();

const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

const today = new Date();
const month = months[today.getMonth()]; // Get the month in words
const day = today.getDate(); // Get the day of the month
const year = today.getFullYear(); // Get the year

const formattedDate = `${month} ${day}, ${year}`;

document.getElementById('formatted-date').textContent = formattedDate;

function getQueryParam(parameterName) {
    const urlSearchParams = new URLSearchParams(window.location.search);
    return urlSearchParams.get(parameterName);
}

// Get references to HTML elements
const recipientInput = document.getElementById('recipient');
const addressInput = document.getElementById('addressInput');

// Add an event listener for the memo_no dropdown change event
document.addEventListener('DOMContentLoaded', function () {
    // Get the memo_no query parameter value
    const memo_no = getQueryParam('memo_no');

    const selectedMemoNo = memo_no;
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

    fetchMemoData(memo_no);
});

// Function to fetch and display memo data
function fetchMemoData(memoNo) {
    console.log(memoNo);
    fetch(`fetch_memo_rows.php?memo_no=${memoNo}`)
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

    finalTotalColumn.textContent = currency + " " + totalFinal.toFixed(2) || 0;
}

// Function to add a new row
function addRow(data) {
    const tableBody = document.getElementById('table-body');
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>${tableBody.children.length + 1}</td>
        <td name="lot_no" class="editable">${data.lot_no}</td>
        <td name="wt" class="editable wt">${data.kept}</td>
        <td name="shape" class="editable">${data.shape}</td>
        <td name="color" class="editable">${data.color}</td>
        <td name="clarity" class="editable">${data.clarity}</td>
        <td name="certificate" class="editable">${data.certificate_no}</td>
        <td name="rap" class="editable rap">${data.rap}</td>
        <td name="disc" class="editable disc">${data.discount}%</td>
        <td name="price" class="editable price">${data.price}</td>
        <td name="final_total" class="editable">${data.final_total === null ? '' : data.final_total}</td>
    `;
    tableBody.appendChild(newRow);
}

// JavaScript code to handle the print button click
document.getElementById('printButton').addEventListener('click', function (e) {
    e.preventDefault();
    // Call the printInvoice function to initiate printing
    printInvoice();
});

// Function to initiate the printing
function printInvoice() {
    // Save data or perform any other actions before printing
    saveData();

    // Get the container element for printable content
    const container = document.querySelector('.printable-content');

    // Create a clone of the container
    const clone = container.cloneNode(true);

    // Apply a media query to hide the button and other elements when printing
    const style = document.createElement('style');
    style.innerHTML = '@media print { #printButton, #goBack, .spacing { display: none; } }';
    document.head.appendChild(style);

    // Add a class to the clone to specify a page break
    clone.classList.add('page-break');

    // Append the clone to the body for printing
    document.body.appendChild(clone);

    // Use window.print() to open the print dialog
    window.print();

    // Remove the added style element and the clone after printing
    document.head.removeChild(style);
    clone.remove();
}


function saveData() {
    const invoice_no = document.getElementById("invoice_no").value;
    const memo_no = getQueryParam('memo_no');
    const date = new Date().toISOString().split('T')[0];

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
                print();
            } else {
                // Handle other response statuses here if needed
                console.error('Server returned an error:', response.statusText);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
        });
}

// JavaScript for the "Back" button
function goBackOneStep() {
    window.history.back(); // This will go back one step in the browser's history
}