<?php include_once("header.php") ?>
<?php require("utilities.php") ?>

<div class="container">

  <h2 class="my-3">Recommendations for you</h2>

  <?php
  require 'db_connection.php';

  // Check if the user is logged in and is a Buyer
  if (isset($_SESSION['userID']) && $_SESSION['account_type'] == 'Buyer') {
    $userID = $_SESSION['userID'];
    echo "<h5>Items that people similar to you are bidding on:</h5>";

    // Recommendation based on User Collaborative Filtering
    $sql = "SELECT DISTINCT
                Items.itemID, 
                itemName, 
                itemDescription, 
                GREATEST(a1.startPriceGBP, IFNULL(MAX(Bids.bidAmountGBP), 0)) AS currentPrice, 
                COUNT(Bids.userID) AS numBids,
                a1.auctionID,
                a1.auctionDate,
                (SELECT AVG(rating)
                 FROM Auctions a2
                 LEFT JOIN Ratings ON a2.auctionID = Ratings.auctionID
                 WHERE a1.userID = a2.userID
                 GROUP BY a2.userID) AS avgRating
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
      echo "Error in query: " . $conn->error;
    } else {
      while ($row = $resultrec->fetch_assoc()) {
        print_listing_li(
          $row['itemID'],
          $row['itemName'],
          $row['itemDescription'],
          $row['currentPrice'],
          $row['numBids'],
          new DateTime($row['auctionDate']),
          $row['auctionID'],
          (int) $row['avgRating']
        );
      }
    }

    echo "<br><br><h5>Items like the ones you bid on:</h5>";

    // Recommendation based on Item Collaborative Filtering
    $sql = "SELECT 
          Items.itemID, 
          itemName, 
          itemDescription, 
          GREATEST(startPriceGBP, IFNULL(MAX(bidAmountGBP), 0)) AS currentPrice, 
          COUNT(Bids.userID) AS numBids,
          a1.auctionID,
          auctionDate,
          (SELECT AVG(rating)
           FROM Auctions a2
           LEFT JOIN Ratings ON a2.auctionID = Ratings.auctionID
           WHERE a1.userID = a2.userID
           GROUP BY a2.userID) AS avgRating
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
      echo "Error in query: " . $conn->error;
    } else {
      while ($row = $resultrec->fetch_assoc()) {
        print_listing_li(
          $row['itemID'],
          $row['itemName'],
          $row['itemDescription'],
          $row['currentPrice'],
          $row['numBids'],
          new DateTime($row['auctionDate']),
          $row['auctionID'],
          (int) $row['avgRating']
        );
      }
    }
  } else {
    echo "<p>Please log in as a Buyer to see recommendations.</p>";
  }

  $conn->close();
  ?>

</div>

<?php include_once("footer.php") ?>
