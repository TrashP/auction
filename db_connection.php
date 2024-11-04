<?php
// Connect to database to dynamically create categories
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Auction";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>