
function validateLogin() {
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;

    // Send login data to the server using AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "login.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            console.log(xhr.responseText)

            // Redirect to the dashboard if login is successful
            if (xhr.responseText === "LoginSuccessful") {
                Swal.fire({
                    icon: "success",
                    // title: "Oops...",
                    text: "Login Successful"
                }).then((result) => {
                    window.location.href = "/Dashboard";
                });
                return;
            }
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Login Unsuccessful"
            });
        }
    };
    xhr.send("login&username=" + username + "&password=" + password);
}

function registerUser() {
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;

    // Send login data to the server using AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "login.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            console.log(xhr.responseText)

            // Redirect to the dashboard if login is successful
            if (xhr.responseText.includes("RegistrationSuccessful")) {
                Swal.fire({
                    icon: "success",
                    // title: "Oops...",
                    text: "Registration Successful"
                }).then((result) => {
                    window.location.href = "/Dashboard";
                });
                return;
            }
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Registration Unsuccessful"
            });
        }
    };
    xhr.send("register&username=" + username + "&password=" + password);
}