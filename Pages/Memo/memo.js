// Function to add a new row
function addRow() {
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
<td class="delete-cell"><span class="delete-icon">Delete</span></td>
`;
    tableBody.appendChild(newRow);

    // Attach a click event listener to the delete icon
    const deleteIcon = newRow.querySelector('.delete-icon');
    deleteIcon.addEventListener('click', function () {
        newRow.querySelector('[name="lot_no"]').textContent = '';
        newRow.querySelector('[name="desc"]').textContent = '';
        newRow.querySelector('[name="shape"]').textContent = '';
        newRow.querySelector('[name="size"]').textContent = '';
        newRow.querySelector('[name="pcs"]').textContent = '';
        newRow.querySelector('[name="wt"]').textContent = '';
        newRow.querySelector('[name="color"]').textContent = '';
        newRow.querySelector('[name="clarity"]').textContent = '';
        newRow.querySelector('[name="certificate"]').textContent = '';
        newRow.querySelector('[name="rap"]').textContent = '';
        newRow.querySelector('[name="disc"]').textContent = '';
        newRow.querySelector('[name="price"]').textContent = '';
        newRow.querySelector('[name="total"]').textContent = '';

        newRow.querySelector('[name="size"]').setAttribute('contentEditable', 'true');
        newRow.querySelector('[name="shape"]').setAttribute('contentEditable', 'true');
        newRow.querySelector('[name="color"]').setAttribute('contentEditable', 'true');
        newRow.querySelector('[name="clarity"]').setAttribute('contentEditable', 'true');
        newRow.querySelector('[name="certificate"]').setAttribute('contentEditable', 'true');

        calculateTotals();
    });

    const shapeInput = newRow.querySelector('[name="shape"]');
    const sizeInput = newRow.querySelector('[name="size"]');

    shapeInput.addEventListener('input', function () {
        const shape = this.textContent.trim();
        fetchDropdownData(shape, shapeInput);
    });

    sizeInput.addEventListener('input', function () {
        const size = this.textContent.trim();
        fetchDropdownData(size, sizeInput);
    });

    function fetchDropdownData(value, otherInput) {
        // Make an AJAX request to fetch dropdown data from the server based on the value (shape/size)
        // Replace 'fetch_dropdown_data.php' with the actual URL for fetching dropdown data
        fetch(`fetch_dropdown_data.php?value=${value}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    // Create and populate the dropdown
                    const dropdown = document.createElement('select');
                    dropdown.classList.add('dropdown');
                    for (const item of data) {
                        const option = document.createElement('option');
                        option.value = item.lot_no;
                        option.textContent = `${item.lot_no} - ${item.shape} - ${item.size} - ${item.pcs} - ${item.wt} - ${item.color} - ${item.clarity} - ${item.certificate} - ${item.rap} - ${item.discount} - ${item.price}`;
                        dropdown.appendChild(option);
                    }

                    // Clear the other input field
                    otherInput.textContent = '';

                    // Replace the current input field with the dropdown
                    newRow.replaceChild(dropdown, otherInput);

                    // Add an event listener to handle dropdown selection
                    dropdown.addEventListener('change', function () {
                        const selectedValue = this.value;
                        populateFieldsFromDropdown(selectedValue, otherInput);
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    // Function to populate fields based on the selected value from the dropdown
    function populateFieldsFromDropdown(selectedValue, otherInput) {
        console.log(selectedValue);
        // Make an AJAX request to fetch data for the selected lot_no from the server
        // Replace 'fetch_lot_data.php' with the actual URL for fetching lot data
        fetch(`fetch_lot_data.php?lotNo=${selectedValue}`)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    // Populate fields in the current row with data from the server
                    newRow.querySelector('[name="lot_no"]').textContent = data.lot_no;
                    newRow.querySelector('[name="wt"]').textContent = data.weight;
                    newRow.querySelector('[name="pcs"]').textContent = data.pcs;
                    newRow.querySelector('[name="color"]').textContent = data.color;
                    newRow.querySelector('[name="clarity"]').textContent = data.clarity;
                    newRow.querySelector('[name="certificate"]').textContent = data.certificate_no;
                    newRow.querySelector('[name="rap"]').textContent = data.rap;
                    newRow.querySelector('[name="disc"]').textContent = data.discount;

                    // Remove the dropdown from the current row
                    const dropdown = newRow.querySelector('.dropdown');
                    if (dropdown) {
                        // Update the corresponding field in the current row
                        if (otherInput === shapeInput) {
                            newRow.querySelector('[name="size"]').textContent = data.size;
                            otherInput.textContent = data.shape;
                        } else {
                            newRow.querySelector('[name="shape"]').textContent = data.shape;
                            otherInput.textContent = data.size;
                        }

                        // Replace the dropdown with the text input
                        dropdown.parentNode.replaceChild(otherInput, dropdown);
                    }

                    calculatePriceAndTotal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    // Add event listeners to rap and disc fields for calculations
    const rapField = newRow.querySelector('.rap');
    const discField = newRow.querySelector('.disc');
    const priceField = newRow.querySelector('.price');
    const totalField = newRow.querySelector('.total');
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
    const totalInput = newRow.querySelector('.editable[name="total"]');

    // Add event listeners to wt and total input fields in the new row
    wtInput.addEventListener('input', calculateTotals);

    // Initial calculation when a new row is created
    calculateTotals();

    newRow.querySelector('[name="lot_no"]').addEventListener('blur', function () {
        const lotNo = this.textContent.trim();
        console.log(lotNo);

        // Make an AJAX request to fetch values from the server
        fetch('fetch_lot_data.php?lotNo=' + lotNo)
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

                        newRow.querySelector('[name="size"]').textContent = data.size;
                        newRow.querySelector('[name="size"]').setAttribute('contentEditable', 'false');

                        newRow.querySelector('[name="pcs"]').textContent = data.pcs;

                        newRow.querySelector('[name="shape"]').textContent = data.shape;
                        newRow.querySelector('[name="shape"]').setAttribute('contentEditable', 'false');

                        newRow.querySelector('[name="color"]').textContent = data.color;
                        newRow.querySelector('[name="color"]').setAttribute('contentEditable', 'false');

                        newRow.querySelector('[name="clarity"]').textContent = data.clarity;
                        newRow.querySelector('[name="clarity"]').setAttribute('contentEditable', 'false');

                        newRow.querySelector('[name="certificate"]').textContent = data.certificate_no;
                        newRow.querySelector('[name="certificate"]').setAttribute('contentEditable', 'false');

                        newRow.querySelector('[name="rap"]').textContent = data.rap;
                        newRow.querySelector('[name="disc"]').textContent = data.discount;

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


// Function to calculate total_wt and total_tot
function calculateTotals() {
    let totalWt = 0;
    let totalTot = 0;

    // Calculate total_wt and total_tot based on input values in all rows
    const wtInputs = document.querySelectorAll('.editable[name="wt"]');
    const totalInputs = document.querySelectorAll('.editable[name="total"]');

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
    const totalTotField = document.querySelector('.total_tot');
    totalWtField.textContent = totalWt.toFixed(2);
    totalWtField.value = totalWt.toFixed(2);
    totalTotField.textContent = totalTot.toFixed(2);
    totalTotField.value = totalTot.toFixed(2);
}

// Get references to the total_wt and total_tot elements
const totalWtField = document.querySelector('.total_wt');

// Add the initial rows
for (let i = 0; i < 10; i++) {
    addRow();
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

// Get the current date in yyyy-mm-dd format
const today = new Date().toISOString().split('T')[0];

// Set the default value for the date input field
document.getElementById('date').value = today;

// Get the current date
const currentDate = new Date();

// Populate the dropdown with the appropriate number of days
const dayDropdown = document.getElementById('dayDropdown');
for (let i = 1; i <= 30; i++) {
    const option = document.createElement('option');
    option.value = i;
    option.textContent = i;
    dayDropdown.appendChild(option);
}

// Set the default value to the current day
dayDropdown.value = currentDate.getDate();

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

// Fetch the next memo number from the PHP script
fetch('generate_memo.php')
    .then(response => response.json())
    .then(data => {
        document.getElementById('memo_no').value = data.next_memo_no;
    })
    .catch(error => {
        console.error('Error:', error);
    });

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
    xhr.open('GET', `fetch_addresses.php?name=${name}`, true);
    xhr.send();
});

// Listen for clicks on autocomplete results and fill in the address
autocompleteResults.addEventListener('click', function (event) {
    if (event.target.tagName === 'DIV') {
        addressInput.value = event.target.textContent;
        autocompleteResults.innerHTML = '';
    }
});


// Add an event listener to the button
const form = document.getElementById('form-data');

form.addEventListener('submit', function (event) {
    event.preventDefault(); 
    saveData(); // Call the saveData function when the button is clicked
});

// Function to save data to the server
function saveData() {
    const tableRows = document.querySelectorAll('#table-body tr');
    const data = [];
    const memo_no = document.getElementById("memo_no").value;
    const date = document.getElementById("date").value;
    const memorandum_day = document.getElementById("dayDropdown").value;
    const name = document.getElementById("recipient").value;
    const address = document.getElementById("addressInput").value;
    const total_wt = document.querySelector('.total_wt').value;
    const total_total = document.querySelector('.total_tot').value;

    console.log(total_wt);
    console.log(total_total);

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
        total_wt: total_wt,
        total_total: total_total,
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
                window.location.href = `../print_memo/print_memo.html?memo_no=${encodeURIComponent(memo_no)}`;
            } else {
                // Handle other response statuses here if needed
                console.error('Server returned an error:', response.statusText);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
        });
}