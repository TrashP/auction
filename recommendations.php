<?php include_once("header.php") ?>
<?php require("utilities.php") ?>

<div class="container">

  <h2 class="my-3">Recommendations for you</h2>

  <?php
  // This page is for showing a buyer recommended items based on their bid 
  // history. It will be pretty similar to browse.php, except there is no 
  // search bar. This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.
  

  // TODO: Check user's credentials (cookie/session).
  
  // TODO: Perform a query to pull up auctions they might be interested in.
  
  // TODO: Loop through results and print them out as list items.
  require 'db_connection.php';

  // Implement recommendation system based on User collaborative filtering
  if (isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID'];
    echo "<h5>Items that people similar to you are bidding on:</h5>";

    // $sql = "SELECT DISTINCT
    //     Bids.userID,
    //     Items.itemID, 
    //     itemName, 
    //     itemDescription, 
    //     GREATEST(startPriceGBP, IFNULL(MAX(bidAmountGBP), 0)) AS currentPrice, 
    //     COUNT(Bids.userID) AS numBids,
    //     a1.auctionID,
    //     auctionDate
    //     FROM Auctions a1
    //     INNER JOIN Items USING (itemID)
    //     INNER JOIN Bids ON a1.auctionID = Bids.auctionID
    //     WHERE Bids.userID IN 
    //         (SELECT b1.userID
    //         FROM Bids b1
    //         INNER JOIN Bids b2 
    //             ON b1.auctionID = b2.auctionID 
    //             AND b1.userID != b2.userID 
    //             AND b2.userID = $userID
    //         WHERE b1.userID != $userID 
    //             AND NOT EXISTS (
    //                 SELECT 1
    //                 FROM Bids b2
    //                 WHERE b2.userID = $userID
    //                 AND b2.auctionID = a1.auctionID
    //               )
    //       ) 
    //     GROUP BY Items.itemID, itemName, itemDescription, startPriceGBP, auctionDate;";
  
    $sql = "SELECT DISTINCT
                Items.itemID, 
                itemName, 
                itemDescription, 
                GREATEST(a1.startPriceGBP, IFNULL(MAX(Bids.bidAmountGBP), 0)) AS currentPrice, 
                COUNT(Bids.userID) AS numBids,
                a1.auctionID,
                a1.auctionDate
            FROM Auctions a1
            INNER JOIN Items USING (itemID)
            INNER JOIN Bids ON a1.auctionID = Bids.auctionID
            WHERE Bids.userID IN (
                SELECT DISTINCT b1.userID
                FROM Bids b1
                INNER JOIN Bids b2 
                    ON b1.auctionID = b2.auctionID 
                    AND b1.userID != b2.userID 
                WHERE b2.userID = $userID
            )
            AND NOT EXISTS (
                SELECT 1
                FROM Bids b3
                WHERE b3.userID = $userID
                AND b3.auctionID = a1.auctionID
            )
            GROUP BY Items.itemID, itemName, itemDescription, a1.startPriceGBP, a1.auctionID, a1.auctionDate;";

    $resultrec = $conn->query($sql);

    if ($resultrec === false) {
      // Output error message
      echo "Error in query: " . $conn->error;
    } else {
      // Output data for each row
      while ($row = $resultrec->fetch_assoc()) {
        print_listing_li($row['itemID'], $row['itemName'], $row['itemDescription'], $row['currentPrice'], $row['numBids'], $row['auctionDate'], $row['auctionID']);
      }
    }

    // Implement recommendation system based on Item collaborative filtering
    echo "<br><br><h5>Items like the ones you bid on:</h5>";

    $sql = "SELECT 
          Items.itemID, 
          itemName, 
          itemDescription, 
          GREATEST(startPriceGBP, IFNULL(MAX(bidAmountGBP), 0)) AS currentPrice, 
          COUNT(Bids.userID) AS numBids,
          a1.auctionID,
          auctionDate
        FROM Auctions a1
        INNER JOIN Items USING (itemID)
        LEFT JOIN Bids ON a1.auctionID = Bids.auctionID
        WHERE category IN (
          SELECT DISTINCT category
          FROM Items
          INNER JOIN Auctions USING (itemID)
          INNER JOIN Bids ON Auctions.auctionID = Bids.auctionID
          WHERE Bids.userID = $userID
        ) AND Items.itemID NOT IN (
          SELECT DISTINCT Items.itemID
          FROM Items
          INNER JOIN Auctions USING (itemID)
          INNER JOIN Bids ON Auctions.auctionID = Bids.auctionID
          WHERE Bids.userID = $userID
        )
        GROUP BY Items.itemID, itemName, itemDescription, startPriceGBP, auctionDate";

    $resultrec = $conn->query($sql);

    if ($resultrec === false) {
      // Output error message
      echo "Error in query: " . $conn->error;
    } else {
      // Output data for each row
      while ($row = $resultrec->fetch_assoc()) {
        print_listing_li($row['itemID'], $row['itemName'], $row['itemDescription'], $row['currentPrice'], $row['numBids'], $row['auctionDate'], $row['auctionID']);
      }
    }
  }

  $conn->close();
  ?>