<?php

require_once 'database.php';

// Connect to the database
$conn = connectToDatabase();

$tableName = 'users';
// if(isset($_SESSION['test'])) {
//     $tableName = $tableName."test";
// }

// Create the users table if it doesn't exist
createUsersTable($conn, $tableName);

// Handle user registration
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $newUsername = $_POST['username'];
    $newPassword = $_POST['password'];

    // Check if the username is already taken
    $checkUserQuery = "SELECT * FROM $tableName WHERE login_id = '$newUsername'";
    $checkUserResult = $conn->query($checkUserQuery);

    if($checkUserResult->num_rows > 0) {
        echo "Username already taken. Please choose a different username.";
    } else {
        // Insert new user into the database
        $insertUserQuery = "INSERT INTO $tableName  (login_id, password) VALUES ('$newUsername', '$newPassword')";
        if($conn->query($insertUserQuery) === TRUE) {
            echo "RegistrationSuccessful";
        } else {
            echo "Error: ".$insertUserQuery."<br>".$conn->error;
        }
    }
}

// Handle login
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query the database
    $loginQuery = "SELECT * FROM $tableName WHERE login_id = '$username' AND password = '$password'";
    $loginResult = $conn->query($loginQuery);

    if($loginResult->num_rows > 0) {
        // Set session timeout to 30 minutes
        ini_set('session.gc_maxlifetime', 1800);

        // Set secure and httponly flags for session cookie
        session_set_cookie_params(['secure' => true, 'httponly' => true]);

        // User exists, set session variable and simulate login
        session_start();

        // Regenerate session ID after successful login
        session_regenerate_id(true);
        $_SESSION['username'] = $username;
        $_SESSION['test'] = false;
        echo "LoginSuccessful";

    } else {
        // User does not exist
        echo "Invalid username or password";
    }
}

// Handle login
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    // Example of logout functionality
    if(!isset($_SESSION)) {
        session_start();
    }
    session_destroy();
    echo "LogoutSuccessful";
}

// Close the connection
closeConnection($conn);

?>