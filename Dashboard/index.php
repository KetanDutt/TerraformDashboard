<?php
session_start();

// Include the database connection file
require_once('../database.php');
require_once('./dashboard.php');

// Check if the user is logged in
if(!isset($_SESSION['username'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    header("Location: ../"); // Redirect to login page if not logged in
    exit();
}

// Database connection
$conn = connectToDatabase();

// Check connection
if($conn->connect_error) {
    die("Connection failed: ".$conn->connect_error);
}

$tableName = 'performance_test';
// if(isset($_SESSION['test'])) {
//     $tableName = $tableName."test";
// }

if(!doesTableExist($tableName, $conn)) {
    // If not, create the table
    createPerformanceTestTable($conn, $tableName);
}
// Fetch current running clusters from the database
$sql = "SELECT * FROM $tableName";
$result = $conn->query($sql);


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
    <div class="row">
        <h6>Logged is as
            <?php echo $_SESSION['username']; ?>
        </h6>
        <button class="swal2-confirm swal2-styled" type="submit" name="action"
            style="margin:0px;border-radius: 1vh;display: flex;flex-direction: row;flex-wrap: nowrap;align-content: center;justify-content: center;align-items: center;"
            onclick="logout()">logout
            <i class="material-icons right">logout</i>
        </button>
    </div>

    <h3 class="center">Performance Dashboard</h3>
    <br>
    <br>
    <h5>Running Tests :-</h5>
    <div>
        <table class="striped center" style="border: 2px solid rgba(255,255,255,0.12);">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>JMeter Version</th>
                    <th>Master Count</th>
                    <th>Master Config</th>
                    <th>Slave Count</th>
                    <th>Slave Config</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php
                // Display data in the table
                if($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td id=\"id".$row["id"]."\">".$row["id"]."</td>";
                        echo "<td id=\"testName".$row["id"]."\">".$row["testName"]."</td>";
                        echo "<td id=\"jmeterVersion".$row["id"]."\">".$row["jmeterVersion"]."</td>";
                        echo "<td id=\"MasterCount".$row["id"]."\">".$row["MasterCount"]."</td>";
                        echo "<td id=\"MasterConfig".$row["id"]."\">".str_replace(";", "<br>", $row["MasterConfig"])."</td>";
                        echo "<td id=\"SlaveCount".$row["id"]."\">".$row["SlaveCount"]."</td>";
                        echo "<td id=\"SlaveConfig".$row["id"]."\">".str_replace(";", "<br>", $row["SlaveConfig"])."</td>";
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
                                style="margin:0px;background-color:#e06666;border-radius: 1vh;display: flex;flex-direction: row;flex-wrap: nowrap;align-content: center;justify-content: center;align-items: center;"
                                onclick="deleteTest('.$row["id"].')">
                                <i class="material-icons" style="
                                margin: 0px;
                            ">delete</i>
                            </button>
                            <button class="swal2-confirm swal2-styled" type="submit" name="action"
                                style="margin:0px;background-color:#66e088;border-radius: 1vh;display: flex;flex-direction: row;flex-wrap: nowrap;align-content: center;justify-content: center;align-items: center;"
                                onclick="editTest('.$row["id"].')">
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
    <br>
    <br>
    <h5>Create New :-</h5>
    <div style="background-color:rgba(61, 61, 61, 0.5);border-radius: 1vh;padding:10px 30px;width:50%">
        <div class="row">
            <div class="input-field">
                <input type="text" id="testName" name="testName" required style="color: white;">
                <label for="testName">Test Name</label>
            </div>
        </div>
        <div class="row">
            <div class="input-field">
                <select id="jmeterVersion" required>
                    <option value="1.4.5">1.4.5</option>
                    <option value="1.5.76">1.5.76</option>
                    <option value="2.3.5">2.3.5</option>
                </select>
                <label>JMeter version</label>
            </div>
        </div>
        <div class="row">
            <div class="input-field">
                <select id="MasterCount" required>
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
                <select id="SlaveCount" required>
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