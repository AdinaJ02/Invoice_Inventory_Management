// Fetch data from fetch_data.php using JavaScript
fetch('../php_data/fetch_data_company.php')
    .then(response => response.json())
    .then(data => {
        const companyName = document.querySelector('#title_table h2');
        const desc = document.querySelector('#title_table h4');
        const phoneNo = document.querySelector('#title_table b');
        const address = document.querySelector('#title_table #address');
        const email = document.querySelector('#title_table #email');
        const disclaimerMemo = document.querySelector('#disclaimer b');

        // Set data in HTML elements
        companyName.textContent = data.company_name;
        desc.textContent = data.desc;
        phoneNo.textContent = `Cell: ${data.phone_no}`;
        address.textContent = data.address;
        email.textContent = `E: ${data.email}`;
        disclaimerMemo.textContent = data.disclaimer_memo;
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

// Get references to HTML elements
const memoNo = document.getElementById('memo_no');
const memorandumDayInput = document.getElementById('memorandum_day');
const dateInput = document.getElementById('date');
const recipientInput = document.getElementById('recipient');
const addressInput = document.getElementById('addressInput');

function getQueryParam(parameterName) {
    const urlSearchParams = new URLSearchParams(window.location.search);
    return urlSearchParams.get(parameterName);
}
// Add an event listener for the memo_no dropdown change event
document.addEventListener('DOMContentLoaded', function () {
    // Get the memo_no query parameter value
    const memo_no = getQueryParam('memo_no');

    memoNo.value = memo_no;
    const selectedMemoNo = memoNo.value;
    if (selectedMemoNo) {
        // Perform a fetch to fetch_memo_details.php with the selected memo_no
        fetch(`../print_memo/fetch_memo_details.php?memo_no=${selectedMemoNo}`)
            .then(response => response.json())
            .then(data => {
                // Update the form fields with fetched data
                memorandumDayInput.value = data.memorandum_day;
                dateInput.value = data.memo_date;
                recipientInput.value = data.customer_name;
                addressInput.value = data.address;
            })
            .catch(error => console.error('Error fetching memo details:', error));
    } else {
        // Clear the form fields if no memo_no is selected
        memorandumDayInput.value = '';
        dateInput.value = '';
        recipientInput.value = '';
        addressInput.value = '';
    }

    // Make the fields readonly
    memorandumDayInput.readOnly = true;
    dateInput.readOnly = true;
    recipientInput.readOnly = true;
    addressInput.readOnly = true;

    fetchMemoData(memoNo.value);
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
    // Get references to the total_wt and total_tot elements
    const totalWtField = document.querySelector('td[name="total_wt"]');
    const totalTotField = document.querySelector('td[name="total_tot"]');

    // Get references to the wt and total columns
    const wtColumns = document.querySelectorAll('td[name="wt"]');
    const totalColumns = document.querySelectorAll('td[name="total"]');

    // Calculate total_wt and total_tot
    let totalWt = 0;
    let totalTot = 0;

    wtColumns.forEach((wtCell, index) => {
        const wtValue = parseFloat(wtCell.textContent) || 0;
        const totalValue = parseFloat(totalColumns[index].textContent) || 0;

        totalWt += wtValue;
        totalTot += totalValue;
    });

    // Display the calculated values in the respective cells
    totalWtField.textContent = totalWt.toFixed(2);
    totalTotField.textContent = totalTot.toFixed(2);
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

    finalTotalColumn.textContent = totalFinal.toFixed(2) || 0;
}

// Listen for changes in the last row and add a new row if necessary
document.getElementById('table-body').addEventListener('input', function (e) {
    const lastRow = this.lastElementChild;
    const lastRowCells = lastRow.querySelectorAll('td.editable');

    for (const cell of lastRowCells) {
        if (cell.textContent.trim() !== '') {
            addRowEmpty();
            break;
        }
    }
});

// Function to add a new row
function addRow(data) {
    const tableBody = document.getElementById('table-body');
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>${tableBody.children.length + 1}</td>
        <td contenteditable="true" name="lot_no" class="editable">${data.lot_no}</td>
        <td contenteditable="true" name="desc" class="editable">${data.description}</td>
        <td contenteditable="true" name="shape" class="editable">${data.shape}</td>
        <td contenteditable="true" name="size" class="editable">${data.size}</td>
        <td contenteditable="true" name="pcs" class="editable">${data.pcs}</td>
        <td contenteditable="true" name="wt" class="editable wt">${data.weight}</td>
        <td contenteditable="true" name="color" class="editable">${data.color}</td>
        <td contenteditable="true" name="clarity" class="editable">${data.clarity}</td>
        <td contenteditable="true" name="certificate" class="editable">${data.certificate_no}</td>
        <td contenteditable="true" name="rap" class="editable rap">${data.rap}</td>
        <td contenteditable="true" name="disc" class="editable disc">${data.discount}</td>
        <td contenteditable="true" name="price" class="editable price">${data.price}</td>
        <td name="total" class="editable total">${data.total}</td>
        <td contenteditable="true" name="return" class="editable">${data.return === null ? '' : data.return}</td>
        <td contenteditable="true" name="kept" class="editable">${data.kept === null ? '' : data.kept}</td>
        <td name="final_total" class="editable">${data.final_total === null ? '' : data.final_total}</td>
    `;
    tableBody.appendChild(newRow);

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
            const totalCell = parentRow.querySelector('[name="total"]');

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
        <td contenteditable="true" name="desc" class="editable"></td>
        <td contenteditable="true" name="shape" class="editable"></td>
        <td contenteditable="true" name="size" class="editable"></td>
        <td contenteditable="true" name="pcs" class="editable"></td>
        <td contenteditable="true" name="wt" class="editable wt"></td>
        <td contenteditable="true" name="color" class="editable"></td>
        <td contenteditable="true" name="clarity" class="editable"></td>
        <td contenteditable="true" name="certificate" class="editable"></td>
        <td contenteditable="true" name="rap" class="editable rap"></td>
        <td contenteditable="true" name="disc" class="editable disc"></td>
        <td contenteditable="true" name="price" class="editable price"></td>
        <td name="total" class="editable total"></td>
        <td contenteditable="true" name="return" class="editable"></td>
        <td contenteditable="true" name="kept" class="editable"></td>
        <td name="final_total" class="editable"></td>
`;
    tableBody.appendChild(newRow);

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
            const totalCell = parentRow.querySelector('[name="total"]');

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
        }
    });
}

