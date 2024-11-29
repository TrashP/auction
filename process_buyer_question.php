<?php
require_once 'db_connection.php'; 
require_once 'utilities.php';     
require_once "forum_helper.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Start the session only if it hasn't been started already
    }

    // Get the form data
    $auction_id = $_POST['auction_id'] ?? 0;
    $buyer_text = $_POST['buyer_text'] ?? "";
    $seller_id = $_POST['userID'] ?? "";
    $buyer_id = $_SESSION['userID'] ?? "";
    $item_name = $_GET['itemName'] ?? "";

    //get seller if
    $seller_id = !empty($auction_id) ? getSellerID($conn, $auction_id) : "";
    


    /*----------Blank value errors----------*/
    //Checks if all required fields are blank
    $errors = [];
    if (empty($auction_id)) {
        $errors[] = "Auction ID is missing.";
    }
    if (empty($buyer_id)) {
        $errors[] = "You must be logged in to post a question.";
    }
    if (empty($buyer_text)) {
        $errors[] = "Question text cannot be empty.";
    }

    if (empty($item_name)) {
        $errors[] = "Could not extract item name from url.";
    }

    if (empty($seller_id)) {
        $errors[] = "Could not get seller id.";
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






    // Call the sendBuyerMessage method
    $result = sendBuyerMessage($conn, $auction_id, $buyer_id, $seller_id, $buyer_text);


    if ($result) {
        echo '<div class="alert alert-success">Your question has been posted successfully.</div>';
        // Redirect back to the forum page

    } else {
        echo '<div class="alert alert-danger">Failed to post your question. Please try again.</div>';
    }
}
