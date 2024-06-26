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
    // Attach a click event listener to the delete icon
    deleteIcon.addEventListener('click', function () {
        const shapeDropdown = newRow.querySelector('[name="shape"] + .dropdown');
        const sizeDropdown = newRow.querySelector('[name="size"] + .dropdown');
        const descDropdown = newRow.querySelector('[name="desc"] + .dropdown');

        if (shapeDropdown) {
            shapeDropdown.parentNode.removeChild(shapeDropdown);
            shapeInput.textContent = '';
        } else {
            newRow.querySelector('[name="shape"]').textContent = '';
        }

        if (sizeDropdown) {
            sizeDropdown.parentNode.removeChild(sizeDropdown);
            sizeInput.textContent = '';
        } else {
            newRow.querySelector('[name="size"]').textContent = '';
        }

        if (descDropdown) {
            descDropdown.parentNode.removeChild(descDropdown);
            descInput.textContent = '';
        } else {
            newRow.querySelector('[name="desc"]').textContent = '';
        }

        // Clear other cell contents without affecting the cells themselves
        const cellsToClear = newRow.querySelectorAll('.editable');
        cellsToClear.forEach((cell) => {
            cell.textContent = ''; // Clear the content
            // You may want to add additional logic here to handle other elements
        });

        // Enable content editing for certain cells
        newRow.querySelector('[name="size"]').setAttribute('contenteditable', 'true');
        newRow.querySelector('[name="shape"]').setAttribute('contenteditable', 'true');
        newRow.querySelector('[name="desc"]').setAttribute('contenteditable', 'true');

        // Calculate totals based on the updated data
        calculateTotals();
    });

    const shapeCells = document.querySelectorAll('td[name="shape"]');
    const sizeCells = document.querySelectorAll('td[name="size"]');
    const descCells = document.querySelectorAll('td[name="desc"]');
    let inputTimeout; // Variable to store the input delay timer

    shapeCells.forEach(shapeCell => {
        let isCellFocused = false;
        shapeCell.addEventListener('input', function () {
            clearTimeout(inputTimeout); // Clear the previous timeout
            if (isCellFocused) {
                // Delay showing the dropdown if the cell is focused
                inputTimeout = setTimeout(() => {
                    const shape = shapeCell.textContent.trim();
                    if (shape !== '') {
                        fetchDropdownData(shape, 'shape', shapeCell);
                    }
                }, 1000); // Adjust the delay time (in milliseconds) as needed
            }
        });

        shapeCell.addEventListener('focus', function () {
            isCellFocused = true;
        });

        shapeCell.addEventListener('blur', function () {
            isCellFocused = false;
        });
    });

    descCells.forEach(descCell => {
        let isCellFocused = false;
        descCell.addEventListener('input', function () {
            clearTimeout(inputTimeout); // Clear the previous timeout
            if (isCellFocused) {
                // Delay showing the dropdown if the cell is focused
                inputTimeout = setTimeout(() => {
                    const desc = descCell.textContent.trim();
                    if (desc !== '') {
                        fetchDropdownData(desc, 'desc', descCell);
                    }
                }, 1000); // Adjust the delay time (in milliseconds) as needed
            }
        });

        descCell.addEventListener('focus', function () {
            isCellFocused = true;
        });

        descCell.addEventListener('blur', function () {
            isCellFocused = false;
        });
    });

    sizeCells.forEach(sizeCell => {
        let isCellFocused = false;
        sizeCell.addEventListener('input', function () {
            clearTimeout(inputTimeout); // Clear the previous timeout
            if (isCellFocused) {
                // Delay showing the dropdown if the cell is focused
                inputTimeout = setTimeout(() => {
                    const size = sizeCell.textContent.trim();
                    if (size !== '') {
                        fetchDropdownData(size, 'size', sizeCell);
                    }
                }, 1000); // Adjust the delay time (in milliseconds) as needed
            }
        });

        sizeCell.addEventListener('focus', function () {
            isCellFocused = true;
        });

        sizeCell.addEventListener('blur', function () {
            isCellFocused = false;
        });
    });

    function fetchDropdownData(value, type, cellElement) {
        // Make an AJAX request to fetch dropdown data from the server based on the value (shape/size)
        // Replace 'fetch_dropdown_data.php' with the actual URL for fetching dropdown data
        fetch(`fetch_dropdown_data.php?value=${value}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    // Create and populate the dropdown
                    const dropdown = document.createElement('select');
                    dropdown.classList.add('dropdown');

                    // Add a default option
                    const defaultOption = document.createElement('option');
                    defaultOption.textContent = `Select ${type.charAt(0).toUpperCase() + type.slice(1)}`;
                    dropdown.appendChild(defaultOption);

                    for (const item of data) {
                        const option = document.createElement('option');
                        option.value = item.lot_no;
                        option.textContent = `${item.description} - ${item.shape} - ${item.size} - ${item.pcs} - ${item.weight}`;
                        dropdown.appendChild(option);
                    }

                    // Clear the content of the cell
                    cellElement.textContent = '';

                    // Append the dropdown to the cell
                    cellElement.appendChild(dropdown);

                    // Add an event listener to handle dropdown selection
                    dropdown.addEventListener('change', function () {
                        const selectedValue = this.value;
                        populateFieldsFromDropdown(selectedValue, cellElement);
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    // Function to populate fields based on the selected value from the dropdown
    function populateFieldsFromDropdown(selectedValue, cellElement) {
        console.log(selectedValue);
        // Make an AJAX request to fetch data for the selected lot_no from the server
        // Replace 'fetch_lot_data.php' with the actual URL for fetching lot data
        fetch(`fetch_lot_data.php?lotNo=${selectedValue}`)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    // Populate the cell with the selected value
                    cellElement.textContent = selectedValue;

                    // Find the parent row (assuming your table row structure)
                    const currentRow = cellElement.closest('tr');

                    // Populate other fields in the current row with data from the server
                    currentRow.querySelector('[name="lot_no"]').textContent = data.lot_no;
                    currentRow.querySelector('[name="desc"]').textContent = data.description;
                    currentRow.querySelector('[name="shape"]').textContent = data.shape;
                    currentRow.querySelector('[name="size"]').textContent = data.size;
                    currentRow.querySelector('[name="wt"]').textContent = data.weight;
                    currentRow.querySelector('[name="pcs"]').textContent = data.pcs;
                    currentRow.querySelector('[name="color"]').textContent = data.color;
                    currentRow.querySelector('[name="clarity"]').textContent = data.clarity;
                    currentRow.querySelector('[name="certificate"]').textContent = data.certificate_no;
                    currentRow.querySelector('[name="rap"]').textContent = data.rap;
                    currentRow.querySelector('[name="disc"]').textContent = data.discount + "%";

                    const rapField = currentRow.querySelector('.rap');
                    const discField = currentRow.querySelector('.disc');
                    const priceField = currentRow.querySelector('.price');
                    const totalField = currentRow.querySelector('.total');
                    const wtField = currentRow.querySelector('.wt');

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

                        newRow.querySelector('[name="desc"]').textContent = data.description;
                        newRow.querySelector('[name="desc"]').setAttribute('contentEditable', 'false');

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


// Function to calculate total_wt and total_tot
function calculateTotals() {
    let totalWt = 0;
    let totalTot = 0;

    // Calculate total_wt and total_tot based on input values in all rows
    const wtInputs = document.querySelectorAll('.editable[name="wt"]');
    const totalInputs = document.querySelectorAll('.editable[name="total"]');

    wtInputs.forEach((input) => {
        const wtValue = Math.abs(parseFloat(input.textContent)) || 0;
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
dayDropdown.value = 5;

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
        xhr.open('GET', `fetch_name.php?input=${name}`, true);
        xhr.send();
    }, 500); // Adjust the delay time (in milliseconds) as needed
});

// Close the dropdown when clicking outside the dropdown container
document.addEventListener('click', function (event) {
    if (!dropdownContainer.contains(event.target)) {
        dropdownContainer.style.display = 'none';
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
    // console.log(total_total);

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

addressInput.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
        const lotNoCell = document.querySelector('[name="lot_no"]');
        if (lotNoCell) {
            lotNoCell.focus(); // Move the focus to the lot_no cell
        }
    }
});

// JavaScript for the "Back" button
function goBackOneStep() {
    window.history.back(); // This will go back one step in the browser's history
}