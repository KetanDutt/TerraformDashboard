<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once("Test/database.php");
require_once("Test/insertIntoMasterSlaveTable.php");
require_once("Test/deleteInstances.php");
require_once("Test/configureSlave.php");

// Function to make a POST request
function makePostRequest($url, $data, $headers)
{
    $options = array(
        'http' => array(
            'header' => $headers,
            'method' => 'POST',
            'content' => json_encode($data),
            'ignore_errors' => true, // Handle HTTP errors manually
        ),
    );

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    echo $result;
    return json_decode($result, true);
    // return $result;
}

// Base URL
$baseUrl = 'http://23.23.92.146:8080/';

// Set operation from POST data
$operation = isset($_POST["operation"]) ? $_POST["operation"] : "";

// JWT token - replace with your actual JWT token
$jwtToken = isset($_POST['jwtToken']) ? $_POST['jwtToken'] : "";

// Common headers
$headers = "Content-type: application/json\r\nAuthorization: $jwtToken";

// Get parameters from POST data for each operation
switch ($operation) {
    case 'auth':
        $url = $baseUrl . 'auth';
        $data = array(
            'username' => isset($_POST["username"]) ? $_POST["username"] : "",
            'password' => isset($_POST["password"]) ? $_POST["password"] : ""
        );
        $result = makePostRequest($url, $data, $headers);

        if (isset($result['token'])) {
            // Set session timeout to 30 minutes
            ini_set('session.gc_maxlifetime', 1800);

            // Set secure and httponly flags for session cookie
            session_set_cookie_params(['secure' => true, 'httponly' => true]);

            // User exists, set session variable and simulate login
            session_start();

            // Regenerate session ID after successful login
            session_regenerate_id(true);
            $_SESSION['username'] = isset($_POST["username"]) ? $_POST["username"] : "";
            $_SESSION['jwtToken'] = $result['token'];

            echo "LoginSuccessful";
        } else {
            echo "LoginUnsuccessful";
        }
        break;
    case 'create':
        $url = $baseUrl . 'create';
        $data = array(
            'master_no' => isset($_POST["MasterNo"]) ? (int) $_POST["MasterNo"] : 0,
            'slave_no' => isset($_POST["SlaveNo"]) ? (int) $_POST["SlaveNo"] : 0,
            'InstanceType' => isset($_POST["InstanceType"]) ? $_POST["InstanceType"] : "",
            'instance_name' => isset($_POST["InstanceName"]) ? $_POST["InstanceName"] : "",
            'jmeter_version' => isset($_POST["JmeterVersion"]) ? $_POST["JmeterVersion"] : ""
        );
        $result = makePostRequest($url, $data, $headers);

        if (isset($result['master_instances']) && isset($result['slave_instances'])) {
            echo "CreatedSuccessfully";

            $conn = connectToDatabase();
            checkAndCreateTables($conn);
            insertIntoMasterSlaveTable($conn, $data, $result);
        } else {
            echo "CreatedUnsuccessfully";
            echo json_encode($result);
        }
        break;
    case 'delete':
        $url = $baseUrl . 'delete';
        $testIdToDelete = isset($_POST["testIdToDelete"]) ? $_POST["testIdToDelete"] : 0;

        $conn = connectToDatabase();
        $instance_ids = GetInstanceIds($conn, $testIdToDelete);
        $result = makePostRequest($url, $instance_ids, $headers);

        if (isset($result['message']) && str_contains($result['message'], "successfully")) {
            echo "DeletedSuccessfully";
            DeleteInstanceIds($conn, $instance_ids['instance_ids']);
        } else {
            echo "DeletedUnsuccessfully";
            echo json_encode($result);
        }
        break;
    case 'configureSlave':
        $url = $baseUrl . 'slave_configure';
        $testIdToConfigure = isset($_POST["id"]) ? $_POST["id"] : 0;

        $conn = connectToDatabase();
        $data = getMasterSlaveConfigJson($conn, $testIdToConfigure);
        echo json_encode($data);
        $result = makePostRequest($url, $data, $headers);

        if (isset($result['message']) && str_contains($result['message'], "successfully")) {
            echo "ConfiguredSuccessfully";
        } else {
            echo "ConfiguredUnsuccessfully";
        }
        break;
    case 'uploadData':
        $url = $baseUrl . 'upload_data';
        if (isset($_FILES['csvData'])) {
            $csvData = $_FILES['csvData'];
            $ipAddress = $_POST['ip_address'];

            // Handle the uploaded CSV file
            $uploadedFile = 'uploads/' . basename($csvData['name']);

            if (move_uploaded_file($csvData['tmp_name'], $uploadedFile)) {
                $postData = array(
                    'upload' => new CURLFile($csvData['tmp_name'], $csvData['type'], $csvData['name']),
                    'ip_address' => $ipAddress
                );
                var_dump($postData);

                $headers2 = array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => 'Authorization: Bearer ' . $jwtToken . "\r\n" .
                            'Content-type: multipart/form-data; boundary=' . uniqid(),
                        'content' => http_build_query($postData),
                    ),
                );

                $context = stream_context_create($headers2);
                $response = file_get_contents($url, false, $context);
                echo $response;

            } else {
                // Failed to upload file
                echo 'Error uploading file.';
            }
        } else {
            echo "UploadUnsuccessfully";
        }
        break;
    default:
        echo "Invalid operation specified.";
        exit;
}

?>