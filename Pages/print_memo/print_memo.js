// Fetch data from fetch_data.php using JavaScript
fetch("../php_data/fetch_data_company.php")
  .then((response) => response.json())
  .then((data) => {
    const companyName = document.querySelector("#title_table h2");
    const desc = document.querySelector("#title_table h4");
    const phoneNo = document.querySelector("#title_table b");
    const address = document.querySelector("#title_table #address");
    const email = document.querySelector("#title_table #email");
    const disclaimerMemo = document.querySelector("#disclaimer b");
    const bank_details = document.querySelector("#bank_details");

    // Set data in HTML elements
    companyName.textContent = data.company_name;
    desc.textContent = data.desc;
    phoneNo.textContent = `Cell: ${data.phone_no}`;
    address.textContent = data.address;
    email.textContent = `E: ${data.email}`;
    disclaimerMemo.textContent = data.disclaimer_memo;
    bank_details.textContent = data.bank_details;
  })
  .catch((error) => {
    console.error("Error:", error);
  });

// Get references to HTML elements
const memoNo = document.getElementById("memo_no");
const memorandumDayInput = document.getElementById("memorandum_day");
const dateInput = document.getElementById("formatted-date");
const recipientInput = document.getElementById("recipient");
const addressInput = document.getElementById("addressInput");

function getQueryParam(parameterName) {
  const urlSearchParams = new URLSearchParams(window.location.search);
  return urlSearchParams.get(parameterName);
}

// Add an event listener for the memo_no dropdown change event
document.addEventListener("DOMContentLoaded", function () {
  // Get the memo_no query parameter value
  const memo_no = getQueryParam("memo_no");

  memoNo.value = memo_no;

  const selectedMemoNo = memoNo.value;
  if (selectedMemoNo) {
    // Perform a fetch to fetch_memo_details.php with the selected memo_no
    fetch(`fetch_memo_details.php?memo_no=${selectedMemoNo}`)
      .then((response) => response.json())
      .then((data) => {
        // Update the form fields with fetched data
        memorandumDayInput.value = data.memorandum_day;
        const rawDate = new Date(data.memo_date); // Convert the date string to a Date object
        const months = [
          "January",
          "February",
          "March",
          "April",
          "May",
          "June",
          "July",
          "August",
          "September",
          "October",
          "November",
          "December",
        ];

        const day = rawDate.getDate();
        const month = months[rawDate.getMonth()];
        const year = rawDate.getFullYear();

        const formattedDate = `${month} ${day}, ${year}`;
        console.log(formattedDate);
        dateInput.textContent = formattedDate; // Set the formatted date in the input field
        recipientInput.value = data.customer_name;
        addressInput.value = data.address;
      })
      .catch((error) => console.error("Error fetching memo details:", error));
  } else {
    // Clear the form fields if no memo_no is selected
    memorandumDayInput.value = "";
    dateInput.value = "";
    recipientInput.value = "";
    addressInput.value = "";
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
  fetch(`fetch_memo_rows.php?memo_no=${memoNo}`)
    .then((response) => response.json())
    .then((data) => {
      // Call a function to display the data in the table
      displayMemoData(data);
    })
    .catch((error) => {
      console.error("Error fetching data:", error);
    });
}

// Function to display memo data in the table
function displayMemoData(data) {
  const tableBody = document.getElementById("table-body");
  tableBody.innerHTML = ""; // Clear previous rows

  data.forEach((row, index) => {
    const newRow = document.createElement("tr");
    newRow.innerHTML = `
            <td>${index + 1}</td>
            <td>${row.lot_no}</td>
            <td>${row.description}</td>
            <td>${row.shape}</td>
            <td>${row.size}</td>
            <td>${row.pcs}</td>
            <td name="wt">${row.weight}</td>
            <td>${row.color}</td>
            <td>${row.clarity}</td>
            <td>${row.certificate_no}</td>
            <td>${row.rap}</td>
            <td>${row.discount}%</td>
            <td>${row.price}</td>
            <td name="total">${row.total}</td>
            <td>${row.return === null ? "" : row.return}</td>
            <td>${row.kept === null ? "" : row.kept}</td>
            <td name="final_total">${
              row.final_total === null ? "" : row.final_total
            }</td>
        `;
    tableBody.appendChild(newRow);
  });

  totalWeightTotal();
  totalFinalTotal();
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
  const totalTotFinalField = document.querySelectorAll(
    'td[name="final_total"]'
  );
  const finalTotalColumn = document.querySelector('td[name="total_final_tot"]');
  let totalFinal = 0;

  for (let i = 0; i < totalTotFinalField.length; i++) {
    const finalCell = totalTotFinalField[i];
    const finalValue = parseFloat(finalCell.textContent) || 0;
    totalFinal += finalValue;
  }

  finalTotalColumn.textContent = totalFinal.toFixed(2) || 0;
}

// JavaScript code to handle the print button click
document.getElementById("printButton").addEventListener("click", function () {
  // Call the printMemo function to initiate printing
  printMemo();
});

// Function to initiate the printing
function printMemo() {
  // Hide the button when printing by applying a media query
  const style = document.createElement("style");
  style.innerHTML = "@media print { #printButton, #goBack, #editButton { display: none; } #bank_details { display: block; position: fixed; bottom: 0; width: 100%; padding: 10px; border-top: 1px solid #ccc; background-color: #fff; }}";
  document.head.appendChild(style);

  // Clone the original content
  const originalContent = document.querySelector(".printable-content");
  // Generate the duplicated content here
  const originalMemoNo = document.getElementById("memo_no");
  const selectedMemoNo = originalMemoNo.value;

  const duplicateContent = originalContent.cloneNode(true);

  // Hide the duplicate content in the main display
  duplicateContent.classList.add("spacing");

  // Update the memo_no input field in the duplicated content with the selected value
  const duplicatedMemoNo = duplicateContent.querySelector("#memo_no");
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

// JavaScript for the "Back" button
function goBackOneStep() {
  window.location.href = "../landing_page/memo_landing.html";
}

function editMemo() {
  // Get the value of the memoNo input field
  const memoNoValue = memoNo.value;
  // Use encodeURIComponent to properly encode the value
  const encodedMemoNo = encodeURIComponent(memoNoValue);
  // Navigate to the edit_memo page with the memo_no parameter
  window.location.href = `../edit_memo/edit_memo.html?memo_no=${encodedMemoNo}`;
}