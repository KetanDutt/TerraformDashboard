<?php

function getMasterSlaveConfigJson($conn, $testIdToConfigure)
{
    $config = array();

    // Use the $testIdToConfigure to get the instance_name column from table performance_test using $conn database connection
    $query = "SELECT instance_name FROM performance_test WHERE id = " . $testIdToConfigure;
    $result = $conn->query($query);

    // Check if any rows are returned
    if ($result && $result->num_rows > 0) {
        // Fetch instance_name
        $row = $result->fetch_assoc();
        $instanceName = $row['instance_name'];

        // Use that instance_name to get all the public_ip from table master_instances
        $query = "SELECT public_ip FROM master_instances WHERE instance_name = '" . $instanceName . "'";
        $result = $conn->query($query);

        // Check if any rows are returned
        if ($result && $result->num_rows > 0) {
            // Fetch master IP
            $row = $result->fetch_assoc();
            $masterIP = $row['public_ip'];

            // Use that instance_name to get all the private_ip from table slave_instances
            $query = "SELECT private_ip FROM slave_instances WHERE instance_name = '" . $instanceName . "'";
            $result = $conn->query($query);

            // Fetch all slave IPs
            $slaveIPs = array();
            while ($row = $result->fetch_assoc()) {
                $slaveIPs[] = $row['private_ip'];
            }

            // Structure the data into an associative array
            $config['master_ip'] = $masterIP;
            $config['slave_ip'] = $slaveIPs;
        }
    }

    // Return JSON-encoded configuration
    return ($config);
}

?>