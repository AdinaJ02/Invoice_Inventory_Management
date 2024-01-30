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

document.addEventListener('DOMContentLoaded', function () {
    // Disable the "Print Invoice" button initially
    const printButton = document.getElementById("printButton");
    printButton.disabled = true;
});


// Fetch the next memo number from the PHP script
fetch('generate_invoice.php')
    .then(response => response.json())
    .then(data => {
        document.getElementById('invoice_no').value = data.next_invoice_no;
    })
    .catch(error => {
        console.error('Error:', error);
    });

// Add the initial rows
for (let i = 0; i < 10; i++) {
    addRow();
}

// Get the current date in yyyy-mm-dd format
const today = new Date().toISOString().split('T')[0];

// Set the default value for the date input field
document.getElementById('date').value = today;

// Get references to the input fields and the results container
const recipientInput = document.getElementById('recipient');
const addressInput = document.getElementById('addressInput');
const autocompleteResults = document.getElementById('autocomplete-results');

let inputTimeout; // Variable to store the input delay timer
let isNameDropdownDisplayed = false;
let dropdown; // Variable to store the dropdown

// Create a container for the dropdown and append it above the recipientInput
const dropdownContainer = document.createElement('div');
dropdownContainer.classList.add('dropdown-container');
document.body.appendChild(dropdownContainer);

// Listen for input changes in the recipient input field
recipientInput.addEventListener('input', function () {
    const name = recipientInput.value.trim();

    // Clear the address field and autocomplete results
    addressInput.value = '';
    autocompleteResults.innerHTML = '';

    if (name.length === 0) {
        // Hide the dropdown container if input is empty
        dropdownContainer.style.display = 'none';
        return;
    }

    // Delay before making the AJAX request
    clearTimeout(inputTimeout);
    inputTimeout = setTimeout(() => {
        // Make an AJAX request to fetch the names and addresses based on the input
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const data = JSON.parse(xhr.responseText);
                console.log('Received response:', data);

                // Display the dropdown with names and addresses
                if (data.length > 0) {
                    // Set the flag to indicate that the name dropdown is displayed
                    isNameDropdownDisplayed = true;

                    // Clear the dropdown container before populating new data
                    dropdownContainer.innerHTML = '';

                    // Create and populate the dropdown
                    dropdown = document.createElement('select');
                    dropdown.classList.add('dropdown');

                    const option = document.createElement('option');
                    option.textContent = "Select name and address";
                    dropdown.appendChild(option);

                    for (const item of data) {
                        const option = document.createElement('option');
                        // Combine name and address for each option
                        option.textContent = `${item.name} - ${item.address}`;
                        dropdown.appendChild(option);
                    }

                    // Append the dropdown to the dropdown container
                    dropdownContainer.appendChild(dropdown);

                    // Add an event listener to handle dropdown selection
                    dropdown.addEventListener('change', function () {
                        const selectedValue = this.value;
                        // Split the selected value into name and address
                        const [selectedName, selectedAddress] = selectedValue.split(' - ');
                        recipientInput.value = selectedName;
                        addressInput.value = selectedAddress;
                        autocompleteResults.innerHTML = '';
                        isNameDropdownDisplayed = false; // Reset the flag
                        // Hide the dropdown container after selection
                        dropdownContainer.style.display = 'none';
                    });

                    // Show the dropdown container above the recipientInput
                    const rect = recipientInput.getBoundingClientRect();
                    dropdownContainer.style.display = 'block';
                    dropdownContainer.style.position = 'absolute';
                    dropdownContainer.style.top = `${rect.top - dropdownContainer.clientHeight}px`;
                    dropdownContainer.style.left = `${rect.left}px`;

                    // Calculate the spacing at the bottom of the dropdown container
                    const spacingBottom = 5; // You can adjust this value as needed

                    // Adjust the position of the dropdown container
                    dropdownContainer.style.top = `${rect.top - dropdownContainer.clientHeight - spacingBottom}px`;

                } else {
                    // If no names found, hide the dropdown container
                    dropdownContainer.style.display = 'none';
                }
            }
        };

        // Adjust the URL to your PHP script that fetches names and addresses based on input
        xhr.open('GET', `../Memo/fetch_name.php?input=${name}`, true);
        xhr.send();
    }, 500); // Adjust the delay time (in milliseconds) as needed
});

