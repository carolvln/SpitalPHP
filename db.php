<?php
$servername = "localhost";
$username = "cvilnoiu_hospital";
$password = "hospital123";
$database = "cvilnoiu_hospital";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
