<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memo</title>
    <link rel="stylesheet" href="memo.css">
</head>

<body>
    <table class="header_top">
        <tr>
            <td colspan="3" id="header_table">
                <h3><select id="dayDropdown">
                        <?php
                        for ($i = 1; $i <= 30; $i++) {
                            echo "<option value='$i'>$i</option>";
                        }
                        ?>
                    </select> - DAY MEMORANDUM</h3>
            </td>
        </tr>
        <tr>
            <td rowspan="3"><img src="../php_data/getImage.php" id="logo" /></td>
            <td rowspan="3" id="title_table">
                <h2></h2>
                <h4></h4>
                <b></b><br />
                <p id="address"></p>
                <p id="email"></p>
            </td>
            <td colspan="3"><b>Memo no.</b></td>
        </tr>
        <tr>
            <td colspan="3">
                <div class="date-container">
                    <label for="date"><b>Date</b></label>
                    <input type="date" id="date" name="date">
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <div class="underline-input">
                    <label for="recipient"><b>To,</b></label>
                    <input type="text" id="recipient" name="recipient">
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" id="disclaimer"><b></b>
            </td>
        </tr>
    </table>

    <table class="table_data">
        <thead>
            <tr id="header">
                <th>Sr No</th>
                <th>Lot No</th>
                <th>Description</th>
                <th>Shape</th>
                <th>Size</th>
                <th>Pcs</th>
                <th>Wt (cts)</th>
                <th>Color</th>
                <th>Clarity</th>
                <th>Certificate</th>
                <th>Rap ($)</th>
                <th>Discount</th>
                <th>Price ($)</th>
                <th>Total</th>
                <th>Return</th>
                <th>Kept</th>
                <th>Final Total</th>
            </tr>
        </thead>
        <tbody id="table-body">
            <!-- JavaScript will generate rows here -->
        </tbody>
        <tr>
            <td><b>Total</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <div class="form-group" style="text-align: center;">
        <input type="submit" value="Submit Memo" id="submitMemo">
    </div>

    <script>
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
        <td contenteditable="true" name="wt" class="editable"></td>
        <td contenteditable="true" name="color" class="editable"></td>
        <td contenteditable="true" name="clarity" class="editable"></td>
        <td contenteditable="true" name="certificate" class="editable"></td>
        <td contenteditable="true" name="rap" class="editable"></td>
        <td contenteditable="true" name="disc" class="editable"></td>
        <td name="price"></td>
        <td name="total"></td>
        <td contenteditable="true" name="return" class="editable"></td>
        <td contenteditable="true" name="kept" class="editable"></td>
        <td name="final_total"></td>
    `;
            tableBody.appendChild(newRow);
        }

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
                    // If any cell in the last row has content, add a new row
                    addRow();
                    break;
                }
            }
        });


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
    </script>
</body>

</html>