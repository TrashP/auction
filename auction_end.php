<?php include_once("db_connection.php"); ?>
<?php require 'email_functions.php'; ?>


<?php


//alter auctions table to include a processed coloumn

// Query to fetch expired auctions where emails haven't been sent
$sql = "SELECT a.auctionID, a.userID AS ownerID, a.highestBidderID, 
               a.itemID, i.itemName, u1.email AS ownerEmail, u1.firstName AS ownerName, 
               u2.email AS bidderEmail, u2.firstName AS bidderName
        FROM Auctions a
        JOIN Items i ON a.itemID = i.itemID
        LEFT JOIN Users u1 ON a.userID = u1.userID
        LEFT JOIN Users u2 ON a.highestBidderID = u2.userID
        WHERE a.auctionDate <= NOW() AND a.processed = FALSE";

$result = $mysqli->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $auctionID = $row['auctionID'];
        $ownerName = $row['ownerName'];
        $ownerEmail = $row['ownerEmail'];
        $bidderName = $row['bidderName'];
        $bidderEmail = $row['bidderEmail'];
        $itemName = $row['itemName'];

        // Send email to the auction owner
        $subjectOwner = "Auction #$auctionID for '$itemName' Has Ended";
        $messageOwner = "Dear $ownerName,\n\nYour auction (ID: $auctionID, Item: $itemName) has ended. 
                         Please review the results and contact the highest bidder if applicable.";
        sendEmail($ownerName, $ownerEmail, $subjectOwner, $messageOwner);

        // Send email to the highest bidder (if there is a winner)
        if (!empty($bidderEmail)) {
            $subjectBidder = "Congratulations on Winning Auction #$auctionID";
            $messageBidder = "Dear $bidderName,\n\nCongratulations! You have won the auction 
                              (ID: $auctionID, Item: $itemName). Please contact the auction owner for further details.";
            sendEmail($bidderName, $bidderEmail, $subjectBidder, $messageBidder);
        }

        // Mark the auction as processed
        $updateSql = "UPDATE Auctions SET processed = TRUE WHERE auctionID = ?";
        $stmt = $mysqli->prepare($updateSql);
        $stmt->bind_param("i", $auctionID);
        $stmt->execute();
        $stmt->close();
    }
} else {
    echo "No expired auctions to process.";
}

$mysqli->close();
?>
