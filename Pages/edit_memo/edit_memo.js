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

    fetch('isClose.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ memo_no: memo_no }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'close') {
                // Disable the "Add Row" and "Save Memo" buttons
                document.getElementById("addButton").disabled = true;
                document.getElementById("saveButton").disabled = true;
                document.getElementById("closeButton").disabled = true;

                const tableCells = document.querySelectorAll('.table_data tbody td.editable');
                tableCells.forEach(cell => {
                    cell.contentEditable = "false"; // Use "false" as a string to set contentEditable to "false"
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });

    fetchMemoData(memoNo.value);
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

// Add a new row when the "Add Row" button is clicked
document.getElementById("addButton").addEventListener("click", addRowEmpty);

// Close Mmeo button
document.getElementById("closeButton").addEventListener("click", closeMemo);

// Print Invoice button
document.getElementById("printButton").addEventListener("click", printInvoice);

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
        <td contenteditable="true" name="disc" class="editable disc">${data.discount}%</td>
        <td contenteditable="true" name="price" class="editable price">${data.price}</td>
        <td name="total" class="editable total">${data.total}</td>
        <td contenteditable="true" name="return" class="editable">${data.return === null ? '' : data.return}</td>
        <td contenteditable="true" name="kept" class="editable">${data.kept === null ? '' : data.kept}</td>
        <td name="final_total" class="editable">${data.final_total === null ? '' : data.final_total}</td>
        <td class="delete-cell"><span class="delete-icon">Delete</span></td>
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
        
         if (targetCell.getAttribute('name') === 'return') {
            calculateTotals();
        }
    });

    const shapeCells = document.querySelectorAll('td[name="shape"]');
    const sizeCells = document.querySelectorAll('td[name="size"]');
    const descCells = document.querySelectorAll('td[name="desc"]');
    let inputTimeout; // Variable to store the input delay timer

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
        fetch(`../Memo/fetch_dropdown_data.php?value=${value}`)
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
        fetch(`../Memo/fetch_lot_data.php?lotNo=${selectedValue}`)
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

                    calculatePriceAndTotal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

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

    // Function to calculate total_wt and total_tot
    function calculateTotals() {
        let totalWt = 0;
        let totalTot = 0;
        let totalKept = 0;

        // Calculate total_wt and total_tot based on input values in all rows
        const wtInputs = document.querySelectorAll('.editable[name="wt"]');
        const totalInputs = document.querySelectorAll('.editable[name="total"]');
        const keptInputs = document.querySelectorAll('.editable[name="kept"]');

        wtInputs.forEach((input) => {
            const wtValue = Math.abs(parseFloat(input.textContent)) || 0;
            totalWt += wtValue;
        });

        totalInputs.forEach((input) => {
            const totalValue = parseFloat(input.textContent) || 0;
            totalTot += totalValue;
        });
        
        keptInputs.forEach((input) => {
            const keptValue = parseFloat(input.textContent) || 0;
            totalKept += keptValue;
        });

        // Update the corresponding <td> elements for the totals
        const totalWtField = document.querySelector('.total_wt');
        const totalTotField = document.querySelector('.total_tot');
        const totalKeptField = document.querySelector('.total_kept');
        totalWtField.textContent = totalWt.toFixed(2);
        totalWtField.value = totalWt.toFixed(2);
        totalTotField.textContent = totalTot.toFixed(2);
        totalTotField.value = totalTot.toFixed(2);
        totalKeptField.textContent = totalKept.toFixed(2);
        totalKeptField.value = totalKept.toFixed(2);
    }
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
        <td class="delete-cell"><span class="delete-icon">Delete</span></td>
