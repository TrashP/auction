<?php include_once("header.php")?>
<?php include_once("db_connection.php");?>
<?php require("utilities.php")?>


<!-- // This page is for showing a user the auctions they've bid on.
// It will be pretty similar to browse.php, except there is no search bar.
// This can be started after browse.php is working with a database.
// Feel free to extract out useful functions from browse.php and put them in
// the shared "utilities.php" where they can be shared by multiple files. -->


<!-- // TODO: Check user's credentials (cookie/session). -->

<?php
  if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] == NULL || !isset($_SESSION['userID'])) {
      //maybe supply a prompt
      header("Location: register.php");
      exit;
    }
    
      // GET user info from session storage
      $userID = $_SESSION["userID"];
      $account_type = $_SESSION["account_type"];
      $username = $_SESSION["username"];
    ?>







<!-- // TODO: Perform a query to pull up the auctions they've bidded on. -->

<?php

echo "<h2>$userID</h2>";
$bidsQuery = "SELECT * FROM Bids WHERE userID = 4";
// $bidsQuery = "SELECT * FROM Bids WHERE userID = $userID";
$bidResult =  $conn->query($bidsQuery);
$bids = $bidResult->fetch_assoc();

// I will also need to get the highest bid via auction_id -> highest_bidder_id > search bid table with (highest_bidder_id, auction_id)


?>
  <!-- // TODO: Loop through results and print them out as list items. -->
  <!-- PUT HTML HERE -->


  
  <div class="container">
    <h2 class="my-3">My bids</h2>

    <table class="table">
      <thead>
        <th scope="col">Bid ID</th>
        <th scope="col">Auction ID</th>
        <th scope="col">Item</th>
        <th scope="col">My Bid Amount(£)</th>
        <th scope="col">Current Highest Bid (£)</th>
        <th scope="col">Time Till End</th>
      </thead>
      <tbody>
        <?php
          if ($bidResult->num_rows > 0) {
              while ($bid = $bidResult->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($bid['bidID']) . "</td>";
                echo "<td>" . htmlspecialchars($bid['auctionID']) . "</td>";
                echo "<td> ITEM PLACEHOLDER </td>";
                echo "<td>" . htmlspecialchars($bid['bidAmountGBP']) . "</td>";
                echo "</tr>";
              }

          } else {
            echo "<tr><td>User has not placed any bids</td></tr>";
          }

        ?>
      </tbody>

    </table>

  </div>


  


<?php include_once("footer.php")?>