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

// Fetch the next memo number from the PHP script
fetch('../print_invoice/generate_invoice.php')
    .then(response => response.json())
    .then(data => {
        document.getElementById('invoice_no').value = data.next_memo_no;
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

// Listen for input changes in the recipient input field
recipientInput.addEventListener('input', function () {
    const name = recipientInput.value.trim();

    // Clear the address field and autocomplete results
    addressInput.value = '';
    autocompleteResults.innerHTML = '';

    if (name.length === 0) {
        return;
    }

    // Make an AJAX request to fetch the address based on the name
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const data = JSON.parse(xhr.responseText);
            console.log('Received response:', data);

            // Display the autocomplete results
            if (data.length > 0) {
                autocompleteResults.innerHTML = data.map(item => `<div>${item}</div>`).join('');;
            }
        }
    };

    // Adjust the URL to your PHP script that fetches addresses based on names
    xhr.open('GET', `../Memo/fetch_addresses.php?name=${name}`, true);
    xhr.send();
});

// Listen for clicks on autocomplete results and fill in the address
autocompleteResults.addEventListener('click', function (event) {
    if (event.target.tagName === 'DIV') {
        addressInput.value = event.target.textContent;
        autocompleteResults.innerHTML = '';
    }
});

function addRow() {
    const tableBody = document.getElementById('table-body');
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
<td>${tableBody.children.length + 1}</td>
<td contenteditable="true" name="lot_no" class="editable"></td>
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
        const price = parseFloat(priceField.textContent);
        const total = price * wtValue;
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

    wtInputs.forEach((input) => {
        const wtValue = parseFloat(input.textContent) || 0;
        totalWt += wtValue;
    });

    totalInputs.forEach((input) => {
        const totalValue = parseFloat(input.textContent) || 0;
        totalTot += totalValue;
    });

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
    const invoice_no = document.getElementById("invoice_no").value;
    window.location.href = `../print_invoice_create/print_invoice_create.html?invoice_no=${encodeURIComponent(invoice_no)}`;
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

// JavaScript for the "Back" button
function goBackOneStep() {
    window.history.back(); // This will go back one step in the browser's history
}