// Add an event listener to the table body to listen for changes in the "Return" cells
document.getElementById('table-body').addEventListener('input', function (event) {
    const targetCell = event.target;

    // Check if the changed cell has the name "return"
    if (targetCell.getAttribute('name') === 'return') {
        // Get the corresponding "Weight" cell
        const weightCell = targetCell.parentElement.querySelector('[name="wt"]');

        if (weightCell) {
            // Calculate "Kept" by subtracting "Return" from "Weight"
            const returnVal = parseFloat(targetCell.textContent) || 0;
            const weightVal = parseFloat(weightCell.textContent) || 0;
            const keptVal = weightVal - returnVal;

            // Get the "Kept" cell and update its content
            const keptCell = targetCell.parentElement.querySelector('[name="kept"]');
            if (keptCell) {
                keptCell.textContent = keptVal.toFixed(2); // You can format it as needed
            }

            // Get the "Price" cell
            const priceCell = targetCell.parentElement.querySelector('[name="price"]');

            // Calculate "Final Total" by multiplying "Weight" and "Price"
            if (priceCell) {
                const priceVal = parseFloat(priceCell.textContent) || 0;
                const finalTotalVal = keptVal * priceVal;

                // Get the "Final Total" cell and update its content
                const finalTotalCell = targetCell.parentElement.querySelector('[name="final_total"]');
                if (finalTotalCell) {
                    finalTotalCell.textContent = finalTotalVal.toFixed(2); // You can format it as needed
                }
            }
        }
    }

    totalWeightTotal();
    totalFinalTotal();
});


// Add an event listener to the button
const saveButton = document.getElementById('saveButton');

saveButton.addEventListener('click', function () {
    saveData(); // Call the saveData function when the button is clicked
});

// Function to save data to the server
function saveData() {
    const tableRows = document.querySelectorAll('#table-body tr');
    const data = [];
    const memo_no = document.getElementById("memo_no").value;
    const date = document.getElementById("date").value;
    const memorandum_day = document.getElementById("memorandum_day").value;
    const name = document.getElementById("recipient").value;
    const address = document.getElementById("addressInput").value;

    tableRows.forEach((row) => {
        const rowData = {};
        row.querySelectorAll('td.editable').forEach((cell) => {
            const name = cell.getAttribute('name');
            rowData[name] = cell.textContent.trim();
        });
        data.push(rowData);
    });

    const requestData = {
        memo_no: memo_no,
        date: date,
        memorandum_day: memorandum_day,
        name: name,
        address: address,
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
                // If the response status is OK (HTTP status 200), redirect to another page
                window.location.href = '../print_invoice/print_invoice.html';
            } else {
                // Handle other response statuses here if needed
                console.error('Server returned an error:', response.statusText);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
        });
}