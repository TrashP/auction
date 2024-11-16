<?php
    include_once("db_connection.php");


    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        header("Location: browse.php");
        exit();
    }



    //verify that the user is a buyer
    if ($_SESSION["account_type"] != "Buyer") {
        die("Bids can only be made from a buyer based account>Go back</a>");
    }

    //make sure we are not missing any data
    if (empty($auctionID) || empty($user_id) || empty($bidAmount)) {
        die("AuctionID, UserID and bidAmount are all required'>Go back</a>");
    }


    $auctionID = $_POST['auctionID'] ?? '';
    $userID = $_POST['userID'] ?? '';
    $bidAmount = $_POST['bidAmount'] ?? '';



    //create post request
    $stmt = $conn->prepare("INSERT INTO Bids (userID, auctionID, bidAmountGBP) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $userID, $auctionID, $bidAmount);

    


    if ($stmt->execute()) {
        echo "New Record created successfully";
        $result = $stmt->get_result();
        // echo "<div class='text-center'>You are now logged in! Redirecting...</div>";
        // header("refresh:2;url=browse.php");
        // exit();

    } else {
        echo "Error: ". $stmt->error;
    }

        
    $conn->close();
?>