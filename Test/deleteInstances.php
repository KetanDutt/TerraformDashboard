<?php


function GetInstanceIds($conn, $instanceID)
{
  try {
    // Step 1: Get instance_name from performance_test table
    $sql = "SELECT instance_name FROM performance_test WHERE id='$instanceID'";
    $result = $conn->query($sql);

    if ($result === FALSE) {
      throw new Exception("Error getting InstanceIDs from performance record: " . $conn->error);
    }

    // Fetch the result
    $row = $result->fetch_assoc();
    $instanceName = $row['instance_name'];

    // Step 2: Get matching instance_ids from master_instances and slave_instances tables
    $sqlMaster = "SELECT instance_id FROM master_instances WHERE instance_name='$instanceName'";
    $resultMaster = $conn->query($sqlMaster);

    $sqlSlave = "SELECT instance_id FROM slave_instances WHERE instance_name='$instanceName'";
    $resultSlave = $conn->query($sqlSlave);

    if ($resultMaster === FALSE || $resultSlave === FALSE) {
      throw new Exception("Error getting instance_ids from master or slave instances: " . $conn->error);
    }

    // Fetch all the results
    $instanceIds = [];
    while ($rowMaster = $resultMaster->fetch_assoc()) {
      $instanceIds[] = $rowMaster['instance_id'];
    }

    while ($rowSlave = $resultSlave->fetch_assoc()) {
      $instanceIds[] = $rowSlave['instance_id'];
    }

    // You can return these values as needed
    return [
      'instance_ids' => $instanceIds,
    ];

  } catch (Exception $e) {
    logMessage("Error: " . $e->getMessage());
    return null; // or handle the error in a way that suits your application
  }
}

function DeleteInstanceIds($conn, $instanceIds)
{
  try {
    // Step 1: Get instance_name from master_instances table
    $sql = "SELECT instance_name FROM master_instances WHERE instance_id='$instanceIds[0]'";
    $result = $conn->query($sql);

    if ($result === FALSE) {
      throw new Exception("Error getting InstanceIDs from performance record: " . $conn->error);
    }

    // Fetch the result
    $row = $result->fetch_assoc();
    $instanceName = $row['instance_name'];

    // Step 2: Delete matching records from performance_test table
    $sqlPerformance = "DELETE FROM performance_test WHERE instance_name='$instanceName'";
    $resultPerformance = $conn->query($sqlPerformance);

    if ($resultPerformance === FALSE) {
      throw new Exception("Error deleting records from performance_test: " . $conn->error);
    }

    // Step 3: Delete matching records from master_instances table
    foreach ($instanceIds as $instanceId) {
      $sqlMaster = "DELETE FROM master_instances WHERE instance_id='$instanceId'";
      $resultMaster = $conn->query($sqlMaster);

      if ($resultMaster === FALSE) {
        throw new Exception("Error deleting records from master_instances: " . $conn->error);
      }
    }

    // Step 4: Delete matching records from slave_instances table
    foreach ($instanceIds as $instanceId) {
      $sqlSlave = "DELETE FROM slave_instances WHERE instance_id='$instanceId'";
      $resultSlave = $conn->query($sqlSlave);

      if ($resultSlave === FALSE) {
        throw new Exception("Error deleting records from slave_instances: " . $conn->error);
      }
    }

    // Optionally, you can also log a success message or return a success flag
    logMessage("Successfully deleted records for instance_ids: " . implode(", ", $instanceIds));
    return true;

  } catch (Exception $e) {
    logMessage("Error: " . $e->getMessage());
    return false; // or handle the error in a way that suits your application
  }
}

?>