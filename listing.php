<?php include_once("header.php")?>
<?php require("utilities.php")?>

<?php

  require 'db_connection.php';
  if (session_status() === PHP_SESSION_NONE) {
      session_start(); // Start the session only if it hasn't been started already
  }

  // Get info from the URL:
  $itemID = $_GET['itemID'];
  $auctionID = $_GET['auctionID'];
  $userID = $_SESSION['userID'];

  // TODO: Use item_id to make a query to the database.
  $itemsQuery = "SELECT itemName, itemDescription FROM items WHERE itemID = '$itemID'";
  $itemsResult = $conn->query($itemsQuery);
  $item = $itemsResult->fetch_assoc();

  if ($itemsResult->num_rows === 0) {
    echo '<div class="alert alert-danger mt-3" role="alert"> Error: Item does not exist </div>';
    mysqli_close($conn);
    exit();
  }

  // $bidsQuery = "SELECT MAX(bidAmountGBP) AS currentPrice, COUNT(bidAmountGBP) AS numBids FROM Bids WHERE auctionID = '$auctionID'";
  $bidsQuery = "SELECT 
  COALESCE(MAX(bidAmountGBP), 0) AS currentPrice, 
  COUNT(bidAmountGBP) AS numBids,
  COALESCE((SELECT MAX(bidAmountGBP) 
            FROM Bids 
            WHERE auctionID = $auctionID AND userID = $userID), 0) AS maxUserBid
  FROM Bids
  WHERE auctionID = '$auctionID';";

  $bidsResult = $conn->query($bidsQuery);
  $bids = $bidsResult->fetch_assoc();

  $auctionQuery = "SELECT auctionDate, startPriceGBP FROM auctions WHERE auctionID = '$auctionID'";
  $auctionResult = $conn->query($auctionQuery);
  $auction = $auctionResult->fetch_assoc();

  if ($auctionResult->num_rows === 0) {
    echo '<div class="alert alert-danger mt-3" role="alert"> Error: Auction does not exist </div>';
    mysqli_close($conn);
    exit();
  }

  // Check if the user is watching the item
  $watchQuery = "SELECT watching FROM Watchlist WHERE userID = ? AND auctionID = ?";
  $watchStmt = $conn->prepare($watchQuery);
  $watchStmt->bind_param("ii", $userID, $auctionID);
  $watchStmt->execute();
  $watchResult = $watchStmt->get_result();
  $watching = $watchResult->num_rows > 0 ? $watchResult->fetch_assoc()['watching'] : false;

  // DELETEME: For now, using placeholder data.
  $title = $item['itemName'];
  $description = $item['itemDescription'];
  $start_price = $auction['startPriceGBP'];
  $current_price = $bids['currentPrice'];
  $max_user_bid = $bids['maxUserBid'];
  $num_bids = $bids['numBids'];
  $end_time = new DateTime($auction['auctionDate']);

  $errors = [];

  /*----------Blank value errors----------*/
  //Checks if all required fields are blank
    if (empty($userID)) {
      $errors[] = "Something went wrong... Could not get user id.";
    }
    if (empty($auctionID)){
        $errors[] = "Could not extract auction id from url.";
    }
    if (empty($itemID)) {
        $errors[] = "Could not extract item id from url.";
    }
    if (empty($title)) {
      $errors[] = "Something went wrong... Could not get item name";
    }
    if (empty($description)){
      $errors[] = "Something went wrong... Could not get item description.";
    }
    if (empty($current_price)) {
      $errors[] = "Something went wrong... Could not get current price.";
    }
    if (empty($max_user_bid)) {
      $errors[] = "Something went wrong... Could not get the users highest bid.";
    }
    if (empty($num_bids)) {
      $errors[] = "Something went wrong... Could not get number of bids.";
    }
    if (empty($end_time)) {
      $errors[] = "Something went wrong... Could not get auction end time.";
    }


  // TODO: Note: Auctions that have ended may pull a different set of data,
  //       like whether the auction ended in a sale or was cancelled due
  //       to lack of high-enough bids. Or maybe not.
  
  // Calculate time to auction end:
  $now = new DateTime();
  
  if ($now < $end_time) {
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
  }
  
  // TODO: If the user has a session, use it to make a query to the database
  //       to determine if the user is already watching this item.
  //       For now, this is hardcoded.
  $has_session = true;
?>


<div class="container">

<<div class="row">
        <div class="col-sm-8">
            <h2 class="my-3"><?= htmlspecialchars($title) ?></h2>
        </div>
        <div class="col-sm-4 align-self-center">
            <?php if ($_SESSION['account_type'] == 'Buyer' && $now < $end_time): ?>
                <!-- Watchlist Button -->
                <form method="POST" action="watchlist_funcs.php">
                    <input type="hidden" name="auctionID" value="<?= $auctionID ?>">
                    <input type="hidden" name="userID" value="<?= $userID ?>">
                    <input type="hidden" name="itemID" value="<?= $itemID ?>">
                    <button type="submit" name="toggle_watchlist" class="btn <?= $watching ? 'btn-danger' : 'btn-secondary' ?>">
                        <?= $watching ? "Remove from Watchlist" : "Add to Watchlist"; ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

<div class="row"> <!-- Row #2 with auction description + bidding info -->
  <div class="col-sm-8"> <!-- Left col with item info -->

    <div class="itemDescription">
    <?php echo($description); ?>
    </div>

  </div>

  <div class="col-sm-4"> <!-- Right col with bidding info -->

    <p>
    <?php if ($now > $end_time): ?>
    <p>This auction ended on the <?php echo(date_format($end_time, 'j M H:i')) ?></p>
    <!-- TODO: Print the result of the auction here? -->
<?php else: ?>
    <p>Auction End Date: <?php echo(date_format($end_time, 'j M H:i') . $time_remaining) ?></p>  

    <p class="lead">Current Highest bid: £<?php echo(number_format($current_price, 2)) ?></p>
    
    <!-- Available only to buyers -->
    <?php if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] == 'Buyer'): ?>
        <p class="lead">My Highest bid: £<?php echo(number_format($max_user_bid, 2)) ?></p>

        <!-- Bidding form -->
        <form method="POST" action="place_bid.php?itemID=<?= $itemID ?>&auctionID=<?= $auctionID ?>&maxUserBid=<?= $max_user_bid ?>">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">£</span>
                </div>
                <input type="number" class="form-control" id="bid" name="bid">
            </div>
            <button style="margin-top: 10px;" type="submit" class="btn btn-primary form-control">Place bid</button>
        </form>
    <?php endif; ?>
<?php endif; ?>


  
  </div> <!-- End of right col with bidding info -->

</div> <!-- End of row #2 -->



<?php include_once("footer.php")?>


<script> 
// JavaScript functions: addToWatchlist and removeFromWatchlist.

function addToWatchlist(button) {
  console.log("These print statements are helpful for debugging btw");

  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'add_to_watchlist', arguments: [<?php echo($item_id);?>]},

    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_nowatch").hide();
          $("#watch_watching").show();
        }
        else {
          var mydiv = document.getElementById("watch_nowatch");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Add to watch failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func

function removeFromWatchlist(button) {
  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'remove_from_watchlist', arguments: [<?php echo($item_id);?>]},

    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_watching").hide();
          $("#watch_nowatch").show();
        }
        else {
          var mydiv = document.getElementById("watch_watching");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Watch removal failed. Try again later."));
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func
</script>