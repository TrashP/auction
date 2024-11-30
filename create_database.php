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
        category ENUM('Art and Collectibles', 'Electronics and Gadgets', 'Fashion and Accessories', 'Home and Garden', 'Automotive and Vehicles', 'Sports and Outdoors', 'Real Estate and Property', 'Books, Movies and Music', 'Toys and Games', 'Business and Industrial Equipment', 'Health and Beauty', 'Hobbies and Crafts', 'Pet Supplies', 'Industrial and Scientific', 'Charity and Fundraising', 'Others') NOT NULL,
        itemPhotoPath VARCHAR(255) NOT NULL
        )";

if ($mysqli->query($sql) === TRUE) {
        echo "Table 'Items' created successfully.<br>";
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

// Create the 'Auctions' table if it does not exist
$sql = "CREATE TABLE IF NOT EXISTS Auctions
        (
        auctionID INT AUTO_INCREMENT PRIMARY KEY,
        userID INT NOT NULL,
        itemID INT NOT NULL,
        auctionDate DATETIME NOT NULL,
        startPriceGBP INT NOT NULL,
        reservePriceGBP INT NOT NULL,
        highestBidderID INT,
        quantity INT NOT NULL,
        processed BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (userID) REFERENCES Users(userID),
        FOREIGN KEY (itemID) REFERENCES Items(itemID)
        )";

if ($mysqli->query($sql) === TRUE) {
        echo "Table 'Auctions' created successfully.<br>";
} else {
        die("Error creating table: " . $mysqli->error);
}

// Create the 'Bids' table if it does not exist
$sql = "CREATE TABLE IF NOT EXISTS Bids
        (
        bidID INT AUTO_INCREMENT PRIMARY KEY,
        userID INT NOT NULL,
        auctionID INT NOT NULL,
        bidAmountGBP INT NOT NULL,
        FOREIGN KEY (userID) REFERENCES Users(userID),
        FOREIGN KEY (auctionID) REFERENCES Auctions(auctionID)
        )";

if ($mysqli->query($sql) === TRUE) {
        echo "Table 'Bids' created successfully.<br>";
} else {
        die("Error creating table: " . $mysqli->error);
}

$sql = "CREATE TABLE IF NOT EXISTS Ratings
        (
        userID INT NOT NULL,
        auctionID INT NOT NULL,
        rating ENUM('1', '2', '3', '4', '5') NOT NULL,
        comment VARCHAR(255),
        date DATE DEFAULT CURRENT_DATE,
        PRIMARY KEY (userID, auctionID),
        FOREIGN KEY (userID) REFERENCES Users(userID),
        FOREIGN KEY (auctionID) REFERENCES Auctions(auctionID)
        )";

if ($mysqli->query($sql) === TRUE) {
        echo "Table 'Ratings' created successfully.<br>";
} else {
        die("Error creating table: " . $mysqli->error);
}

$sql = "CREATE TABLE IF NOT EXISTS Watchlist
        (
        userID INT NOT NULL,
        auctionID INT NOT NULL,
        watching BOOLEAN NOT NULL,
        PRIMARY KEY (userID, auctionID),
        FOREIGN KEY (userID) REFERENCES Users(userID),
        FOREIGN KEY (auctionID) REFERENCES Auctions(auctionID)
        )";

if ($mysqli->query($sql) === TRUE) {
        echo "Table 'Watchlist' created successfully.<br>";
} else {
        die("Error creating table: " . $mysqli->error);
}

$sql = "CREATE TABLE IF NOT EXISTS Points
        (
        userID INT NOT NULL,
        auctionID INT NOT NULL,
        points INT NOT NULL,
        PRIMARY KEY (userID, auctionID),
        FOREIGN KEY (userID) REFERENCES Users(userID),
        FOREIGN KEY (auctionID) REFERENCES Auctions(auctionID)
        )";

if ($mysqli->query($sql) === TRUE) {
        echo "Table 'Points' created successfully.<br>";
} else {
        die("Error creating table: " . $mysqli->error);
}

$sql = "CREATE TABLE IF NOT EXISTS ProxyBids
        (
        userID INT NOT NULL,
        auctionID INT NOT NULL,
        maxBidGBP INT NOT NULL,
        PRIMARY KEY (userID, auctionID),
        FOREIGN KEY (userID) REFERENCES Users(userID),
        FOREIGN KEY (auctionID) REFERENCES Auctions(auctionID)
        )";

if ($mysqli->query($sql) === TRUE) {
        echo "Table 'ProxyBids' created successfully.<br>";
} else {
        die("Error creating table: " . $mysqli->error);
}



// CHATROOM SQL




//Message SQL
$sql = "CREATE TABLE IF NOT EXISTS Messages (
        message_id INT AUTO_INCREMENT PRIMARY KEY,
        auction_id INT NOT NULL,
        buyer_id INT NOT NULL,
        seller_id INT NOT NULL,
        buyer_message TEXT NOT NULL,
        seller_message TEXT DEFAULT NULL,
        sent_date_buyer DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        sent_date_seller DATETIME DEFAULT NULL,
        FOREIGN KEY (auction_id) REFERENCES Auctions(auctionID) ON DELETE CASCADE,
        FOREIGN KEY (buyer_id) REFERENCES Users(userID) ON DELETE CASCADE,
        FOREIGN KEY (seller_id) REFERENCES Users(userID) ON DELETE CASCADE)";
    
    

