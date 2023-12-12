function loadComplete() {
    M.AutoInit();
}

function createNewTest() {
    let testName = document.getElementById("testName").value;
    let jmeterVersion = document.getElementById("jmeterVersion").value;
    let MasterCount = document.getElementById("MasterCount").value;
    let SlaveCount = document.getElementById("SlaveCount").value;

    console.log(testName, jmeterVersion, MasterCount, SlaveCount);// Send test data to the server using AJAX

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "dashboard.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                console.log(xhr.responseText);

                // Check for specific response from the server
                if (xhr.responseText.trim() === "RowInsertedSuccessfully") {
                    Swal.fire({
                        icon: "success",
                        title: "Success",
                        text: "Test Added!"
                    }).then((result) => {
                        window.location.href = "/Dashboard";
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: xhr.responseText.trim()
                    });
                }
            } else {
                console.error("Error:", xhr.status);
            }
        }
    };

    // Format the data in the required POST parameter format
    var data = "operation=insert&testName=" + testName + "&jmeterVersion=" + jmeterVersion + "&MasterCount=" + MasterCount + "&SlaveCount=" + SlaveCount;

    // Send the data to the server
    xhr.send(data);
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
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "dashboard.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        console.log(xhr.responseText);

                        // Check for specific response from the server
                        if (xhr.responseText.trim() === "RowDeletedSuccessfully") {
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

            // Format the data in the required POST parameter format
            var data = "operation=delete&testIdToDelete=" + id;

            // Send the data to the server
            xhr.send(data);
        }
    });
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
            title: "Edit Performance Test",
            html: `<div class="row">
                        <div class="input-field">
                            <input type="text" id="testNameEdit" name="testName" required style="color: white;">
                            <label for="testName">Test Name</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field">
                            <select id="jmeterVersionEdit" required>
                                <option value="1.4.5">1.4.5</option>
                                <option value="1.5.76">1.5.76</option>
                                <option value="2.3.5">2.3.5</option>
                            </select>
                            <label>JMeter version</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field">
                            <select id="MasterCountEdit" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                            </select>
                            <label>Master Count</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="input-field">
                            <select id="SlaveCountEdit" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                            </select>
                            <label>Slave Count</label>
                        </div>
                    </div>`,
            focusConfirm: false,
            didOpen: () => {
                let testName = document.getElementById("testName" + id).innerText;
                let jmeterVersion = document.getElementById("jmeterVersion" + id).innerText;
                let MasterCount = document.getElementById("MasterCount" + id).innerText;
                let SlaveCount = document.getElementById("SlaveCount" + id).innerText;

                let testNameEdit = document.getElementById("testNameEdit");
                let jmeterVersionEdit = document.getElementById("jmeterVersionEdit");
                let MasterCountEdit = document.getElementById("MasterCountEdit");
                let SlaveCountEdit = document.getElementById("SlaveCountEdit");

                testNameEdit.value = testName;
                jmeterVersionEdit.value = jmeterVersion;
                MasterCountEdit.value = MasterCount;
                SlaveCountEdit.value = SlaveCount;

                M.AutoInit();

                let elems = document.getElementsByClassName("select-wrapper");
                Array.from(elems).forEach((elem) => {
                    if (!elem.parentElement.className.includes("input-field")) {
                        elem.style.display = "none";
                    }
                });

            },
            preConfirm: () => {
                return [
                    document.getElementById("testNameEdit").value,
                    document.getElementById("jmeterVersionEdit").value,
                    document.getElementById("MasterCountEdit").value,
                    document.getElementById("SlaveCountEdit").value
                ];
            }
        });
        if (formValues) {
            // Swal.fire(JSON.stringify(formValues));
            console.log(formValues);

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "dashboard.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        console.log(xhr.responseText);

                        // Check for specific response from the server
                        if (xhr.responseText.trim().includes("RowEditedSuccessfully")) {
                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: "Test Edited!"
                            }).then((result) => {
                                window.location.href = "/Dashboard";
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Oops...",
                                text: "Test was not Edited"
                            });
                        }
                    } else {
                        console.error("Error:", xhr.status);
                    }
                }
            };

            // Format the data in the required POST parameter format
            var data = "operation=edit&testIdToEdit=" + id + "&testName=" + formValues[0] + "&jmeterVersion=" + formValues[1] + "&MasterCount=" + formValues[2] + "&SlaveCount=" + formValues[3];

            // Send the data to the server
            xhr.send(data);
        }
    })()
}