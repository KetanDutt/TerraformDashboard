<?php

// Replace these variables with your database credentials
$servername = "localhost";
$username = "root";
$password = "9999748948";
$dbname = "RTC_Performance_Dashboard";

// Function to create a database connection
function createConnection($servername, $username, $password, $dbname)
{
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Function to check and create master and slave tables if they don't exist
function checkAndCreateTables($conn)
{
    try {
        // SQL to check if the master table exists
        $checkMasterTable = "SHOW TABLES LIKE 'master_instances'";
        $resultMaster = $conn->query($checkMasterTable);

        // SQL to check if the slave table exists
        $checkSlaveTable = "SHOW TABLES LIKE 'slave_instances'";
        $resultSlave = $conn->query($checkSlaveTable);

        // SQL to check if the performance table exists
        $checkPerformanceTable = "SHOW TABLES LIKE 'performance_test'";
        $resultPerformance = $conn->query($checkPerformanceTable);

        // Create master table if it doesn't exist
        if ($resultMaster->num_rows == 0) {
            $createMasterTable = "CREATE TABLE master_instances (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                instance_name VARCHAR(255) NOT NULL,
                public_ip VARCHAR(15) NOT NULL,
                private_ip VARCHAR(15) NOT NULL,
                instance_id VARCHAR(30) NOT NULL,
                master_name VARCHAR(255) NOT NULL
            )";

            if ($conn->query($createMasterTable) === TRUE) {
                logMessage("Master table created successfully");
            } else {
                throw new Exception("Error creating master table: " . $conn->error);
            }
        } else {
            logMessage("Master Table Exists");
        }

        // Create slave table if it doesn't exist
        if ($resultSlave->num_rows == 0) {
            $createSlaveTable = "CREATE TABLE slave_instances (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                instance_name VARCHAR(255) NOT NULL,
                public_ip VARCHAR(15) NOT NULL,
                private_ip VARCHAR(15) NOT NULL,
                instance_id VARCHAR(30) NOT NULL,
                slave_name VARCHAR(255) NOT NULL
            )";

            if ($conn->query($createSlaveTable) === TRUE) {
                logMessage("Slave table created successfully");
            } else {
                throw new Exception("Error creating slave table: " . $conn->error);
            }
        } else {
            logMessage("Slave Table Exists");
        }

        // Create performance table if it doesn't exist
        if ($resultPerformance->num_rows == 0) {
            $createPerformanceTable = "CREATE TABLE performance_test (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                instance_name VARCHAR(255) NOT NULL UNIQUE,
                jmeter_version VARCHAR(20) NOT NULL,
                InstanceType VARCHAR(20) NOT NULL,
                slave_no INT NOT NULL,
                master_no INT NOT NULL,
                masterIP VARCHAR(255) NOT NULL,
                slaveIP VARCHAR(255) NOT NULL
            )";

            if ($conn->query($createPerformanceTable) === TRUE) {
                logMessage("Performance table created successfully");
            } else {
                throw new Exception("Error creating performance table: " . $conn->error);
            }
        } else {
            logMessage("Performance Table Exists");
        }
    } catch (Exception $e) {
        logMessage("Error: " . $e->getMessage());
    }
}

// Function to log messages to a file
function logMessage($message)
{
    echo date('Y-m-d H:i:s') . ' ' . $message . "<br>";
    // $logFile = 'insertIntoMasterSlaveTable.txt';
    // file_put_contents($logFile, date('Y-m-d H:i:s') . ' ' . $message . "\n", FILE_APPEND);
}

// Function to insert data into master and slave tables
function insertIntoMasterSlaveTable($conn, $data, $apiResponse)
{
    $instance_name = $data['instance_name'];

    insertIntoPerformanceTable($conn, $data, $apiResponse);

    if ($apiResponse === null) {
        logMessage("Error decoding JSON response");
    } else {

        foreach ($apiResponse['master_instances'] as $masterInstance) {
            $masterData = json_decode($masterInstance, true);
            insertIntoMasterTable($conn, $masterData, $instance_name);
        }
        foreach ($apiResponse['slave_instances'] as $slaveInstance) {
            $slaveData = json_decode($slaveInstance, true);
            insertIntoSlaveTable($conn, $slaveData, $instance_name);
        }
    }
}

