<?php
// Include the database connection file
require_once('../database.php');

// Check if performance_test table exists
$tableName = 'performance_test';
// if(isset($_SESSION['test'])) {
//     $tableName = $tableName."test";
// }

// Function to check if a table exists in the database
function doesTableExist($tableName, $conn) {
    $query = "SHOW TABLES LIKE '$tableName'";
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

// Function to create the performance_test table if it doesn't exist
function createPerformanceTestTable($conn, $tableName) {
    $query = "CREATE TABLE $tableName (
            id INT AUTO_INCREMENT PRIMARY KEY,
            testName VARCHAR(255) NOT NULL,
            jmeterVersion VARCHAR(50) NOT NULL,
            MasterCount INT NOT NULL,
            MasterConfig VARCHAR(255) NOT NULL,
            SlaveCount INT NOT NULL,
            SlaveConfig VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
    mysqli_query($conn, $query);
}

// Function to insert a row into the performance_test table
function insertPerformanceTestRow($conn, $tableName, $testName, $jmeterVersion, $MasterCount, $SlaveCount) {
    $query = "INSERT INTO $tableName (testName, jmeterVersion, MasterCount, MasterConfig, SlaveCount, SlaveConfig)
        VALUES ('$testName', '$jmeterVersion', $MasterCount,'CPU: 6;RAM: 4GB ', $SlaveCount,'CPU: 6;RAM: 4GB')
    ";
    mysqli_query($conn, $query);
}

// Function to insert or update a row in the performance_test table
function updatePerformanceTestRow($conn, $tableName, $testIdToEdit, $testName, $jmeterVersion, $MasterCount, $SlaveCount) {
    // Update operation
    $query = "UPDATE $tableName
            SET testName = '$testName',
                jmeterVersion = '$jmeterVersion',
                MasterCount = $MasterCount,
                SlaveCount = $SlaveCount
            WHERE id = '$testIdToEdit'
        ";
    mysqli_query($conn, $query);
}

// Function to delete a row from the performance_test table
function deletePerformanceTestRow($conn, $tableName, $testId) {
    $query = "DELETE FROM $tableName WHERE id = '$testId'";
    mysqli_query($conn, $query);
}

// Database connection
$conn = connectToDatabase();

if(!doesTableExist($tableName, $conn)) {
    // If not, create the table
    createPerformanceTestTable($conn, $tableName);
}
if(isset($_POST['operation'])) {
    $operation = $_POST['operation'];

    if($operation === 'insert') {
        // Get POST parameters
        $testName = isset($_POST['testName']) ? $_POST['testName'] : '';
        $jmeterVersion = isset($_POST['jmeterVersion']) ? $_POST['jmeterVersion'] : '';
        $MasterCount = isset($_POST['MasterCount']) ? $_POST['MasterCount'] : '';
        $SlaveCount = isset($_POST['SlaveCount']) ? $_POST['SlaveCount'] : '';

        // Insert a row into the performance_test table using POST parameters
        if($testName && $jmeterVersion && $MasterCount && $SlaveCount) {
            insertPerformanceTestRow($conn, $tableName, $testName, $jmeterVersion, $MasterCount, $SlaveCount);
            echo "RowInsertedSuccessfully";
        } else {
            echo "Error: Missing required parameters!";
        }
    } elseif($operation === 'delete') {
        // Example parameter for deletion
        $testIdToDelete = isset($_POST['testIdToDelete']) ? $_POST['testIdToDelete'] : '';

        // Delete a row from the performance_test table
        if($testIdToDelete) {
            deletePerformanceTestRow($conn, $tableName, $testIdToDelete);
            echo "RowDeletedSuccessfully";
        } else {
            echo "Error: Missing required parameters for deletion!";
        }
    } elseif($operation === "edit") {
        // Get POST parameters
        $testIdToEdit = isset($_POST['testIdToEdit']) ? $_POST['testIdToEdit'] : '';
        $testName = isset($_POST['testName']) ? $_POST['testName'] : '';
        $jmeterVersion = isset($_POST['jmeterVersion']) ? $_POST['jmeterVersion'] : '';
        $MasterCount = isset($_POST['MasterCount']) ? $_POST['MasterCount'] : '';
        $SlaveCount = isset($_POST['SlaveCount']) ? $_POST['SlaveCount'] : '';

        // Insert a row into the performance_test table using POST parameters
        if($testName && $jmeterVersion && $MasterCount && $SlaveCount) {
            updatePerformanceTestRow($conn, $tableName, $testIdToEdit, $testName, $jmeterVersion, $MasterCount, $SlaveCount);
            echo "RowEditedSuccessfully";
        } else {
            echo "Error: Missing required parameters!";
        }
    }
}

// Close the database connection
closeConnection($conn);
?>