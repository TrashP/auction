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
        CREATE DATABASE $newDatabase
        DEFAULT CHARACTER SET utf8
        DEFAULT COLLATE utf8_general_ci;";

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

// Create the 'Items' table if it does not exist
$sql = "CREATE TABLE IF NOT EXISTS Items
        (
        itemID INT AUTO_INCREMENT PRIMARY KEY,
        itemName VARCHAR(255) NOT NULL,
        itemDescription VARCHAR(255),
        category ENUM('Art and Collectibles', 'Electronics and Gadgets', 'Fashion and Accessories', 'Home and Garden', 'Automotive and Vehicles', 'Sports and Outdoors', 'Real Estate and Property', 'Books, Movies and Music', 'Toys and Games', 'Business and Industrial Equipment', 'Health and Beauty', 'Hobbies and Crafts', 'Pet Supplies', 'Industrial and Scientific', 'Charity and Fundraising', 'Others') NOT NULL
        )";

if ($mysqli->query($sql) === TRUE) {
        echo "Table 'Items' created successfully.<br>";
} else {
        die("Error creating table: " . $mysqli->error);
}

// Create the 'Bids' table if it does not exist
$sql = "CREATE TABLE IF NOT EXISTS Bids
        (
        bidID INT PRIMARY KEY,
        userID INT NOT NULL,
        auctionID INT NOT NULL,
        bidAmountGBP INT NOT NULL
        )";

if ($mysqli->query($sql) === TRUE) {
        echo "Table 'Bids' created successfully.<br>";
} else {
        die("Error creating table: " . $mysqli->error);
}

// Create the 'Auctions' table if it does not exist
$sql = "CREATE TABLE IF NOT EXISTS Auctions
        (
        auctionID INT AUTO_INCREMENT PRIMARY KEY,
        userID INT NOT NULL,
        itemID INT NOT NULL,
        auctionDate DATE NOT NULL,
        startPriceGBP INT NOT NULL,
        reservePriceGBP INT NOT NULL,
        highestBidderID INT,
        quantity INT NOT NULL
        )";

if ($mysqli->query($sql) === TRUE) {
        echo "Table 'Auctions' created successfully.<br>";
} else {
        die("Error creating table: " . $mysqli->error);
}

// Create the 'Users' table if it does not exist
$sql = "CREATE TABLE IF NOT EXISTS Users
        (
        userID INT AUTO_INCREMENT PRIMARY KEY,
        firstName VARCHAR(255) NOT NULL,
        lastName VARCHAR(255) NOT NULL,
        dateOfBirth DATE NOT NULL,
        email VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('Buyer', 'Seller')
        )";

if ($mysqli->query($sql) === TRUE) {
        echo "Table 'Users' created successfully.<br>";
} else {
        die("Error creating table: " . $mysqli->error);
}

// Add sample data to database
$sql = "INSERT INTO Items (itemID, itemName, itemDescription, category)
        VALUES (1, 'Watch', 'Rolex platinum watch made in Switzerland', 'Fashion and Accessories');";
$mysqli->query($sql);

$sql = "INSERT INTO Items (itemID, itemName, itemDescription, category)
        VALUES (2, 'Guitar', 'Gibson Les Paul guitar made in 1965 and signed by Jimmy Page. Great for playing the Blues or Rock n Roll.', 'Books, Movies and Music');";
$mysqli->query($sql);

$sql = "INSERT INTO Items (itemID, itemName, itemDescription, category)
        VALUES (3, 'Pokemon Card', 'Dragonite cards from 2001 in near-mint condition.', 'Art and Collectibles');";
$mysqli->query($sql);

$sql = "INSERT INTO Bids (bidID, userID, auctionID, bidAmountGBP)
        VALUES (1, 4, 1, 50000);";
$mysqli->query($sql);

$sql = "INSERT INTO Bids (bidID, userID, auctionID, bidAmountGBP)
        VALUES (2, 4, 2, 8473892);";
$mysqli->query($sql);

$sql = "INSERT INTO Bids (bidID, userID, auctionID, bidAmountGBP)
        VALUES (3, 4, 3, 398349);";
$mysqli->query($sql);



$sql = "INSERT INTO Bids (bidID, userID, auctionID, bidAmountGBP)
        VALUES (4, 5, 2, 2000);";
$mysqli->query($sql);

$sql = "INSERT INTO Auctions (auctionID, userID, itemID, auctionDate, startPriceGBP, reservePriceGBP, highestBidderID, quantity)
        VALUES (1, 1, 1, '2024-10-30', 40000, 80000, 4, 1);";
$mysqli->query($sql);

$sql = "INSERT INTO Auctions (auctionID, userID, itemID, auctionDate, startPriceGBP, reservePriceGBP, highestBidderID, quantity)
        VALUES (2, 2, 2, '2024-11-01', 1000, 10000, 5, 1);";
$mysqli->query($sql);

$sql = "INSERT INTO Auctions (auctionID, userID, itemID, auctionDate, startPriceGBP, reservePriceGBP, quantity)
        VALUES (3, 3, 3, '2024-11-02', 500, 2000, 2);";
$mysqli->query($sql);

$sql = "INSERT INTO Users (userID, firstName, lastName, dateOfBirth, email, password, role)
        VALUES (1, 'Leo', 'Messi', '1990-07-23', 'leomessi@gmail.com', 'lmessi', 'Seller');";
$mysqli->query($sql);

$sql = "INSERT INTO Users (userID, firstName, lastName, dateOfBirth, email, password, role)
        VALUES (2, 'Taylor', 'Swift', '1991-01-25', 'taylorswift@gmail.com', 'tswift', 'Seller');";
$mysqli->query($sql);

$sql = "INSERT INTO Users (userID, firstName, lastName, dateOfBirth, email, password, role)
        VALUES (3, 'Tom', 'Cruise', '1980-03-03', 'tomcruise@gmail.com', 'tcruise', 'Seller');";
$mysqli->query($sql);

$sql = "INSERT INTO Users (userID, firstName, lastName, dateOfBirth, email, password, role)
        VALUES (4, 'Chris', 'Martin', '1985-07-11', 'chrismartin@gmail.com', 'cmartin', 'Buyer');";
$mysqli->query($sql);

$sql = "INSERT INTO Users (userID, firstName, lastName, dateOfBirth, email, password, role)
        VALUES (5, 'Chris', 'Nolan', '1982-03-01', 'chrisnolan@gmail.com', 'cnolan', 'Buyer');";
$mysqli->query($sql);

$mysqli->close();
?>