// Function to insert data into the performance table
function insertIntoPerformanceTable($conn, $data, $apiResponse)
{
    try {
        $instance_name = $data['instance_name'];
        $jmeter_version = $data['jmeter_version'];
        $InstanceType = $data['InstanceType'];
        $slave_no = $data['slave_no'];
        $master_no = $data['master_no'];

        $masterStr = "";
        $slaveStr = "";
        foreach ($apiResponse['master_instances'] as $masterInstance) {
            $masterData = json_decode($masterInstance, true);
            $masterStr .= $masterData["public_master_ip"] . "<br>";
        }
        foreach ($apiResponse['slave_instances'] as $slaveInstance) {
            $slaveData = json_decode($slaveInstance, true);
            $slaveStr .= $slaveData["public_slave_ip"] . "<br>";
        }

        $sql = "INSERT INTO performance_test (instance_name, jmeter_version, InstanceType, slave_no, master_no, masterIP, slaveIP) VALUES ('$instance_name', '$jmeter_version', '$InstanceType', '$slave_no', '$master_no','$masterStr','$slaveStr')";

        if ($conn->query($sql) === TRUE) {
            logMessage("Performance record inserted successfully");
        } else {
            throw new Exception("Error inserting performance record: " . $conn->error);
        }
    } catch (Exception $e) {
        logMessage("Error: " . $e->getMessage());
    }
}

// Function to insert data into the master table
function insertIntoMasterTable($conn, $masterData, $instance_name)
{
    try {
        $publicMasterIp = $masterData['public_master_ip'];
        $privateMasterIp = $masterData['private_master_ip'];
        $instanceIdMaster = $masterData['Instance_id_master'];
        $masterInstanceName = $masterData['Master_instance_Name'];

        $sql = "INSERT INTO master_instances (instance_name, public_ip, private_ip, instance_id, master_name) VALUES ('$instance_name','$publicMasterIp', '$privateMasterIp', '$instanceIdMaster', '$masterInstanceName')";

        if ($conn->query($sql) === TRUE) {
            logMessage("Master record inserted successfully");
        } else {
            throw new Exception("Error inserting master record: " . $conn->error);
        }
    } catch (Exception $e) {
        logMessage("Error: " . $e->getMessage());
    }
}

// Function to insert data into the slave table
function insertIntoSlaveTable($conn, $slaveData, $instance_name)
{
    try {
        $publicSlaveIp = $slaveData['public_slave_ip'];
        $privateSlaveIp = $slaveData['private_slave_ip'];
        $instanceIdSlave = $slaveData['Instance_id_slave'];
        $slaveInstanceName = $slaveData['Slave_instance_Name'];

        $sql = "INSERT INTO slave_instances (instance_name, public_ip, private_ip, instance_id, slave_name) VALUES ('$instance_name','$publicSlaveIp', '$privateSlaveIp', '$instanceIdSlave', '$slaveInstanceName')";

        if ($conn->query($sql) === TRUE) {
            logMessage("Slave record inserted successfully");
        } else {
            throw new Exception("Error inserting slave record: " . $conn->error);
        }
    } catch (Exception $e) {
        logMessage("Error: " . $e->getMessage());
    }
}

// $apiResponse = '{
//   "master_instances": [
//     "{\"public_master_ip\": \"54.198.214.1\", \"private_master_ip\": \"172.31.35.22\", \"Instance_id_master\": \"i-05109227c016e461f\", \"Master_instance_Name\": \"rtc_master_461f\"}",
//     "{\"public_master_ip\": \"54.198.214.1\", \"private_master_ip\": \"172.31.35.22\", \"Instance_id_master\": \"i-05109227c016e461f\", \"Master_instance_Name\": \"rtc_master_461f\"}"
//   ],
//   "slave_instances": [
//     "{\"public_slave_ip\": \"54.234.101.49\", \"private_slave_ip\": \"172.31.40.215\", \"Instance_id_slave\": \"i-030cddf40b4adb34e\", \"Slave_instance_Name\": \"rtc_slave_b34e\"}",
//     "{\"public_slave_ip\": \"54.234.101.49\", \"private_slave_ip\": \"172.31.40.215\", \"Instance_id_slave\": \"i-030cddf40b4adb34e\", \"Slave_instance_Name\": \"rtc_slave_b34e\"}"
//   ]
// }';

// try {
//     // Call the function to create tables
//     $conn = createConnection($servername, $username, $password, $dbname);
//     checkAndCreateTables($conn);

//     // Call the function to insert data into tables
//     $instance_name = "Test_From_Script";
//     $apiResponse = json_decode($apiResponse, TRUE);
//     insertIntoMasterSlaveTable($conn, $instance_name, $apiResponse);
// } finally {
//     // Close the database connection
//     if ($conn) {
//         $conn->close();
//     }
// }
?>