<?php include_once("header.php") ?>
<?php include_once("db_connection.php"); ?>
<?php require("utilities.php") ?>
<?php
ini_set('display_errors', 0); // Disable error display
error_reporting(E_ERROR | E_PARSE); // Show only errors and parse errors
?>

<!-- // This page is for showing a user the auctions they've bid on.
// It will be pretty similar to browse.php, except there is no search bar.
// This can be started after browse.php is working with a database.
// Feel free to extract out useful functions from browse.php and put them in
// the shared "utilities.php" where they can be shared by multiple files. -->

<?php
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] == NULL || !isset($_SESSION['userID'])) {
  //maybe supply a prompt
  header("Location: register.php");
  exit;
}

// GET user info from session storage
$userID = $_SESSION["userID"];
$accountType = $_SESSION["account_type"];

$errors = [];

/*----------Blank value errors----------*/
//Checks if all required fields are blank
if (empty($userID)) {
  $errors[] = "Something went wrong... Could not get user id.";
}

if (empty($accountType)) {
  $errors[] = "Something went wrong... Could not get account type.";
}

/*----------Logical Errors----------*/
if ($accountType == "Seller") {
  $errors[] = "Sellers do not have bids.";
}
if (!empty($errors)) {
  // Display errors
  echo '<div class="alert alert-danger"><ul>';
  foreach ($errors as $error) {
    echo "<li>$error</li>";
  }
  $browseLink = "browse.php";
  echo '<div class="text-center"><a href="' . $browseLink . '">Go back to the browse page.</a></div>';
  mysqli_close($conn);
  exit();
}
?>

<div class="container">
  <h2 class="my-3">My Purchases</h2>
  <?php
  
  if (isset($_SESSION['userID']) && $_SESSION['account_type'] == 'Buyer') {
    // SQL query to select Auctions won by this buyer
    $boughtItemsQuery = "SELECT DISTINCT
                Items.itemID, 
                itemName, 
                itemDescription, 
                MAX(Bids.bidAmountGBP) AS currentPrice, 
                a1.auctionID,
                AVG(rating) AS avgRating
            FROM Auctions a1
            INNER JOIN Items USING (itemID)
            INNER JOIN Bids ON a1.auctionID = Bids.auctionID
            LEFT JOIN Ratings ON a1.auctionID = Ratings.auctionID
            WHERE Bids.userID = $userID AND Bids.bidAmountGBP = (
                SELECT MAX(bidAmountGBP)
                FROM Bids b
                WHERE b.auctionID = a1.auctionID
              )
              AND a1.auctionDate <= NOW()
            GROUP BY Items.itemID, itemName, itemDescription, a1.auctionID";
  }



  $boughtItems = $conn->query($boughtItemsQuery);

  if ($boughtItems === false) {
      // Output error message
      echo "Error in query: " . $conn->error;
  } else {
    if ($boughtItems->num_rows === 0) {
    echo " <div class='no-items-container text-center'>
        <p class='no-items-text'>You have no bought items at the moment. Start exploring and bidding now!</p>
        <a href='browse.php' class='btn btn-primary mt-3'>Browse Items</a>
    </div>";


      } else {
          // Output data for each row
          while ($row = $boughtItems->fetch_assoc()) {
              print_listing_rating($row['itemID'], $row['itemName'], $row['itemDescription'], $row['currentPrice'], $row['auctionID'], (int) $row['avgRating']);
          }
      }
  }
  


  ?>
</div>

<?php include_once("footer.php") ?>
