<?php include_once("header.php")?>

<div class="container my-5">

<?php
// This function takes the form data and adds the new auction to the database.

/* TODO #1: Connect to MySQL database (perhaps by requiring a file that
            already does this). */
            require 'db_connection.php';
?>

<?php
/* TODO #2: Extract form data into variables. Because the form was a 'post'
            form, its data can be accessed via $POST['auctionTitle'], 
            $POST['auctionDetails'], etc. Perform checking on the data to
            make sure it can be inserted into the database. If there is an
            issue, give some semi-helpful feedback to user. */
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $title = $_POST['auctionTitle'] ?? '';
                $details = $_POST['auctionDetails'] ?? '';
                $category = $_POST['auctionCategory'] ?? '';
                $startPrice = (float)($_POST['auctionStartPrice'] ?? -1.00);
                $reservePrice = $_POST['auctionReservePrice'] ?? null;
                $auctionEndDate = $_POST['auctionEndDate'] ?? null;
            }

            $errors = [];

            echo $startPrice;
            echo $category;
            echo $title;

            /*----------Blank value errors----------*/
            //Checks if all required fields are blank
            if (empty($title)) {
                $errors[] = "Title is required.";
            }
            if (empty($category)) {
                $errors[] = "Category is required.";
            }
            if (empty($startPrice)) {
                $errors[] = "Starting price is required.";
            }
            if (empty($auctionEndDate)) {
                $errors[] = "Auction end date is required.";
            }

            /*----------Logical Errors----------*/
            //Checks if start price is negative
            if ($startPrice < 0) {
                $errors[] = "Starting price must be a positive number.";
            }

            //checks if end date is before today
            $today = date("Y-m-d H:i:s");
            if ($_POST['auctionEndDate'] < $today) {
                $errors[] = "End date must be today onwards.";
            }

            if (!empty($errors)) {
                // Display errors
                echo '<div class="alert alert-danger"><ul>';
                foreach ($errors as $error) {
                    echo "<li>$error</li>";
                }
            }
?>

<?php
/* TODO #3: If everything looks good, make the appropriate call to insert
            data into the database. */
            

// If all is successful, let user know.
echo('<div class="text-center">Auction successfully created! <a href="FIXME">View your new listing.</a></div>');


?>

</div>


<?php include_once("footer.php")?>