if ($mysqli->query($sql) === TRUE) {
        echo "Table 'Messages' created successfully.<br>";
} else {
        die("Error creating table: " . $mysqli->error);
}




$hashedPassword = password_hash('lmessi', PASSWORD_DEFAULT);
$sql = "INSERT INTO Users (userID, firstName, lastName, dateOfBirth, email, password, role)
        VALUES (1, 'Leo', 'Messi', '1990-07-23', 'leomessi@gmail.com', '$hashedPassword', 'Seller');";
$mysqli->query($sql);

$sql = "INSERT INTO Users (userID, firstName, lastName, dateOfBirth, email, password, role)
        VALUES (2, 'Taylor', 'Swift', '1991-01-25', 'taylorswift@gmail.com', 'tswift', 'Seller');";
$mysqli->query($sql);

$hashedPassword = password_hash('tcruise', PASSWORD_DEFAULT);
$sql = "INSERT INTO Users (userID, firstName, lastName, dateOfBirth, email, password, role)
        VALUES (3, 'Tom', 'Cruise', '1980-03-03', 'tomcruise@gmail.com', '$hashedPassword', 'Seller');";
$mysqli->query($sql);

$hashedPassword = password_hash('cmartin', PASSWORD_DEFAULT);
$sql = "INSERT INTO Users (userID, firstName, lastName, dateOfBirth, email, password, role)
        VALUES (4, 'Chris', 'Martin', '1985-07-11', 'chrismartin@gmail.com', '$hashedPassword', 'Buyer');";
$mysqli->query($sql);

$sql = "INSERT INTO Users (userID, firstName, lastName, dateOfBirth, email, password, role)
        VALUES (5, 'Chris', 'Nolan', '1982-03-01', 'chrisnolan@gmail.com', 'cnolan', 'Buyer');";
$mysqli->query($sql);

$sql = "INSERT INTO Users (userID, firstName, lastName, dateOfBirth, email, password, role)
        VALUES 
        (6, 'Emma', 'Watson', '1992-04-15', 'emmawatson@gmail.com', 'ewatson', 'Buyer'),
        (7, 'Robert', 'Downey', '1970-04-04', 'rdowney@gmail.com', 'rdowney', 'Seller');";
$mysqli->query($sql);

// Add sample data to database
$sql = "INSERT INTO Items (itemID, itemName, itemDescription, category, itemPhotoPath)
        VALUES (1, 'Watch', 'Rolex platinum watch made in Switzerland', 'Fashion and Accessories', 'photos/rolex_submariner.jpg');";
$mysqli->query($sql);

$sql = "INSERT INTO Items (itemID, itemName, itemDescription, category, itemPhotoPath)
        VALUES (2, 'Guitar', 'Gibson Les Paul guitar made in 1965 and signed by Jimmy Page. Great for playing the Blues or Rock n Roll.', 'Books, Movies and Music', 'photos/gibson_les_paul_guitar.jpg');";
$mysqli->query($sql);

$sql = "INSERT INTO Items (itemID, itemName, itemDescription, category, itemPhotoPath)
        VALUES (3, 'Pokemon Card', 'Dragonite cards from 2001 in near-mint condition.', 'Art and Collectibles', 'photos/charizard_card.jpg');";
$mysqli->query($sql);

$sql = "INSERT INTO Items (itemID, itemName, itemDescription, category, itemPhotoPath)
        VALUES (4, 'Chain', 'Gold Chain for men', 'Fashion and Accessories', 'photos/gold_chain.jpg');";
$mysqli->query($sql);

$sql = "INSERT INTO Items (itemID, itemName, itemDescription, category, itemPhotoPath)
        VALUES 
        (5, 'Smartphone', 'Latest Apple iPhone 15 Pro, 256GB, Graphite', 'Electronics and Gadgets', 'photos/iphone_15_pro.jpg'),
        (6, 'Mountain Bike', 'Trek X-Caliber 9 with 29-inch wheels, ideal for trails', 'Sports and Outdoors', 'photos/trek_bike.jpg'),
        (7, 'Painting', 'Original oil painting of a serene landscape by a renowned artist', 'Art and Collectibles', 'photos/oil_painting.jpg'),
        (8, 'Vintage Car', '1965 Ford Mustang, fully restored to showroom condition', 'Automotive and Vehicles', 'photos/ford_mustang.jpg'),
        (9, 'Dining Table Set', 'Solid oak dining table with 6 matching chairs', 'Home and Garden', 'photos/dining_table.jpg'),
        (10, 'Fitness Tracker', 'Fitbit Versa 4, with advanced health monitoring features', 'Health and Beauty', 'photos/fitbit_versa.jpg');";
$mysqli->query($sql);

