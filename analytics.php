<?php

require __DIR__ . '/vendor/autoload.php';

header("Content-type: application/json");
header("X-Powered-By: Riverside Rocks");
header("X-Server: kestral (v2.2)");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protections: 1; mode=block");
header("X-Frame-Options: SAMEORIGIN");

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV['MYSQL_SERVER'];
$username = $_ENV["MYSQL_USERNAME"];
$password = $_ENV["MYSQL_PASSWORD"];
$dbname = $_ENV["MYSQL_DATABASE"];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT country, count(*) as hits from analytics GROUP BY country ORDER BY hits DESC";
$stmt = $conn->prepare($sql); 
$stmt->execute();
$result = $stmt->get_result();
$countries = array();
$hits = array();
while ($row = $result->fetch_assoc()) {
    $country = $row["country"];
    if(! $country)
    {
        $country = "Unknown";
    }
    $hit = $row["hits"];
    array_push($countries, $country);
    array_push($hits, $hit);
}

echo json_encode(array(
    "notice" => "Please don't excessively request this data!",
    "data" =>
        array(
            "countries" => $countries,
            "hits" => $hits,
        )
));
