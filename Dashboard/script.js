function loadComplete() {
    M.AutoInit();
}

let Loading = false;
function showLoadingDialog(title) {
    Loading = true;
    Swal.fire({
        title: title,
        // html: html,
        timer: 2000,
        allowOutsideClick: false,
        willOpen: () => {
            Swal.showLoading();
            timerInterval = setInterval(() => {
                if (Loading) {
                    Swal.increaseTimer(2000);
                } else {
                    console.log("Loading complete");
                    Swal.stopTimer();
                    Swal.hideLoading();
                    Swal.close();
                    clearInterval(timerInterval);
                }
            }, 100);
        }
    }).then(() => { });

    let elem = [...document.getElementsByClassName("swal2-loader")[0].parentElement.children];
    elem.forEach(element => {
        if (!element.className.includes("swal2-loader"))
            element.style.display = "none";
    });
}

function createNewTest() {
    showLoadingDialog('Creating New Instances');

    var MasterNo = document.getElementById("MasterNo").value;
    var SlaveNo = document.getElementById("SlaveNo").value;
    var InstanceType = document.getElementById("InstanceType").value;
    var InstanceName = document.getElementById("InstanceName").value;
    var JmeterVersion = document.getElementById("JmeterVersion").value;
    var jwtToken = document.getElementById("jwtToken").value;

    // Send create instance data to the server using AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../Api.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            console.log(xhr.responseText);
            Loading = false;

            // Redirect to the dashboard if login is successful
            if (xhr.responseText.includes("CreatedSuccessfully")) {
                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: "Created Successfully"
                }).then((result) => {
                    window.location.href = "/Dashboard";
                });
                return;
            }
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Creation Failed"
            });
        }
    };

    xhr.send("operation=create" +
        "&jwtToken=" + jwtToken +
        "&MasterNo=" + MasterNo +
        "&SlaveNo=" + SlaveNo +
        "&InstanceType=" + InstanceType +
        "&InstanceName=" + InstanceName +
        "&JmeterVersion=" + JmeterVersion);
}

function deleteTest(id) {
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            showLoadingDialog('Deleting Instances');
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../Api.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        console.log(xhr.responseText);

                        // Check for specific response from the server
                        if (xhr.responseText.trim().includes("DeletedSuccessfully")) {
                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: "Test Deleted!"
                            }).then((result) => {
                                window.location.href = "/Dashboard";
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                text: "Test was not Deleted"
                            });
                        }
                    } else {
                        console.error("Error:", xhr.status);
                    }
                }
            };

            var jwtToken = document.getElementById("jwtToken").value;
            // Format the data in the required POST parameter format
            var data = "operation=delete&testIdToDelete=" + id
                + "&jwtToken=" + jwtToken;

            // Send the data to the server
            xhr.send(data);
        }
    });
}

function configureTest(id) {
    showLoadingDialog('Configuring Instances');
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../Api.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                console.log(xhr.responseText);

                // Check for specific response from the server
                if (xhr.responseText.trim().includes("ConfiguredSuccessfully")) {
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: "Test Configured Successfully!"
                    }).then((result) => {
                        window.location.href = "/Dashboard";
                    });
                } else {
                    str = xhr.responseText.slice(1)
                    str = str.replaceAll("\"}ConfiguredUnsuccessfully", "").split("{\"error\": \"")[1]
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        html: "<p>Test was not Configured<br>" + str + "</p>"
                    });
                }
            } else {
                console.error("Error:", xhr.status);
            }
        }
    };

    var jwtToken = document.getElementById("jwtToken").value;
    // Format the data in the required POST parameter format
    var data = "operation=configureSlave&id=" + id
        + "&jwtToken=" + jwtToken;

    console.log(data)
    // Send the data to the server
    xhr.send(data);
}