$sql = "INSERT INTO Items (itemName, itemDescription, category, itemPhotoPath)
        VALUES 
        ('Laptop', 'Dell XPS 15 with 16GB RAM and 512GB SSD', 'Electronics and Gadgets', 'photos/dell_xps_15.jpg'),
        ('Electric Scooter', 'Xiaomi Mi Electric Scooter Pro 2', 'Sports and Outdoors', 'photos/xiaomi_scooter.jpg'),
        ('Wristwatch', 'Seiko Presage Cocktail Time automatic watch', 'Fashion and Accessories', 'photos/seiko_presage.jpg'),
        ('Book Collection', 'Complete works of J.R.R. Tolkien', 'Books, Movies and Music', 'photos/tolkien_books.jpg'),
        ('Gaming Console', 'Sony PlayStation 5 with two controllers', 'Electronics and Gadgets', 'photos/ps5_console.jpg');";
$mysqli->query($sql);

$sql = "INSERT INTO Auctions (auctionID, userID, itemID, auctionDate, startPriceGBP, reservePriceGBP, highestBidderID, quantity)
        VALUES (1, 1, 1, '2024-10-30 14:30:00', 40000, 80000, 4, 1);";
$mysqli->query($sql);

$sql = "INSERT INTO Auctions (auctionID, userID, itemID, auctionDate, startPriceGBP, reservePriceGBP, highestBidderID, quantity)
        VALUES (2, 2, 2, '2024-11-01 09:15:00', 1000, 10000, 5, 1);";
$mysqli->query($sql);

$sql = "INSERT INTO Auctions (auctionID, userID, itemID, auctionDate, startPriceGBP, reservePriceGBP, quantity)
        VALUES (3, 3, 3, '2024-11-02 18:45:00', 500, 2000, 2);";
$mysqli->query($sql);

$sql = "INSERT INTO Auctions (auctionID, userID, itemID, auctionDate, startPriceGBP, reservePriceGBP, quantity)
        VALUES (4, 3, 4, '2024-07-02 18:45:01', 2000, 10000, 1);";
$mysqli->query($sql);

$sql = "INSERT INTO Auctions (auctionID, userID, itemID, auctionDate, startPriceGBP, reservePriceGBP, quantity)
        VALUES 
        (5, 2, 5, '2024-12-01 10:00:00', 900, 1200, 5),
        (6, 3, 6, '2024-12-02 15:30:00', 1200, 2000, 2),
        (7, 1, 7, '2024-12-03 17:00:00', 5000, 8000, 1),
        (8, 1, 8, '2024-12-04 11:45:00', 15000, 25000, 1),
        (9, 2, 9, '2024-12-05 09:20:00', 300, 800, 3),
        (10, 3, 10, '2024-12-06 14:00:00', 100, 300, 10);";
$mysqli->query($sql);

$sql = "INSERT INTO Auctions (auctionID, userID, itemID, auctionDate, startPriceGBP, reservePriceGBP, quantity)
        VALUES 
        (11, 1, 11, '2024-12-07 12:00:00', 1200, 1500, 1),
        (12, 2, 12, '2024-12-08 14:30:00', 400, 700, 3),
        (13, 3, 13, '2024-12-09 16:00:00', 250, 400, 2),
        (14, 1, 14, '2024-12-10 10:00:00', 100, 200, 1),
        (15, 2, 15, '2024-12-11 18:00:00', 500, 800, 2);";
$mysqli->query($sql);

$sql = "INSERT INTO Bids (bidID, userID, auctionID, bidAmountGBP)
        VALUES (1, 4, 1, 50000);";
$mysqli->query($sql);

$sql = "INSERT INTO Bids (bidID, userID, auctionID, bidAmountGBP)
        VALUES (3, 4, 3, 398349);";
$mysqli->query($sql);

$sql = "INSERT INTO Bids (bidID, userID, auctionID, bidAmountGBP)
        VALUES (4, 5, 2, 3000);";
$mysqli->query($sql);

$sql = "INSERT INTO Bids (bidID, userID, auctionID, bidAmountGBP)
        VALUES (5, 5, 1, 60000);";
$mysqli->query($sql);

$sql = "INSERT INTO Bids (bidID, userID, auctionID, bidAmountGBP)
        VALUES 
        (6, 4, 5, 950),
        (7, 5, 5, 1000),
        (8, 4, 6, 1500),
        (9, 5, 7, 6000),
        (10, 4, 8, 16000),
        (11, 5, 10, 200);";
$mysqli->query($sql);

$sql = "INSERT INTO Watchlist (userID, auctionID, watching)
        VALUES (4, 4, TRUE);";
$mysqli->query($sql);

$sql = "INSERT INTO Ratings (userID, auctionID, rating, comment) 
        VALUES 
        (4, 1, '5', 'Excellent auction, smooth process.'),
        (5, 2, '4', 'Good experience, item as described.'),
        (6, 3, '3', 'Item was okay, but packaging could be better.'),
        (4, 5, '5', 'Great communication, highly recommended!'),
        (5, 6, '2', 'Item arrived late and not as described.'),
        (4, 2, '5', 'The guitar is amazing, just as described!');";

$mysqli->query($sql);

$mysqli->close();
?>