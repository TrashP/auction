<?php include_once("header.php") ?>
<?php require("utilities.php") ?>

<?php
require 'db_connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session only if it hasn't been started already
}

// Get data from the form submission
$userID = $_GET['userID'];
$auctionID = $_GET['auctionID'];
$maxBid = $_POST['max_bid'];

// Validate inputs
if (!is_numeric($maxBid) || $maxBid <= 0) {
    echo "Error: Invalid maximum bid amount.";
    exit();
}

// Get the current price of the auction from the database
$currentPriceQuery = "SELECT MAX(bidAmountGBP) as currentPriceGBP FROM Bids WHERE auctionID = '$auctionID'";
$currentPriceResult = $conn->query($currentPriceQuery);
if ($currentPriceResult->num_rows === 0) {
    echo "Error: Auction not found.";
    exit();
}
$currentPrice = $currentPriceResult->fetch_assoc()['currentPriceGBP'];

// Get the current proxy bid ceiling for this user (if it exists)
$currentProxyBidQuery = "SELECT maxBidGBP FROM ProxyBids WHERE userID = '$userID' AND auctionID = '$auctionID'";
$currentProxyBidResult = $conn->query($currentProxyBidQuery);
$currentProxyBid = $currentProxyBidResult->num_rows > 0 ? $currentProxyBidResult->fetch_assoc()['maxBidGBP'] : 0;

// Check if the new maximum bid is valid
if ($maxBid > $currentPrice && $maxBid > $currentProxyBid) {
    // Insert or update the proxy bid in the ProxyBids table
    $insertQuery = "INSERT INTO ProxyBids (userID, auctionID, maxBidGBP, currentBidGBP) 
                    VALUES ('$userID', '$auctionID', '$maxBid', '$currentPrice') 
                    ON DUPLICATE KEY UPDATE maxBidGBP = '$maxBid'";

  if ($conn->query($insertQuery) === TRUE) {
    echo '<div class="alert alert-success mt-3" role="alert">';
    echo 'Proxy bid set successfully!<br>';
    echo 'Your proxy bid price has a ceiling of £' . htmlspecialchars($maxBid);
    echo '</div>';
  } else {
        echo "Error updating proxy bid: " . $conn->error;
    }
} else {
    // Provide feedback on why the bid was rejected
    if ($maxBid <= $currentPrice) {
      echo '<div class="alert alert-danger mt-3" role="alert">';
      echo 'Error: Your maximum bid must be higher than the current auction price (£' . number_format($currentPrice, 2) . ').';
      echo '</div>';
  }
  if ($maxBid <= $currentProxyBid) {
      echo '<div class="alert alert-danger mt-3" role="alert">';
      echo 'Error: Your maximum bid must be higher than your current proxy bid (£' . number_format($currentProxyBid, 2) . ').';
      echo '</div>';
  }
  
}

mysqli_close($conn);
?>

<?php include_once("footer.php") ?>
