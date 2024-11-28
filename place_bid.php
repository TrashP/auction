<?php include_once("header.php")?>


<div class="container my-5">

<?php

// This function takes the form data and adds the new bid to the database.

/* TODO #1: Connect to MySQL database (perhaps by requiring a file that
            already does this). */
            include_once("db_connection.php");
            if (session_status() === PHP_SESSION_NONE) {
                session_start(); // Start the session only if it hasn't been started already
            }

            //check if logged in???

/* TODO #2: Extract form data into variables. If there is an
            issue, give some semi-helpful feedback to user. */
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $userID = $_SESSION['userID'] ?? 0;
                $auctionID = $_GET['auctionID'] ?? 0;
                $itemID = $_GET['itemID'] ?? 0;
                $bidAmountGBP = $_POST['bid'] ?? 0;
                $accountType = $_SESSION["account_type"] ?? "";
                $maxUserBid = $_GET["maxUserBid"] ?? 0;
                $isProxyBidEnabled = isset($_POST['proxy_bid_enabled']) ? true : false;
            }

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
            if (empty($bidAmountGBP)) {
                $errors[] = "Please enter a bid greater than £0.";
            }
            if (empty($accountType)) {
                $errors[] = "Something went wrong... Could not get account type.";
            }
            if ($maxUserBid === null) {
                $errors[] = "Could not extract max user bid from url.";;
            }

            /*----------Logical Errors----------*/
            //Checks if start price is negative
            if ($bidAmountGBP < 0) {
                echo $bidAmountGBP;
                $errors[] = "Starting price must be a positive number.";
            }

            if ($maxUserBid >= $bidAmountGBP) {
                $errors[] = "You must bid higher than your previous bid.";
            }

            if ($accountType == "Seller") {
              $errors[] = "Sellers cannot place a bid.";
            }

            //Displays all possible errors with the fields
            if (!empty($errors)) {
                // Display errors
                echo '<div class="alert alert-danger"><ul>';
                foreach ($errors as $error) {
                    echo "<li>$error</li>";
                }
                $listingLink = "listing.php?itemID=$itemID&auctionID=$auctionID";
                echo '<div class="text-center"><a href="' . $listingLink .'">Go back to the listing.</a></div>';
                mysqli_close($conn);
                exit();
            }

/* TODO #4: If everything looks good, make the appropriate call to insert
            data into the database. */

            //make query
            $placeBidQuery = "INSERT INTO Bids (userID, auctionID, bidAmountGBP) VALUES ($userID, $auctionID, $bidAmountGBP)";
            $placeBidResult = $conn->query($placeBidQuery);


            //update highest bidder id if necessary
            if ($bidAmountGBP > $maxUserBid) {
                $updateAuctionQuery = "UPDATE Auctions SET highestBidderID = ? WHERE auctionID = ?";
                $result = $conn->prepare($updateAuctionQuery);
            
                if (!$result) {die("Prepare failed: " . $conn->error);}
            
                $result->bind_param("ii", $userID, $auctionID);
            
                if ($result->execute()) {
                    echo "Auction table updated with new highest bidder.<br>";
                } else {
                    die("Error updating auction: " . $result->error);
                }
            }

            // Check the current highest bid
            $currentPriceQuery = "SELECT COALESCE(MAX(bidAmountGBP), 0) AS currentPrice FROM Bids WHERE auctionID = '$auctionID'";
            $currentPriceResult = $conn->query($currentPriceQuery);
            $currentPrice = $currentPriceResult->fetch_assoc()['currentPrice'];
            
            #for the person who has the highest proxy bid ceiling
            $proxyQuery = "SELECT userID, maxBidGBP FROM ProxyBids WHERE auctionID = '$auctionID' AND maxBidGBP > '$bidAmountGBP' ORDER BY maxBidGBP DESC LIMIT 1";
            $proxyResult = $conn->query($proxyQuery);

            if ($proxyResult->num_rows > 0) {
                $proxyBid = $proxyResult->fetch_assoc();
                $proxyUserID = $proxyBid['userID'];
                $proxyMaxBid = $proxyBid['maxBidGBP'];
                $proxyBidAmount = min($proxyMaxBid, $bidAmountGBP + 1);
        
                if ($proxyBidAmount > $bidAmountGBP) {
                    // Place proxy bid
                    $proxyInsertQuery = "INSERT INTO Bids (userID, auctionID, bidAmountGBP) VALUES ('$proxyUserID', '$auctionID', '$proxyBidAmount')";
                    $placeProxyBidResult = $conn->query($proxyInsertQuery);
                }
            }
            
            
/* TODO #5: Bid was placed, deal with outcomes  */
            if (!$placeBidResult) {
                echo '<div class="alert alert-danger mt-3" role="alert">
                        <strong>Error:</strong> Unable to add data to the Bids table. Please try again later.
                    </div>';
                mysqli_close($conn);
                exit();
            } else {
                echo '<div class="alert alert-success mt-3" role="alert">
                        <h2>Bid Successfully Submitted</h2>
                        <p><strong>Bid Amount:</strong> £' . number_format(htmlspecialchars($bidAmountGBP), 2) . '</p>';

                // Check if proxy bidding is enabled
                // if ($isProxyBidEnabled) {
                //     echo '<p><strong>Proxy Bid Status:</strong> Enabled</p>';
                //     echo '<p><strong>Maximum Proxy Bid:</strong> £' . number_format(htmlspecialchars($maxBidGBP), 2) . '</p>';
                // } else {
                //     echo '<p><strong>Proxy Bid Status:</strong> Not Enabled</p>';
                // }

                $listingLink = "listing.php?itemID=" . urlencode($itemID) . "&auctionID=" . urlencode($auctionID);
                echo '<div class="text-center mt-3">
                        <a href="' . htmlspecialchars($listingLink) . '" class="btn btn-primary">Go Back to Listing</a>
                    </div>';
            }

?>

<?php include_once("footer.php")?>