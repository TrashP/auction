<?php include_once("header.php")?>


<div class="container my-5">

<?php
// This function takes the form data and adds the new auction to the database.

/* TODO #1: Connect to MySQL database (perhaps by requiring a file that
            already does this). */
            require 'db_connection.php';
            if (session_status() === PHP_SESSION_NONE) {
                session_start(); // Start the session only if it hasn't been started already
            }

/* TODO #2: Extract form data into variables. Because the form was a 'post'
            form, its data can be accessed via $POST['auctionTitle'], 
            $POST['auctionDetails'], etc. Perform checking on the data to
            make sure it can be inserted into the database. If there is an
            issue, give some semi-helpful feedback to user. */

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $title = $_POST['auctionTitle'] ?? '';
                $description = $_POST['auctionDetails'] ?? '';
                $quantity = $_POST['auctionQuantity'] ?? -1;
                $category = $_POST['auctionCategory'] ?? 'Others';
                $startPrice = $_POST['auctionStartPrice'] ?? -1;
                $reservePrice = $_POST['auctionReservePrice'] ?? null;
                $endDate = $_POST['auctionEndDate'] ?? null;
            }

            $errors = [];

            /*----------Blank value errors----------*/
            //Checks if all required fields are blank
            if (empty($title)) {
                $errors[] = "Title is required.";
            }
            if (empty($quantity)){
                $errors[] = "Quantity is required.";
            }
            if (empty($category)) {
                $errors[] = "Category is required.";
            }
            if (empty($startPrice)) {
                $errors[] = "Starting price is required.";
            }
            if (empty($endDate)) {
                $errors[] = "Auction end date is required.";
            }

            /*----------Logical Errors----------*/
            //Checks if start price is negative
            if ($startPrice < 0) {
                $errors[] = "Starting price must be a positive number.";
            }

            //Checks if quantity is negative
            if ($quantity < 0) {
                $errors[] = "Quantity must be a positive number.";
            }

            //Checks if end date is before today
            $today = date("Y-m-d H:i:s");
            if ($endDate < $today) {
                $errors[] = "End date must be today onwards.";
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
            
            //Print out the stuff to be submitted to the DB
            echo "<h2>Auction Details Submitted</h2>";
            echo "<p><strong>Auction Title:</strong> " . htmlspecialchars($title) . "</p>";
            echo "<p><strong>Auction Description:</strong> " . htmlspecialchars($description) . "</p>";
            echo "<p><strong>Quantity:</strong> " . htmlspecialchars($quantity) . "</p>";
            echo "<p><strong>Category:</strong> " . htmlspecialchars($category) . "</p>";
            echo "<p><strong>Starting Price:</strong> $" . number_format($startPrice, 2) . "</p>";
            //Checks if reserve price is set
            if ($reservePrice != null) {
                echo "<p><strong>Reserve Price:</strong> $" . number_format($reservePrice, 2) . "</p>";
            } else {
                echo "<p><strong>Reserve Price:</strong> Not set</p>";
            }
            echo "<p><strong>Auction End Date:</strong> " . htmlspecialchars($endDate) . "</p>";
            
            //insert into Items table
            $itemsQuery = "INSERT INTO Items (itemName, itemDescription, category)
                      VALUES ('$title', '$description', '$category')";
            
            $itemsResult = $conn->query($itemsQuery);

            if (!$itemsResult) {
                echo '<div class="alert alert-danger mt-3" role="alert"> Error: adding data into Items table </div>';
                mysqli_close($conn);
                exit();
            }

            //insert into Auctions table
            $userID = $_SESSION['userID'];
            $itemID = mysqli_insert_id($conn);

            $auctionsQuery = "INSERT INTO Auctions (userID, itemID, auctionDate, startPriceGBP, reservePriceGBP, quantity)
                    VALUES ('$userID', '$itemID', '$endDate', '$startPrice', '$reservePrice', '$quantity');";

            $auctionsResult = mysqli_query($conn, $auctionsQuery);

            //If it is successful, generate a listing link
            if ($auctionsResult) {
                // If all is successful, let user know.
                // Provide link to listing
                $listingLink = "listing.php?itemID=$itemID&auctionID=$auctionID";
                echo '<div class="text-center">Auction successfully created! <a href="' . $listingLink . '">View your new listing.</a></div>';
                mysqli_close($conn);
                exit();
            } else {
                echo '<div class="alert alert-danger mt-3" role="alert"> Error: adding data into Auctions table </div>';
                mysqli_close($conn);
                exit();
            }         
?>

<?php include_once("footer.php")?>