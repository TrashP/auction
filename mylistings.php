<?php include_once("header.php") ?>
<?php require("utilities.php") ?>
<?php require_once("db_connection.php") ?>

<div class="container">


  <?php
  // This page is for showing a user the auction listings they've made.
  // It will be pretty similar to browse.php, except there is no search bar.
  // This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.
  

  // TODO: Check user's credentials (cookie/session).

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
    if ($accountType == "Buyer") {
      $errors[] = "Buyers do not have listings";
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

     
  

    <!-- // TODO: Perform a query to pull up the auctions that belong to the user. -->

    <?php


      $auctionsQuery = $conn->prepare("
        SELECT 
            Auctions.*,
            Items.itemName,
            IFNULL(MAX(Bids.bidAmountGBP), 0) AS highestBid
        FROM Auctions
        INNER JOIN 
            Items ON Auctions.itemID = Items.itemID
        LEFT JOIN 
            Bids ON Auctions.auctionID = Bids.auctionID
        WHERE 
            Auctions.userID = ?
        GROUP BY 
            Auctions.auctionID");

      $auctionsQuery->bind_param("i", $userID);
      $auctionsQuery->execute();
      $auctionsResults = $auctionsQuery->get_result();


    if (!$auctionsResults) {
      echo '<div class="alert alert-danger mt-3" role="alert"> Error: adding data into listings table </div>';
      mysqli_close($conn);
      exit();
    }


    ?>
    <!-- // TODO: Loop through results and print them out as list items. -->
    <!-- PUT HTML HERE -->




    <div class="container">
      <h2 class="my-3">My Listings</h2>



      <?php if ($auctionsResults->num_rows > 0): ?>
        <table class="table">
          <thead>
            <tr>
              <th scope="col">Item</th>
              <th scope="col">Starting Price</th>
              <th scope="col">Reserve Price</th>
              <th scope="col">Quantity</th>
              <th scope="col">Current Highest Bid (£)</th>
              <th scope="col">Time Till End</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $currentDate = new DateTime();
            while ($data = $auctionsResults->fetch_assoc()) {
              // var_dump($data);
              $auctionEndDate = new DateTime($data['auctionDate']);
      
              $timeDiff = max(0, $auctionEndDate->getTimestamp() - $currentDate->getTimestamp());
              echo "<tr>";
              echo "<td><a href='listing.php?auctionID=" . $data['auctionID'] . "&itemID=" . $data['itemID'] . "'>" . htmlspecialchars($data['itemName']) . "</a></td>";

              echo "<td>£" . htmlspecialchars($data['startPriceGBP']) . "</td>";

              echo "<td>£" . htmlspecialchars($data['reservePriceGBP']) . "</td>";

              echo "<td>£" . htmlspecialchars($data['quantity']) . "</td>";

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
        echo "<tr><td>User has not made any lisitings</td></tr>";
      endif;
      ?>
    </div>
  


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