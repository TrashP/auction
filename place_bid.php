<?php include_once("header.php")?>


<div class="container my-5">

<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);


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
                $userID = $_SESSION['userID'] ?? -1;
                $auctionID = (int) $_GET['auctionID'] ?? -1;
                $itemID = (int) $_GET['itemID'] ?? -1;
                $bidAmountGBP = (int) $_POST['bid'] ?? -1;
                $accountType = $_SESSION["account_type"] ?? "";

            }

            $userID = $_SESSION['userID'] ?? -1;
 

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
                $errors[] = "Please enter a bid.";
            }
            if (empty($accountType)) {
                $errors[] = "Something went wrong... Could not get account type.";
            }

            /*----------Logical Errors----------*/
            //Checks if start price is negative
            if ($bidAmountGBP < 0) {
                echo $bidAmountGBP;
                $errors[] = "Starting price must be a positive number.";
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
                mysqli_close($conn);
                exit();
            }

/* TODO #3: If everything looks good, make the appropriate call to insert
            data into the database. */
            // //insert into Bids table
        

            //Print out the stuff to be submitted to the DB



            //make query
            $placeBidQuery = "INSERT INTO Bids (userID, auctionID, bidAmountGBP) VALUES ($userID, $auctionID, $bidAmountGBP)";
            $placeBidResult = $conn->query($placeBidQuery);
      

            
            
/* TODO #4: Bid was placed, deal with outcomes  */
            if (!$placeBidResult) {
                echo '<div class="alert alert-danger mt-3" role="alert"> Error: adding data into Bids table </div>';
                mysqli_close($conn);
                exit();
            } else {

                echo "<h2>Bid Details Submitted</h2>";
                echo "<p><strong>Bid Amount:</strong> " . htmlspecialchars($bidAmountGBP) . "</p>";
                //give choices to to go to my bids or back to the listing
                
                // $listingLink = "listing.php?itemID=$itemID&auctionID=$auctionID";
                // echo '<div class="text-center">Auction successfully created! <a href="' . $listingLink . '">View your new listing.</a></div>';
                // mysqli_close($conn);
                // exit();
            }

            
            // // bid was placed succesfully -> refresh page
            // header("Location: " . $_SERVER['PHP_SELF'] . "?success=true");
            // echo "Bid was place successfully";
            // exit();

        
?>

<?php include_once("footer.php")?>