// Close the dropdown when clicking outside the dropdown container
document.addEventListener('click', function (event) {
    if (!dropdownContainer.contains(event.target)) {
        dropdownContainer.style.display = 'none';
    }
});

function addRow() {
    const tableBody = document.getElementById('table-body');
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
<td>${tableBody.children.length + 1}</td>
<td contenteditable="true" name="lot_no" class="editable"></td>
<td contenteditable="true" name="desc" class="editable"></td>
<td contenteditable="true" name="wt" class="editable wt"></td>
<td contenteditable="true" name="shape" class="editable"></td>
<td contenteditable="true" name="color" class="editable"></td>
<td contenteditable="true" name="clarity" class="editable"></td>
<td contenteditable="true" name="certificate" class="editable"></td>
<td contenteditable="true" name="rap" class="editable rap"></td>
<td contenteditable="true" name="disc" class="editable disc"></td>
<td contenteditable="true" name="price" class="editable price"></td>
<td name="final_total" class="editable final_total"></td>
<td class="delete-cell"><span class="delete-icon">Delete</span></td>
`;
    tableBody.appendChild(newRow);

    // Attach a click event listener to the delete icon
    const deleteIcon = newRow.querySelector('.delete-icon');
    deleteIcon.addEventListener('click', function () {
        newRow.querySelector('[name="shape"]').textContent = '';
        newRow.querySelector('[name="desc"]').textContent = '';
        newRow.querySelector('[name="lot_no"]').textContent = '';
        newRow.querySelector('[name="wt"]').textContent = '';
        newRow.querySelector('[name="color"]').textContent = '';
        newRow.querySelector('[name="clarity"]').textContent = '';
        newRow.querySelector('[name="certificate"]').textContent = '';
        newRow.querySelector('[name="rap"]').textContent = '';
        newRow.querySelector('[name="disc"]').textContent = '';
        newRow.querySelector('[name="price"]').textContent = '';
        newRow.querySelector('[name="final_total"]').textContent = '';
        calculateTotals();
    });

    // Add event listeners to rap and disc fields for calculations
    const rapField = newRow.querySelector('.rap');
    const discField = newRow.querySelector('.disc');
    const priceField = newRow.querySelector('.price');
    const totalField = newRow.querySelector('.final_total');
    const wtField = newRow.querySelector('.wt');

    rapField.addEventListener('input', calculatePriceAndTotal);
    discField.addEventListener('input', calculatePriceAndTotal);
    priceField.addEventListener('input', calculatePriceAndTotal);
    wtField.addEventListener('input', calculatePriceAndTotal);

    // Initial calculation
    calculatePriceAndTotal();

    // Function to calculate price and total
    function calculatePriceAndTotal() {
        const rapValue = parseFloat(rapField.textContent) || 0;
        const discValue = parseFloat(discField.textContent) || 0;
        const priceValue = parseFloat(priceField.textContent) || 0;
        const wtValue = parseFloat(wtField.textContent) || 0;
        if (rapValue === 0 && discValue === 0) {
        } else {
            // Calculate the Price
            const price = (rapValue * (100 + discValue)) / 100;
            priceField.textContent = price.toFixed(2);
        }
        const price = Math.abs(parseFloat(priceField.textContent));
        const total = Math.abs(price * wtValue);
        totalField.textContent = total.toFixed(2);
        calculateTotals();
    }

    // Clear initial values in price and total fields
    priceField.textContent = '';
    totalField.textContent = '';

    // Get references to the editable input fields in the new row
    const wtInput = newRow.querySelector('.editable[name="wt"]');
    const totalInput = newRow.querySelector('.editable[name="final_total"]');

    // Add event listeners to wt and total input fields in the new row
    wtInput.addEventListener('input', calculateTotals);

    // Initial calculation when a new row is created
    calculateTotals();

    newRow.querySelector('[name="lot_no"]').addEventListener('blur', function () {
        const lotNo = this.textContent.trim();
        console.log(lotNo);

        // Make an AJAX request to fetch values from the server
        fetch('../Memo/fetch_lot_data.php?lotNo=' + lotNo)
            .then(response => {
                console.log("Response status:", response.status);
                console.log("Response headers:", response.headers);

                return response.text(); // Read the response as text
            })
            .then(responseText => {
                console.log("Response text:", responseText); // Log the raw response text

                try {
                    const data = JSON.parse(responseText); // Attempt to parse the response as JSON
                    if (data) {
                        newRow.querySelector('[name="wt"]').textContent = data.weight;

                        newRow.querySelector('[name="desc"]').textContent = data.description;
                        newRow.querySelector('[name="desc"]').setAttribute('contentEditable', 'false');

                        newRow.querySelector('[name="shape"]').textContent = data.shape;
                        newRow.querySelector('[name="shape"]').setAttribute('contentEditable', 'false');

                        newRow.querySelector('[name="color"]').textContent = data.color;
                        newRow.querySelector('[name="color"]').setAttribute('contentEditable', 'false');

                        newRow.querySelector('[name="clarity"]').textContent = data.clarity;
                        newRow.querySelector('[name="clarity"]').setAttribute('contentEditable', 'false');

                        newRow.querySelector('[name="certificate"]').textContent = data.certificate_no;
                        newRow.querySelector('[name="certificate"]').setAttribute('contentEditable', 'false');

                        newRow.querySelector('[name="rap"]').textContent = data.rap;
                        newRow.querySelector('[name="disc"]').textContent = data.discount + "%";

                        calculatePriceAndTotal();
                    }
                } catch (error) {
                    console.error('Error parsing JSON:', error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    })
}

// Add an event listener to disc_price
const discPriceField = document.getElementById('disc_price');
discPriceField.addEventListener('input', calculateDiscTotal);

// Function to calculate disc_total based on disc_price
function calculateDiscTotal() {
    const discPriceValue = parseFloat(discPriceField.textContent) || 0;

    // Calculate disc_total (assuming a fixed multiplier of 1)
    const discTotal = discPriceValue * 1;
    document.getElementById('disc_total').textContent = discTotal.toFixed(2);

    // Calculate the final totals
    calculateTotals();
}

// Listen for changes in the last row and add a new row if necessary
document.getElementById('table-body').addEventListener('input', function (e) {
    const lastRow = this.lastElementChild;
    const lastRowCells = lastRow.querySelectorAll('td.editable');

    for (const cell of lastRowCells) {
        if (cell.textContent.trim() !== '') {
            addRow();
            break;
        }
    }
});

// Function to calculate total_wt and total_tot
function calculateTotals() {
    let totalWt = 0;
    let totalTot = 0;

    // Calculate total_wt and total_tot based on input values in all rows
    const wtInputs = document.querySelectorAll('.editable[name="wt"]');
    const totalInputs = document.querySelectorAll('.editable[name="final_total"]');
    const discTotalInputs = document.getElementById('disc_total'); // Add this line

    wtInputs.forEach((input) => {
        const wtValue = Math.abs(parseFloat(input.textContent)) || 0;
        totalWt += wtValue;
    });

    totalInputs.forEach((input) => {
        const totalValue = parseFloat(input.textContent) || 0;
        totalTot += totalValue;
    });

    const discTotalValue = parseFloat(discTotalInputs.textContent) || 0;
    totalTot += discTotalValue;

    // Update the corresponding <td> elements for the totals
    const totalWtField = document.querySelector('.total_wt');
    const totalTotField = document.querySelector('.total_final_tot');
    totalWtField.textContent = totalWt.toFixed(2);
    totalWtField.value = totalWt.toFixed(2);
    totalTotField.textContent = totalTot.toFixed(2);
    totalTotField.value = totalTot.toFixed(2);
}

const saveButton = document.getElementById("saveInvoice");
const printButton = document.getElementById("printButton");

// Add event listeners to the buttons
saveButton.addEventListener("click", saveData);
printButton.addEventListener("click", print);

function print() {
    saveData();
    const invoice_no = document.getElementById("invoice_no").value;

    // Delay the redirection by 3 seconds
    const encodedInvoiceNo = encodeURIComponent(invoice_no);
    const destinationURL = `../print_invoice_create/print_invoice_create.html?invoice_no=${encodedInvoiceNo}`;
    window.location.href = destinationURL;
}

// JavaScript for checkbox behavior
const receivedCheckbox = document.getElementById("receivedCheckbox");
const notReceivedCheckbox = document.getElementById("notReceivedCheckbox");

receivedCheckbox.addEventListener("change", function () {
    if (receivedCheckbox.checked) {
        notReceivedCheckbox.checked = false;
    }
});

notReceivedCheckbox.addEventListener("change", function () {
    if (notReceivedCheckbox.checked) {
        receivedCheckbox.checked = false;
    }
});

// Function to save data to the server
function saveData() {
    const tableRows = document.querySelectorAll('#table-body tr');
    const data = [];
    const invoice_no = document.getElementById("invoice_no").value;
    const date = document.getElementById("date").value;
    const name = document.getElementById("recipient").value;
    const address = document.getElementById("addressInput").value;
    const total_wt = document.querySelector('.total_wt').value;
    const total_final_tot = document.querySelector('.total_final_tot').value;
    let discTotalElement = document.getElementById("disc_total");
    let disc_total = discTotalElement.textContent.trim() !== '' ? parseFloat(discTotalElement.textContent) : 0;
    // Determine if "Received" or "Not Received" checkbox is checked
    const paymentStatus = document.getElementById("receivedCheckbox").checked ? "Received" : "Not Received";

    tableRows.forEach((row) => {
        const rowData = {};
        row.querySelectorAll('td.editable').forEach((cell) => {
            const name = cell.getAttribute('name');
            rowData[name] = cell.textContent.trim();
        });
        data.push(rowData);
    });

    const requestData = {
        invoice_no: invoice_no,
        date: date,
        name: name,
        address: address,
        total_wt: total_wt,
        total_final_tot: total_final_tot,
        disc_total: disc_total,
        paymentStatus: paymentStatus,
        data: data,
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
                // Display a success message
                const successMessage = document.getElementById('successMessage');
                successMessage.textContent = 'Invoice saved successfully!';

                // Optionally, you can clear the message after a few seconds
                setTimeout(() => {
                    successMessage.textContent = '';
                    const printButton = document.getElementById("printButton");
                    printButton.disabled = false;

                    // If the response status is OK (HTTP status 200), redirect to another page
                    window.location.href = `../edit_invoice/edit_invoice.html?invoice_no=${encodeURIComponent(invoice_no)}`;
                }, 3000); // Clear the message after 3 seconds
            } else {
                // Handle other response statuses here if needed
                console.error('Server returned an error:', response.statusText);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
        });
}

// Add event listener to the entire table to handle "Enter" key presses for cell navigation
document.getElementById('table-body').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const activeCell = document.activeElement;

        if (activeCell) {
            const row = activeCell.parentElement;
            const cellIndex = Array.from(row.cells).indexOf(activeCell);

            if (cellIndex < row.cells.length - 1) {
                // Move to the next cell in the same row
                const nextCell = row.cells[cellIndex + 1];
                nextCell.focus();
            } else {
                // Move to the first cell of the next row
                const nextRow = row.nextElementSibling;
                if (nextRow) {
                    const firstCell = nextRow.cells[0];
                    firstCell.focus();
                } else {
                    // There is no next row; add a new row and move to its first cell
                    addRow();
                    const newRows = document.getElementById('table-body').querySelectorAll('tr');
                    const newRow = newRows[newRows.length - 1];
                    const firstCell = newRow.cells[0];
                    firstCell.focus();
                }
            }
        }
    }
});

// JavaScript for the "Back" button
function goBackOneStep() {
    window.history.back(); // This will go back one step in the browser's history
}