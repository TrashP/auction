// TODO: Extract $_POST variables, check they're OK, and attempt to make a bid.
// Notify user of success/failure and redirect/give navigation options.


    <!-- // check if user is logged in and is a buyer -->
<?php

    // pretty sure checking if null is redundant
  if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] == NULL || !isset($_SESSION['account_type']) || $_SESSION["account_type"] == "seller") {
      //maybe supply a prompt
      header("Location: register.php");
      exit;
    }
    
      // GET user info from session storage
      $userID = $_SESSION["userID"];

?>


    <!-- //extract auction id from url

    //get form data for making a bid

    

    //validate form data

    //post request (place bid result file)

    //handle post request -->