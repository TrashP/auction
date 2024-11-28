<?php include_once("header.php") ?>
<?php require("utilities.php") ?>
<?php
ini_set('display_errors', 0); // Disable error display
error_reporting(E_ERROR | E_PARSE); // Show only errors and parse errors
?>
<div class="container">

  <h2 class="my-3">Browse listings</h2>

  <div id="searchSpecs">
    <!-- Chat-GPT has been used to debug code and provide small code snippets on this page. -->
    <!-- When this form is submitted, this PHP page is what processes it.
     Search/sort specs are passed to this page through parameters in the URL
     (GET method of passing data to a page). -->
    <form method="get" action="browse.php">
      <?php
      $userSearch = '';
      $userCategory = '';
      $userSort = '';

      if ($_SERVER['REQUEST_METHOD'] =='GET') {
        $userSearch = htmlspecialchars($_GET['keyword']);
        $userCategory = htmlspecialchars($_GET['cat']);
        $userSort = htmlspecialchars($_GET['order_by']);
      }
      ?>

      <div class="row">
        <div class="col-md-5 pr-0">
          <div class="form-group">
            <label for="keyword" class="sr-only">Search keyword:</label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text bg-transparent pr-0 text-muted">
                  <i class="fa fa-search"></i>
                </span>
              </div>
              <input type="text" class="form-control border-left-0" name="keyword" id="keyword"
                placeholder="Search for anything" value="<?php echo $userSearch; ?>">
            </div>
          </div>
        </div>
        <div class="col-md-3 pr-0">
          <div class="form-group">
            <label for="cat" class="sr-only">Search within:</label>
            <select class="form-control" name="cat" id="cat" value="<?php echo $userCategory; ?>">
              <option selected value="all">All categories</option>
              <?php
              // Connect to database to dynamically create categories
              require 'db_connection.php';

              // SQL query to fetch categories
              $sql = "SELECT DISTINCT category FROM Items";
              $result = $conn->query($sql);

              if ($result->num_rows > 0) {
                // Output data from each row as an option element
                while ($row = $result->fetch_assoc()) {
                  echo "<option value='" . $row["category"] . "' " . (($userCategory == $row["category"]) ? "selected" : "") . ">" . htmlspecialchars($row["category"]) . "</option>";
                }
              }

              $conn->close();
              ?>
            </select>
          </div>
        </div>
        <div class="col-md-3 pr-0">
          <div class="form-inline">
            <label class="mx-2" for="order_by">Sort by:</label>
            <select class="form-control" name="order_by" id="order_by">
              <option selected value="pricelow" <?php echo ($userSort == 'pricelow') ? 'selected' : '' ?>>Price (low to
                high)</option>
              <option value="pricehigh" <?php echo ($userSort == 'pricehigh') ? 'selected' : '' ?>>Price (high to low)
              </option>
              <option value="date" <?php echo ($userSort == 'date') ? 'selected' : '' ?>>Soonest expiry</option>
              <option value="rating" <?php echo ($userSort == 'rating') ? 'selected' : '' ?>>Rating</option>
            </select>
          </div>
        </div>
        <div class="col-md-1 px-0">
          <button type="submit" class="btn btn-primary">Search</button>
        </div>
      </div>
    </form>
  </div> <!-- end search specs bar -->


</div>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require "db_connection.php";

// Retrieve these from the URL
if (!isset($_GET['keyword'])) {
  // TODO: Define behavior if a keyword has not been specified.
  $keyword = null;
} else {
  $keyword = $_GET['keyword'];
}

if (!isset($_GET['cat'])) {
  // TODO: Define behavior if a category has not been specified.
  $category = null;
} else {
  $category = $_GET['cat'];
}

if (!isset($_GET['order_by'])) {
  // TODO: Define behavior if an order_by value has not been specified.
  $ordering = "pricelow";
} else {
  $ordering = $_GET['order_by'];
}

if (!isset($_GET['page'])) {
  $curr_page = 1;
} else {
  $curr_page = $_GET['page'];
}
/* TODO: Use above values to construct a query. Use this query to 
   retrieve data from the database. (If there is no form data entered,
   decide on appropriate default value/default query to make. */

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
LEFT JOIN Ratings ON Ratings.auctionID = a1.auctionID
GROUP BY Items.itemID, itemName, itemDescription, startPriceGBP, auctionDate";


