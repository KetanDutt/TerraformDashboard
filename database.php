<?php


function connectToDatabase() {
    $servername = "localhost";
    $username = "root";
    $password = "9999748948";
    $dbname = "RTC_Performance_Dashboard";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if($conn->connect_error) {
        die("Connection failed: ".$conn->connect_error);
    }

    return $conn;
}

function createUsersTable($conn, $tableName) {
    $tableCheckQuery = "SHOW TABLES LIKE '".$tableName."'";
    $tableCheckResult = $conn->query($tableCheckQuery);

    if($tableCheckResult->num_rows == 0) {
        // Table does not exist, create it
        $createTableQuery = "CREATE TABLE $tableName  (
            id INT AUTO_INCREMENT PRIMARY KEY,
            login_id VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL
        )";

        if($conn->query($createTableQuery) === TRUE) {
            echo "Table '$tableName' created successfully\n";
        } else {
            echo "Error creating table: ".$conn->error."\n";
        }
    }
}

function closeConnection($conn) {
    $conn->close();
}

?>