function logout() {
    Swal.fire({
        title: "Are you sure?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, Logout!"
    }).then((result) => {
        if (result.isConfirmed) {
            // Send login data to the server using AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "../login.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    console.log(xhr.responseText)

                    // Redirect to the dashboard if login is successful
                    if (xhr.responseText.includes("LogoutSuccessful")) {
                        Swal.fire({
                            icon: "success",
                            // title: "Oops...",
                            text: "Logout Successful"
                        }).then((result) => {
                            window.location.href = "/Dashboard";
                        });
                        return;
                    }
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Logout Unsuccessful"
                    });
                }
            };
            xhr.send("logout");
        }
    });
}

function editTest(id) {
    (async () => {
        const { value: formValues } = await Swal.fire({
            title: "Upload CSV",
            html: `
                    <div class="">
                        <div class="file-field input-field">
                            <button class="swal2-confirm swal2-styled btn" type="submit" name="action"
                                style="margin:0px;background-color:#66e088;border-radius: 1vh;display: flex;flex-direction: row;flex-wrap: nowrap;align-content: center;justify-content: center;align-items: center;"
                                >
                                <span>CSV File</span>
                                <input id="CSVFileInput" type="file">
                            </button>
                            <div class="file-path-wrapper">
                                <input class="file-path validate" type="text" placeholder="Upload CSV files">
                            </div>
                        </div>
                    </div>
                    `,
            focusConfirm: false,
            preConfirm: () => {
                return document.getElementById("CSVFileInput");
            }
        });
        if (formValues) {
            // Check if a file is selected
            if (formValues.files.length > 0) {
                var file = formValues.files[0];
                var reader = new FileReader();

                // Read the file as text
                reader.readAsText(file);

                // When the file is loaded
                reader.onload = function (event) {
                    var csvContent = event.target.result;
                    let csvData = csvContent.trim().split("\r\n");
                    for (let i = 0; i < csvData.length; i++) {
                        csvData[i] = csvData[i].trim().split(",")
                        for (let j = 0; j < csvData[i].length; j++) {
                            csvData[i][j] = csvData[i][j].trim()
                        }
                    }

                    let elem = document.getElementById("slave_no" + id).innerText.replaceAll("info", "");

                    // Split the CSV data into 5 equal parts
                    const totalRows = csvData.length;
                    const parts = elem;
                    const rowsPerPart = Math.ceil(totalRows / parts);

                    // Create an array to store the parts
                    let csvDataParts = [];

                    // Iterate over the parts
                    for (let i = 0; i < parts; i++) {
                        // Get the start and end indices for each part
                        const start = i * rowsPerPart;
                        const end = Math.min((i + 1) * rowsPerPart, totalRows);

                        // Extract the rows for the current part
                        const partData = csvData.slice(start, end);

                        // Specify the desired file name (e.g., "your_filename.csv")
                        const fileName = `csv_Data_${i}.csv`;

                        // Append the file name to the Blob constructor
                        var csvBlob = new Blob([partData.map(row => row.join(",")).join("\r\n")], { type: "text/csv" });
                        csvBlob.lastModifiedDate = new Date();
                        csvBlob.name = fileName;

                        // Store the part in the array
                        csvDataParts.push(csvBlob);
                    }

                    // Display the 5 equal parts of the CSV content
                    let ip_address = "54.91.20.124"
                    let csvDataFile = csvDataParts[0]
                    let jwtToken = document.getElementById("jwtToken").value;
                    showLoadingDialog('Uploading Files');

                    var formData = new FormData();
                    formData.append('csvData', csvDataFile);  // Replace with your actual file variable
                    formData.append('ip_address', ip_address);
                    formData.append('operation', 'uploadData');
                    formData.append('jwtToken', jwtToken);

                    // Assuming you have the correct endpoint for your API.php
                    const apiUrl = '../Api.php';

                    fetch(apiUrl, {
                        method: 'POST',
                        body: formData,
                    })
                        .then(response => response.text())
                        .then(data => {
                            // Handle the response data here
                            console.log('Response:', data);
                        })
                        .catch(error => {
                            // Handle errors here
                            console.error('Error:', error);
                        });
                }
            } else {
                alert("Please select a CSV file.");
            }
        }
    })()
}