var currency = "";

if (!localStorage.getItem('hasReloaded')) {
    // Set a flag to indicate the page has been reloaded
    localStorage.setItem('hasReloaded', 'true');
    // Reload the page
    location.reload();
    location.reload();
}

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
        fetch(`../print_invoice_create/fetch_invoice_details.php?invoice_no=${selectedInvoiceoNo}`)
            .then(response => response.json())
            .then(data => {
                // Update the form fields with fetched data
                // Assuming data.date contains the date in the format "YYYY-MM-DD"
                const rawDate = data.date; // Replace with your actual date

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

                const paymentStatus = data.payment_status; // Replace with the actual value from your data

                // Get the checkboxes by their IDs
                const receivedCheckbox = document.getElementById("receivedCheckbox");
                const notReceivedCheckbox = document.getElementById("notReceivedCheckbox");
                const addButton = document.getElementById("addButton");
                const saveInvoiceButton = document.getElementById("saveInvoice");
                const table = document.querySelector(".table_data tbody");

                const finalTotColumn = document.querySelector('td[name="total_final_tot"]');
                finalTotColumn.textContent = currency + " " + data.final_total || 0;

                const disc_price = document.getElementById("disc_price");
                const disc_total = document.getElementById("disc_total");

                disc_price.textContent = data.disc_total;
                disc_total.textContent = data.disc_total;

                // Check the checkbox based on the paymentStatus value
                if (paymentStatus === "Received") {
                    receivedCheckbox.checked = true;
                    notReceivedCheckbox.checked = false;
                } else if (paymentStatus === "Not Received") {
                    receivedCheckbox.checked = false;
                    notReceivedCheckbox.checked = true;
                }

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

    fetchInvoiceData(invoice_no);
});

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

// Add a new row when the "Add Row" button is clicked
document.getElementById("addButton").addEventListener("click", addRowEmpty);

// Function to fetch and display memo data
function fetchInvoiceData(invoiceNo) {
    fetch(`fetch_invoice_rows.php?invoice_no=${invoiceNo}`)
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
        <td contenteditable="true" name="lot_no" class="editable">${data.lot_no}</td>
        <td contenteditable="true" name="wt" class="editable wt">${data.wt}</td>
        <td contenteditable="true" name="shape" class="editable">${data.shape}</td>
        <td contenteditable="true" name="color" class="editable">${data.color}</td>
        <td contenteditable="true" name="clarity" class="editable">${data.clarity}</td>
        <td contenteditable="true" name="certificate" class="editable">${data.certificate_no}</td>
        <td contenteditable="true" name="rap" class="editable rap">${data.rap}</td>
        <td contenteditable="true" name="disc" class="editable disc">${data.discount}%</td>
        <td contenteditable="true" name="price" class="editable price">${data.price}</td>
        <td name="final_total" class="editable final_total">${data.total}</td>
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

    // Function to calculate price and total
    function calculatePriceAndTotal() {
        const rapValue = parseFloat(rapField.textContent) || 0;
        const discValue = parseFloat(discField.textContent) || 0;
        const priceValue = parseFloat(priceField.textContent) || 0;
        const wtValue = parseFloat(wtField.textContent) || 0;
        if (rapField && discField && priceField && wtField) {
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
    }

    // Add an event listener to the table body to calculate "Price" and "Total" on input changes
    tableBody.addEventListener('input', function (event) {
        const targetCell = event.target;
        const parentRow = targetCell.parentElement;

        // Check if the changed cell has the name "rap," "disc," or "wt"
        if (targetCell.getAttribute('name') === 'rap' || targetCell.getAttribute('name') === 'disc' || targetCell.getAttribute('name') === 'wt') {
            // Get the corresponding "Rap" cell, "Disc" cell, "Weight" cell, "Price" cell, and "Total" cell
            const rapCell = parentRow.querySelector('[name="rap"]');
            const discCell = parentRow.querySelector('[name="disc"]');
            const wtCell = parentRow.querySelector('[name="wt"]');
            const priceCell = parentRow.querySelector('[name="price"]');
            const totalCell = parentRow.querySelector('[name="final_total"]');

            // Parse the values or set them to 0 if empty
            const rapVal = parseFloat(rapCell.textContent) || 0;
            const discVal = parseFloat(discCell.textContent) || 0;
            const wtVal = parseFloat(wtCell.textContent) || 0;

            // Calculate "Price" based on "Rap" and "Disc" (price = rap - (rap * (disc / 100)))
            const priceVal = (rapVal * (100 + discVal)) / 100;
            priceCell.textContent = priceVal.toFixed(2); // You can format it as needed

            // Calculate "Total" based on "Weight" and "Price" (total = wt * price)
            const totalVal = wtVal * priceVal;
            totalCell.textContent = totalVal.toFixed(2); // You can format it as needed

            totalWeightTotal();
            totalFinalTotal();
        }
    });
}

// Function to add a new row
function addRowEmpty() {
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


    // Function to calculate price and total
    function calculatePriceAndTotal() {
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

        const rapValue = parseFloat(rapField.textContent) || 0;
        const discValue = parseFloat(discField.textContent) || 0;
        const priceValue = parseFloat(priceField.textContent) || 0;
        const wtValue = parseFloat(wtField.textContent) || 0;
        if (rapField && discField && priceField && wtField) {
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
    }

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

// Function to calculate total_wt and total_tot
function calculateTotals() {
    let totalWt = 0;
    let totalTot = 0;

    // Calculate total_wt and total_tot based on input values in all rows
    const wtInputs = document.querySelectorAll('.editable[name="wt"]');
    const totalInputs = document.querySelectorAll('.editable[name="final_total"]');
    const discTotalInputs = document.getElementById('disc_total');

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
    totalTotField.textContent = currency + " " + totalTot.toFixed(2);
    totalTotField.value = totalTot.toFixed(2);
}

document.getElementById('printButton').addEventListener('click', function (e) {
    // saveData();
    const invoice_no = document.getElementById("invoice_no").value;
    window.location.href = `../print_invoice_create/print_invoice_create.php?invoice_no=${encodeURIComponent(invoice_no)}`;
});

function saveData() {
    const tableRows = document.querySelectorAll('#table-body tr');
    const data = [];
    const invoice_no = document.getElementById("invoice_no").value;
    const name = document.getElementById("recipient").value;
    const address = document.getElementById("addressInput").value;
    const total_wt = parseFloat(document.querySelector('.total_wt').textContent) || 0;;
    const totalFinalTotElement = document.querySelector('.total_final_tot');
    const total_final_tot = parseFloat(totalFinalTotElement.textContent.replace(/[^\d.]/g, '')) || 0;
    const disc_total = document.getElementById("disc_total").textContent;
    console.log(disc_total);

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
                    location.reload();
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

const saveButton = document.getElementById("saveInvoice");
saveButton.addEventListener("click", saveData);

// JavaScript for the "Back" button
function goBackOneStep() {
    window.history.back(); // This will go back one step in the browser's history
}