// Adding conditions based on keyword and category search
if ($keyword !== null and $keyword !== '') {
  $keyword = htmlspecialchars($keyword); // Sanitize input to prevent XSS
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
LEFT JOIN Ratings ON Ratings.auctionID = a1.auctionID
WHERE itemName LIKE '%$keyword%'
GROUP BY Items.itemID, itemName, itemDescription, startPriceGBP, auctionDate";
}

if ($category !== null and $category !== 'all') {
  $category = htmlspecialchars($category);
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
LEFT JOIN Ratings ON Ratings.auctionID = a1.auctionID
WHERE Items.category = '$category'
GROUP BY Items.itemID, itemName, itemDescription, startPriceGBP, auctionDate";
}

if ($category !== null and $category !== 'all' and $keyword !== null and $keyword !== '') {
  $category = htmlspecialchars($category);
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
LEFT JOIN Ratings ON Ratings.auctionID = a1.auctionID
WHERE Items.category = '$category' AND itemName LIKE '%$keyword%'
GROUP BY Items.itemID, itemName, itemDescription, startPriceGBP, auctionDate";
}

if ($ordering == "pricelow") {
  $sql .= " ORDER BY currentPrice ASC";
} else if ($ordering == "pricehigh") {
  $sql .= " ORDER BY currentPrice DESC";
} else if ($ordering == "date") {
  $sql .= " ORDER BY auctionDate ASC";
} else {
  $sql .= " ORDER BY avgRating DESC, currentPrice ASC";
}

$result = $conn->query($sql);

/* For the purposes of pagination, it would also be helpful to know the
   total number of results that satisfy the above query */
$num_results = $result->num_rows; // TODO: Calculate me for real
$results_per_page = 10;
$curr_page = isset($_GET['page']) ? (int) $_GET['page'] : 1; // Get current page from URL or default to 1
$max_page = ceil($num_results / $results_per_page);
?>

<div class="container mt-5">

  <!-- TODO: If result set is empty, print an informative message. Otherwise... -->
  <?php
  if ($num_results == 0) {
    echo "No results found";
  }
  ?>
  <ul class="list-group">

    <!-- TODO: Use a while loop to print a list item for each auction listing
     retrieved from the query -->

    <?php
    if (isset($_SESSION['firstName'])) {
      $firstName = $_SESSION['firstName'];
      echo "<h3>Hi, " . $firstName . "</h3>";
    }

    if ($result === false) {
      // Output error message
      echo "Error in query: " . $conn->error;
    } else {
      // Output data for each row
      $skip = $results_per_page * ($curr_page - 1);
      $res = $results_per_page;
      echo "<h5>All Items available for auction:</h5>";
      while ($row = $result->fetch_assoc()) {
        if ($skip == 0 and $res != 0) {


          print_listing_li($row['itemID'], $row['itemName'], $row['itemDescription'], $row['currentPrice'], $row['numBids'], new DateTime($row['auctionDate']), $row['auctionID'], (int) $row['avgRating']);
          $res -= 1;
        } else {
          $skip -= 1;
        }
      }
    }

    ?>

  </ul>

  <!-- Pagination for results listings -->
  <nav aria-label="Search results pages" class="mt-5">
    <ul class="pagination justify-content-center">

      <?php

      // Copy any currently-set GET variables to the URL.
      $querystring = "";
      foreach ($_GET as $key => $value) {
        if ($key != "page") {
          $querystring .= "$key=$value&amp;";
        }
      }

      $high_page_boost = max(3 - $curr_page, 0);
      $low_page_boost = max(2 - ($max_page - $curr_page), 0);
      $low_page = max(1, $curr_page - 2 - $low_page_boost);
      $high_page = min($max_page, $curr_page + 2 + $high_page_boost);

      if ($curr_page != 1) {
        echo ('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
        <span class="sr-only">Previous</span>
      </a>
    </li>');
      }

      for ($i = $low_page; $i <= $high_page; $i++) {
        if ($i == $curr_page) {
          // Highlight the link
          echo ('
    <li class="page-item active">');
        } else {
          // Non-highlighted link
          echo ('
    <li class="page-item">');
        }

        // Do this in any case
        echo ('
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
      }

      if ($curr_page != $max_page) {
        echo ('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
      }
      $conn->close();
      ?>

    </ul>
  </nav>
</div>



<?php include_once("footer.php") ?>