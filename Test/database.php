<?php


function connectToDatabase()
{
    $servername = "localhost";
    $username = "root";
    $password = "9999748948";
    $dbname = "RTC_Performance_Dashboard";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function closeConnection($conn)
{
    $conn->close();
}

?>