<?php
session_start();
header("Content-type: text/javascript");

$username = $_SESSION["username"];

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