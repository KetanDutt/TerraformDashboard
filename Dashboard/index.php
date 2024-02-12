<?php
session_start();

// Include the database connection file
require_once('../Test/database.php');

// Check if the user is logged in
// if (!isset($_SESSION['username'])) {
//     error_reporting(E_ALL);
//     ini_set('display_errors', 1);
//     header("Location: ../"); // Redirect to login page if not logged in
//     exit();
// }

// Database connection
$conn = connectToDatabase();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tableName = 'performance_test';

// Check if the table exists
$sqlCheckTable = "SHOW TABLES LIKE '$tableName'";
$resultCheckTable = $conn->query($sqlCheckTable);

if ($resultCheckTable->num_rows > 0) {
    // Fetch current running clusters from the database
    $sql = "SELECT * FROM $tableName";
    $result = $conn->query($sql);
}

// Close the database connection
closeConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Dashboard</title>

    <!-- Materialize -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

    <!-- Sweetalert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@5/dark.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="./style.css" rel="stylesheet">

    <script src="./script.js"></script>
</head>

<body onload="loadComplete()">
    <div class="row" style="
    float: left;
">
        <h6>Logged is as
            <?php echo $_SESSION['username']; ?>
        </h6>
        <!-- <button class="swal2-confirm swal2-styled" type="submit" name="action"
            style="margin:0px;border-radius: 1vh;display: flex;flex-direction: row;flex-wrap: nowrap;align-content: center;justify-content: center;align-items: center;"
            onclick="logout()">logout
            <i class="material-icons right">logout</i>
        </button> -->
    </div>

    <h3 class="center" style="
    margin: 0px;
    padding: 30px;
">Performance Dashboard</h3>
    <h5>Running Tests :-</h5>
    <div>
        <table class="striped center" style="border: 2px solid rgba(255,255,255,0.12);">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Instance Name</th>
                    <th>Instance Type</th>
                    <th>JMeter Version</th>
                    <th>Master Count</th>
                    <th>Slave Count</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php
                // Display data in the table
                if (isset($result) && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td id=\"id" . $row["id"] . "\">" . $row["id"] . "</td>";
                        echo "<td id=\"instance_name" . $row["id"] . "\">" . $row["instance_name"] . "</td>";
                        echo "<td id=\"InstanceType" . $row["id"] . "\">" . $row["InstanceType"] . "</td>";
                        echo "<td id=\"jmeter_version" . $row["id"] . "\">" . $row["jmeter_version"] . "</td>";
                        echo '<td class="tooltipped" data-position="bottom" data-tooltip="Master IP<br>' . $row["masterIP"] . '" id="master_no' . $row["id"] . '"> ' . $row["master_no"] . '<i class="close material-icons" style="font-size: 30px;vertical-align: middle;margin-left: 20px;">info</i></td>';
                        echo '<td class="tooltipped" data-position="bottom" data-tooltip="Slave IP<br>' . $row["slaveIP"] . '" id="slave_no' . $row["id"] . '"> ' . $row["slave_no"] . '<i class="close material-icons" style="font-size: 30px;vertical-align: middle;margin-left: 20px;">info</i></td>';
                        echo '
                        <td style="
                        display: flex;
                        flex-direction: row;
                        flex-wrap: nowrap;
                        align-content: space-around;
                        justify-content: space-around;
                        align-items: stretch;
                    ">
                    <button class="swal2-confirm swal2-styled" type="submit" name="action"
                        style="margin:0px;background-color:#7066e0;border-radius: 1vh;display: flex;flex-direction: row;flex-wrap: nowrap;align-content: center;justify-content: center;align-items: center;"
                        onclick="configureTest(' . $row["id"] . ')">
                        <i class="material-icons" style="
                        margin: 0px;
                    ">settings</i>
                    </button>
                            <button class="swal2-confirm swal2-styled" type="submit" name="action"
                                style="margin:0px;background-color:#e06666;border-radius: 1vh;display: flex;flex-direction: row;flex-wrap: nowrap;align-content: center;justify-content: center;align-items: center;"
                                onclick="deleteTest(' . $row["id"] . ')">
                                <i class="material-icons" style="
                                margin: 0px;
                            ">delete</i>
                            </button>
                            <button class="swal2-confirm swal2-styled" type="submit" name="action"
                                style="margin:0px;background-color:#66e088;border-radius: 1vh;display: flex;flex-direction: row;flex-wrap: nowrap;align-content: center;justify-content: center;align-items: center;"
                                onclick="editTest(' . $row["id"] . ')">
                                <i class="material-icons" style="
                                margin: 0px;
                            ">edit</i>
                            </button>
                        </td>
                        </tr>';
                    }
                } else {
                    echo "<tr><td colspan='8'>No running clusters found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <h5>Create New :-</h5>
    <div style="background-color:rgba(61, 61, 61, 0.5);border-radius: 1vh;padding:10px 30px;width:50%">
        <div class="row" style="display:none">
            <div class="input-field">
                <input type="text" id="jwtToken" name="jwtToken" required style="color: white;"
                    value="<?php echo $_SESSION['jwtToken']; ?>">
                <label for="jwtToken">jwt Token</label>
            </div>
        </div>
        <div class="row">
            <div class="input-field">
                <input type="text" id="InstanceName" name="InstanceName"
                    value="Test_<?php echo random_int(100, 100000) ?>" required style="color: white;">
                <label for="InstanceName">Instance Name</label>
            </div>
        </div>
        <div class="row">
            <div class="input-field">
                <select id="InstanceType" required>
                    <option value="m5.large">m5.large</option>
                    <option value="m5.xlarge">m5.xlarge</option>
                    <option value="m5.2xlarge">m5.2xlarge</option>
                    <option value="m5.4xlarge">m5.4xlarge</option>
                    <option value="m5.8xlarge">m5.8xlarge</option>
                </select>
                <label>Instance Type</label>
            </div>
        </div>
        <div class="row">
            <div class="input-field">
                <select id="JmeterVersion" required>
                    <option value="5.5">5.5</option>
                    <option value="5.6">5.6</option>
                    <option value="5.7">5.7</option>
                </select>
                <label>JMeter version</label>
            </div>
        </div>
        <div class="row">
            <div class="input-field">
                <select id="MasterNo" required>
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
                <select id="SlaveNo" required>
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
        </div>

        <div class="row">
            <button class="swal2-confirm swal2-styled" type="submit" name="action"
                style="margin:0px;border-radius: 1vh;display: flex;flex-direction: row;flex-wrap: nowrap;align-content: center;justify-content: center;align-items: center;"
                onclick="createNewTest()">Submit
                <i class="material-icons right">send</i>
            </button>
        </div>
    </div>
</body>

</html>