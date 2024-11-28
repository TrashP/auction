<?php include_once("header.php") ?>
<?php require("utilities.php") ?>
<?php require_once("db_connection.php") ?>

<div class="container">

  <h2 class="my-3">My Listings</h2>

  <?php
  // This page is for showing a user the auction listings they've made.
  // It will be pretty similar to browse.php, except there is no search bar.
  // This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.
  

  // TODO: Check user's credentials (cookie/session).
  
  // TODO: Perform a query to pull up their auctions.
  
  // TODO: Loop through results and print them out as list items.
  
  ?>

  <h2 class="my-3">My Reviews</h2>
  <table class="table">
    <thead>
      <tr>
        <th scope="col">First Name</th>
        <th scope="col">Last Name</th>
        <th scope="col">Item</th>
        <th scope="col">Rating</th>
        <th scope="col">Comment</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if (isset($_SESSION['userID']) && $_SESSION['account_type'] == 'Seller') {
        $userID = $_SESSION['userID'];

        // SQL query to select the ratings and comments for this seller
        $stmt = $conn->prepare("SELECT firstName, lastName, itemName, rating, comment
                    FROM Auctions
                    INNER JOIN Ratings USING (auctionID)
                    INNER JOIN Items USING (itemID)
                    INNER JOIN Users u1 ON Ratings.userID = u1.userID
                    WHERE Auctions.userID = ?");

        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        $avgRating = 0;

        while ($row = $result->fetch_assoc()) {
          $avgRating += (int) $row['rating'];
          echo "<tr>";
          echo "<td>" . $row['firstName'] . "</td>";
          echo "<td>" . $row['lastName'] . "</td>";
          echo "<td>" . $row['itemName'] . "</td>";
          echo "<td>" . $row['rating'] . "</td>";
          echo "<td>" . $row['comment'] . "</td>";
          echo "</tr>";
        }

        if ($result->num_rows == 0) {
          $avgRating = "N/A";
        } else {
          $avgRating /= $result->num_rows;
        }
      }

      ?>
    </tbody>
  </table>
  <h3 class="my-3">Average Rating: <?php echo $avgRating ?></h3>

  <h2 class="my-3">My Sold Items</h2>
  <?php
  if (isset($_SESSION['userID']) && $_SESSION['account_type'] == 'Seller') {
    $userID = $_SESSION['userID'];

    // SQL query to select Auctions sold by this seller
    $sql = "SELECT DISTINCT
                Items.itemID, 
                itemName, 
                itemDescription, 
                MAX(Bids.bidAmountGBP) AS currentPrice, 
                a1.auctionID,
                a1.reservePriceGBP
            FROM Auctions a1
            INNER JOIN Items USING (itemID)
            INNER JOIN Bids ON a1.auctionID = Bids.auctionID
            WHERE a1.userID = $userID AND auctionDate < NOW()
            GROUP BY Items.itemID, itemName, itemDescription, a1.auctionID, a1.reservePriceGBP
            HAVING currentPrice >= reservePriceGBP";
  }
  $resultrec = $conn->query($sql);
  if ($resultrec === false) {
    // Output error message
    echo "Error in query: " . $conn->error;
  } else {
    // Output data for each row
    while ($row = $resultrec->fetch_assoc()) {
      print_listing_seller($row['itemID'], $row['itemName'], $row['itemDescription'], $row['currentPrice'], $row['auctionID']);
    }
  }

  ?>

  <?php include_once("footer.php") ?>