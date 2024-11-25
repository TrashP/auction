<?php include_once("header.php") ?>
<?php require("utilities.php") ?>

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
$itemsQuery = "SELECT itemName, itemDescription, itemPhotoPath FROM items WHERE itemID = '$itemID'";
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
$photo = $item['itemPhotoPath'];
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
if (empty($auctionID)) {
  $errors[] = "Could not extract auction id from url.";
}
if (empty($itemID)) {
  $errors[] = "Could not extract item id from url.";
}
if (empty($title)) {
  $errors[] = "Something went wrong... Could not get item name";
}
if (empty($description)) {
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

  <div class="row mb-4"> <!-- Row #1 with auction image + details -->
    <!-- Left column for image -->
    <div class="col-sm-7 d-flex flex-column align-items-center">
      <img src="<?php echo htmlspecialchars($photo); ?>" class="img-fluid mb-4"
        style="object-fit: contain; max-height: 300px;"> <!-- Added margin below the photo -->
      <h2 class="text-center"><?php echo ($title); ?></h2>
    </div>

    <!-- Right column for watchlist, auction details, and bidding -->
    <div class="col-sm-5">
      <?php if ($now > $end_time): ?>
        <!-- Auction Ended Message -->
        <div class="card p-3 text-center mb-3">
          <h5 style="color: red;">Auction Ended</h5>
          <p><strong>Ended On:</strong> <?php echo (date_format($end_time, 'j M H:i')) ?></p>
        </div>
      <?php else: ?>
        <!-- Watchlist Buttons -->
        <div class="mb-3"> <!-- Added margin for spacing -->
          <?php if ($_SESSION['account_type'] == 'Buyer'): ?>
            <div id="watch_nowatch" <?php if ($has_session && $watching)
              echo ('style="display: none"'); ?>>
              <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="addToWatchlist()">+ Add to
                watchlist</button>
            </div>
            <div id="watch_watching" <?php if (!$has_session || !$watching)
              echo ('style="display: none"'); ?>>
              <button type="button" class="btn btn-success btn-sm w-100 mb-1" disabled>Watching</button>
              <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeFromWatchlist()">Remove
                watch</button>
            </div>
          <?php endif; ?>
        </div>

        <!-- Auction Details -->
        <div class="card p-3 mb-3">
          <h5>Auction Details</h5>
          <p><strong>Auction End Date:</strong> <?php echo (date_format($end_time, 'j M H:i') . $time_remaining) ?></p>
          <p class="lead"><strong>Current Highest Bid:</strong> £<?php echo (number_format($current_price, 2)) ?></p>
        </div>

        <!-- Place Your Bid -->
        <div class="card p-3" style="margin-top: -20px;"> <!-- Pulled upwards -->
          <h5>Place Your Bid</h5>
          <?php if ($_SESSION['account_type'] == 'Buyer'): ?>
            <p class="lead"><strong>My Highest Bid:</strong> £<?php echo (number_format($max_user_bid, 2)) ?></p>
            <form method="POST"
              action="place_bid.php?itemID=<?= $itemID ?>&auctionID=<?= $auctionID ?>&maxUserBid=<?= $max_user_bid ?>">
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text">£</span>
                </div>
                <input type="number" class="form-control" id="bid" name="bid" placeholder="Enter your bid" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Place Bid</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="row"> <!-- Row #2 with auction description -->
      <div class="col-sm-12">
        <h5><strong>Description</strong></h5> <!-- Added Description header -->
        <div class="itemDescription">
          <?php echo ($description); ?>
        </div>
      </div>
    </div>

  </div> <!-- End of right col with bidding info -->

</div> <!-- End of row #2 -->



<?php include_once("footer.php") ?>


<script>
  // JavaScript functions: addToWatchlist and removeFromWatchlist.

  function addToWatchlist(button) {
    console.log("These print statements are helpful for debugging btw");

    // This performs an asynchronous call to a PHP function using POST method.
    // Sends item ID as an argument to that function.
    $.ajax('watchlist_funcs.php', {
      type: "POST",
      data: { functionname: 'add_to_watchlist', arguments: [<?php echo ($item_id); ?>] },

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
      data: { functionname: 'remove_from_watchlist', arguments: [<?php echo ($item_id); ?>] },

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