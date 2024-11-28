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

<!-- // TODO: Perform a query to pull up the auctions they've bidded on. -->

<?php
$bidsQuery = $conn->prepare("
      SELECT 
          Bids.*,
          Auctions.auctionDate,
          Items.itemID,
          Items.itemName,
          (SELECT MAX(bidAmountGBP) 
          FROM Bids 
          WHERE Bids.auctionID = Auctions.auctionID) AS highestBid
      FROM Bids
      INNER JOIN Auctions ON Bids.auctionID = Auctions.auctionID
      INNER JOIN Items ON Auctions.itemID = Items.itemID
      WHERE Bids.userID = ?
  ");


$bidsQuery->bind_param("i", $userID);
$bidsQuery->execute();
$bidsResult = $bidsQuery->get_result();

if (!$bidsResult) {
  echo '<div class="alert alert-danger mt-3" role="alert"> Error: adding data into Bids table </div>';
  mysqli_close($conn);
  exit();
}


?>
<!-- // TODO: Loop through results and print them out as list items. -->
<!-- PUT HTML HERE -->




<div class="container">
  <h2 class="my-3">My bids</h2>



  <?php if ($bidsResult->num_rows > 0): ?>

    <table class="table">
      <thead>
        <tr>
          <th scope="col">Item</th>
          <th scope="col">My Bid Amount (£)</th>
          <th scope="col">Current Highest Bid (£)</th>
          <th scope="col">Time Till End</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $currentDate = new DateTime();
        while ($data = $bidsResult->fetch_assoc()) {
          $auctionEndDate = new DateTime($data['auctionDate']);
          $timeDiff = max(0, $auctionEndDate->getTimestamp() - $currentDate->getTimestamp());
          echo "<tr>";
          echo "<td><a href='listing.php?auctionID=" . $data['auctionID'] . "&itemID=" . $data['itemID'] . "'>" . htmlspecialchars($data['itemName']) . "</a></td>";
          echo "<td>£" . htmlspecialchars($data['bidAmountGBP']) . "</td>";
          echo "<td>£" . htmlspecialchars($data['highestBid']) . "</td>";


          if ($timeDiff === 0) {
            // Auction has ended
            echo "<td>Auction has Ended</td>";
          } else {
            // Pass remaining time to JavaScript for dynamic countdown
            echo "<td><span class='countdown' data-time='$timeDiff'></span></td>";
          }

          echo "</tr>";
        }
        ?>
      </tbody>
    </table>
    <?php
  else:
    echo "<tr><td>User has not placed any bids</td></tr>";
  endif;
  ?>
</div>

<div class="container">
  <h2 class="my-3">My Bought Items</h2>
  <?php
  if (isset($_SESSION['userID']) && $_SESSION['account_type'] == 'Buyer') {
    // SQL query to select Auctions won by this buyer
    $sql = "SELECT DISTINCT
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
            GROUP BY Items.itemID, itemName, itemDescription, a1.auctionID";
  }
  $resultrec = $conn->query($sql);
  if ($resultrec === false) {
    // Output error message
    echo "Error in query: " . $conn->error;
  } else {
    // Output data for each row
    while ($row = $resultrec->fetch_assoc()) {
      print_listing_rating($row['itemID'], $row['itemName'], $row['itemDescription'], $row['currentPrice'], $row['auctionID'], (int) $row['avgRating']);
    }
  }

  ?>
</div>

<?php include_once("footer.php") ?>





<script>
  document.addEventListener("DOMContentLoaded", function () {
    initializeCountdowns();
  });

  /**
   * Initializes all countdown elements and starts their timers.
   */
  function initializeCountdowns() {
    const countdownElements = document.querySelectorAll(".countdown");

    countdownElements.forEach((element) => {
      const remainingTime = parseInt(element.getAttribute("data-time"), 10);

      if (!isNaN(remainingTime) && remainingTime > 0) {
        startCountdown(element, remainingTime);
      } else {
        element.textContent = "Auction has Ended";
      }
    });
  }

  /**
   * Starts the countdown for a given element.
   * @param {HTMLElement} element - The HTML element to update.
   * @param {number} remainingTime - The remaining time in seconds.
   */
  function startCountdown(element, remainingTime) {
    function updateCountdown() {
      if (remainingTime > 0) {
        const timeFormatted = formatTime(remainingTime);
        element.textContent = timeFormatted;

        remainingTime--;
        setTimeout(updateCountdown, 1000);
      } else {
        element.textContent = "Auction has Ended";
      }
    }

    updateCountdown();
  }

  /**
   * Formats a given time in seconds into days, hours, minutes, and seconds.
   * @param {number} seconds - The time in seconds to format.
   * @returns {string} - The formatted time string.
   */
  function formatTime(seconds) {
    const days = Math.floor(seconds / (60 * 60 * 24));
    const hours = Math.floor((seconds % (60 * 60 * 24)) / (60 * 60));
    const minutes = Math.floor((seconds % (60 * 60)) / 60);
    const secs = seconds % 60;

    return `${days}d ${hours}h ${minutes}m ${secs}s`;
  }
</script>