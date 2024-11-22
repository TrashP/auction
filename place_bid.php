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
            
            // print_r($_GET);
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $userID = $_SESSION['userID'] ?? 0;
                $auctionID = $_GET['auctionID'] ?? 0;
                $itemID = $_GET['itemID'] ?? 0;
                $bidAmountGBP = $_POST['bid'] ?? 0;
                $accountType = $_SESSION["account_type"] ?? "";
                $maxUserBid = $_GET["maxUserBid"] ?? 0;

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

            if (empty($maxUserBid)) {
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
      

            
            
/* TODO #5: Bid was placed, deal with outcomes  */
            if (!$placeBidResult) {
                echo '<div class="alert alert-danger mt-3" role="alert"> Error: adding data into Bids table </div>';
                mysqli_close($conn);
                exit();
            } else {

                echo "<h2>Bid Details Submitted</h2>";
                echo "<p><strong>Bid Amount:</strong> " . htmlspecialchars($bidAmountGBP) . "</p>";
                $listingLink = "listing.php?itemID=$itemID&auctionID=$auctionID";
                echo '<div class="text-center"><a href="' . $listingLink .'">Go back to the listing.</a></div>';


            }

            
  

        
?>

<?php include_once("footer.php")?>