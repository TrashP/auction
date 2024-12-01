<?php include_once("db_connection.php"); ?>
<?php require 'email_functions.php'; ?>


<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    date_default_timezone_set('Europe/London');
    echo "start";

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

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $mysqli->error);
}


echo "start";

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
        $messageOwner = "
        <p>Dear $ownerName,</p>
        <p>Your auction has ended:</p>
        <ul>
            <li><strong>Item:</strong> $itemName</li>
        </ul>
        <p>Please review the results and contact the highest bidder if applicable.</p>
        <p>Best regards,<br>The Auction Team</p>";
        echo "sent email to owner ";


        sendEmail($ownerName, $ownerEmail, $subjectOwner, $messageOwner);

        // Send email to the highest bidder (if there is a winner)
        if (!empty($bidderEmail)) {
            $subjectBidder = "Congratulations on Winning Auction #$auctionID";
            $messageBidder = "
                <p>Dear $bidderName,</p>
                <p>Congratulations! You have won the auction:</p>
                <ul>
                    <li><strong>Item:</strong> $itemName</li>
                </ul>
                <p>Please contact the auction owner for further details.</p>
                <p>Best regards,<br>The Auction Team</p>";
            sendEmail($bidderName, $bidderEmail, $subjectBidder, $messageBidder);
        }

        // Notify users watching the auction
        $watchlistQuery = "
            SELECT u.email, u.firstName
            FROM Watchlist w
            JOIN Users u ON w.userID = u.userID
            WHERE w.auctionID = ? AND w.watching = TRUE
        ";
        $watchlistStmt = $conn->prepare($watchlistQuery);
        $watchlistStmt->bind_param("i", $auctionID);
        $watchlistStmt->execute();
        $watchlistResult = $watchlistStmt->get_result();

        while ($watcher = $watchlistResult->fetch_assoc()) {
            $watcherEmail = $watcher['email'];
            $watcherName = $watcher['firstName'];

            $subjectWatcher = "Auction #$auctionID Has Ended";
            $messageWatcher = "Dear $watcherName,\n\nThe auction (ID: $auctionID, Item: $itemName) you were watching has ended. Thank you for your interest in this auction.";
            sendEmail($watcherName, $watcherEmail, $subjectWatcher, $messageWatcher);
        }

        // Update Watchlist to set 'watching' to 0 for this auction
        $updateWatchlistQuery = "UPDATE Watchlist SET watching = FALSE WHERE auctionID = ?";
        $updateWatchlistStmt = $conn->prepare($updateWatchlistQuery);
        $updateWatchlistStmt->bind_param("i", $auctionID);
        $updateWatchlistStmt->execute();
        $updateWatchlistStmt->close();

        // Mark the auction as processed
        $updateSql = "UPDATE Auctions SET processed = TRUE WHERE auctionID = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("i", $auctionID);
        $stmt->execute();
        $stmt->close();

    }
} else {
    echo "No expired auctions to process.";
}

$mysqli->close();
?>
