<?php
session_start();
header("Content-type: text/javascript");

$username = $_SESSION["username"];

$servername = $_ENV['MYSQL_SERVER'];
$username = $_ENV["MYSQL_USERNAME"];
$password = $_ENV["MYSQL_PASSWORD"];
$dbname = $_ENV["MYSQL_DATABASE"];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM logins WHERE username='$username'";
$result = $conn->query($sql);
if (!empty($result) && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $key = $row["temp_auto_api_key"];
        break;
    }
}

echo "const user = '" . $_SESSION["username"] . "';\n";
echo "const key = " . $key . ";\n";