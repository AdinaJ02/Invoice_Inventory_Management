let currency;

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
        termsInvoice.textContent = data.terms_invoice;
        currency = data.currency;
    })
    .catch(error => {
        console.error('Error:', error);
    });

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
    const invoice_no = getQueryParam('invoice_no');
    document.getElementById("invoice_no").value = invoice_no;

    const selectedInvoiceoNo = invoice_no;
    if (selectedInvoiceoNo) {
        // Perform a fetch to fetch_memo_details.php with the selected memo_no
        fetch(`../edit_invoice_memo/fetch_invoice_details.php?invoice_no=${selectedInvoiceoNo}`)
            .then(response => response.json())
            .then(data => {
                // Update the form fields with fetched data
                // Assuming data.date contains the date in the format "YYYY-MM-DD"
                const rawDate = data.invoice_date; // Replace with your actual date

                // Create a Date object from the raw date string
                const formattedDate = new Date(rawDate);

                // Define an array of month names
                const monthNames = [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ];

                // Extract the components of the date
                const day = formattedDate.getDate();
                const monthIndex = formattedDate.getMonth();
                const year = formattedDate.getFullYear();

                // Format the date in the desired format (e.g., "November 10, 2023")
                const formattedDateString = `${monthNames[monthIndex]} ${day}, ${year}`;

                // Set the formatted date in the "formatted-date" element
                document.getElementById("formatted-date").textContent = formattedDateString;

                recipientInput.value = data.customer_name;
                addressInput.value = data.address;

                const finalTotalColumn = document.querySelector('td[name="total_final_tot"]');
                finalTotalColumn.textContent = currency + " " + data.total_total || 0;
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

    fetchInvoiceData(invoice_no);
});

// Function to fetch and display memo data
function fetchInvoiceData(invoiceNo) {
    fetch(`../edit_invoice_memo/fetch_invoice_rows.php?invoice_no=${invoiceNo}`)
        .then((response) => response.json())
        .then((data) => {
            // Loop through the data and add a row for each record
            data.forEach(record => {
                addRow(record);
            });

            totalWeightTotal();
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

    console.log(data);
}

document.getElementById('printButton').addEventListener('click', function (e) {
    e.preventDefault();
    // Call the printInvoice function to initiate printing
    printInvoice();
});

// Function to initiate the printing
function printInvoice() {
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

// JavaScript for the "Back" button
function goBackOneStep() {
    window.history.back(); // This will go back one step in the browser's history
}