`;
    tableBody.appendChild(newRow);

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
        fetch(`../Memo/fetch_dropdown_data.php?value=${value}`)
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
        fetch(`../Memo/fetch_lot_data.php?lotNo=${selectedValue}`)
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

                    calculatePriceAndTotal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    // Attach a click event listener to the delete icon
    const deleteIcon = newRow.querySelector('.delete-icon');
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

    // Function to calculate total_wt and total_tot
    function calculateTotals() {
        let totalWt = 0;
        let totalTot = 0;
        let totalKept = 0;

        // Calculate total_wt and total_tot based on input values in all rows
        const wtInputs = document.querySelectorAll('.editable[name="wt"]');
        const totalInputs = document.querySelectorAll('.editable[name="total"]');
        const keptInputs = document.querySelectorAll('.editable[name="kept"]');

        wtInputs.forEach((input) => {
            const wtValue = Math.abs(parseFloat(input.textContent)) || 0;
            totalWt += wtValue;
        });

        totalInputs.forEach((input) => {
            const totalValue = parseFloat(input.textContent) || 0;
            totalTot += totalValue;
        });
        
        keptInputs.forEach((input) => {
            const keptValue = parseFloat(input.textContent) || 0;
            totalKept += keptValue;
        });

        // Update the corresponding <td> elements for the totals
        const totalWtField = document.querySelector('.total_wt');
        const totalTotField = document.querySelector('.total_tot');
        const totalKeptField = document.querySelector('.total_kept');
        totalWtField.textContent = totalWt.toFixed(2);
        totalWtField.value = totalWt.toFixed(2);
        totalTotField.textContent = totalTot.toFixed(2);
        totalTotField.value = totalTot.toFixed(2);
        totalKeptField.textContent = totalKept.toFixed(2);
        totalKeptField.value = totalKept.toFixed(2);
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
        
        if (targetCell.getAttribute('name') === 'return') {
            calculateTotals();
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
function saveData(isCalledFromCloseMemo = false) {
    const tableRows = document.querySelectorAll('#table-body tr');
    const data = [];
    const memo_no = document.getElementById("memo_no").value;
    const date = document.getElementById("date").value;
    const memorandum_day = document.getElementById("memorandum_day").value;
    const name = document.getElementById("recipient").value;
    const address = document.getElementById("addressInput").value;
    const total_wt = document.getElementById("total_wt").textContent;
    const total_kept = document.getElementById("total_kept").textContent;
    const total_final_tot = document.getElementById("total_final_tot").textContent;

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
        total_wt: total_kept !== '' && total_kept !== 0.00 ? total_kept : total_wt,
        total_final_tot: total_final_tot,
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
            if (response.ok && !isCalledFromCloseMemo) {
                // Display a success message
                const successMessage = document.getElementById('successMessage');
                successMessage.textContent = 'Data saved successfully!';

                // Optionally, you can clear the message after a few seconds
                setTimeout(() => {
                    successMessage.textContent = '';
                    location.reload();
                }, 3000);// Clear the message after 3 seconds
            } else if (!isCalledFromCloseMemo) {
                // Handle other response statuses here if needed
                console.error('Server returned an error:', response.statusText);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
        });
}

function closeMemo() {
    saveData(true);
    const tableRows = document.querySelectorAll('#table-body tr');
    const data = [];
    // Get the memo_no value from your HTML, assuming it's stored in an element with an id "memo_no"
    const memo_no = document.getElementById("memo_no").value;

    tableRows.forEach((row) => {
        const rowData = {};
        row.querySelectorAll('td.editable').forEach((cell) => {
            const name = cell.getAttribute('name');
            rowData[name] = cell.textContent.trim();
        });
        data.push(rowData);
    });

    // Create a data object to send the memo_no value to the server
    const requestData = {
        memo_no: memo_no,
        data: data,
    };

    // Send a POST request to closeMemo.php
    fetch('closeMemo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData),
    })
        .then((response) => {
            if (response.ok) {
                // Display a success message
                const successMessage = document.getElementById('successMessage');
                successMessage.textContent = 'Memo closed successfully!';

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
        .catch(error => {
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

function printInvoice() {
    saveData();
    const memo_no = document.getElementById("memo_no").value;
    window.location.href = `../print_invoice/print_invoice.html?memo_no=${encodeURIComponent(memo_no)}`;
}

// JavaScript for the "Back" button
function goBackOneStep() {
    window.history.back(); // This will go back one step in the browser's history
}

// Reload the page when the history changes (e.g., after using window.history.back())
window.addEventListener("popstate", function (event) {
    location.reload();
});

function printMemo() {
    saveData();
    const memo_no = document.getElementById("memo_no").value;
    window.location.href = `../print_memo/print_memo.html?memo_no=${encodeURIComponent(memo_no)}`;
}