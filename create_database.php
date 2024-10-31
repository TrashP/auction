<?php
// Chat-GPT has been used to debug the code and suggest minor improvements for the code.

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$newDatabase = "Auction";

// Create connection to mysql servername
$mysqli = new mysqli($servername, $username, $password);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
echo "Connected successfully. <br>";

// Drop and create the database
$sql = "DROP DATABASE IF EXISTS $newDatabase;
        CREATE DATABASE $newDatabase;
        DEFAULT CHARACTER SET utf8
        DEFAULT COLLATE utf8_general_ci;
        GRANT SELECT, UPDATE, INSERT, DELETE
        ON $newDatabase.*
        TO 'root'@'localhost'
        IDENTIFIED BY '';
        USE $newDatabase;";

if ($mysqli->multi_query($sql)) {
    echo "Database '$newDatabase' created successfully.<br>";
    // Clear the result set from multi_query
    while ($mysqli->next_result()) {
        ;
    }
} else {
    die("Error creating database: " . $mysqli->error);
}

// Select the new database
$mysqli->select_db($newDatabase);

?>