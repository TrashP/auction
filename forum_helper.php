

<?php

    function getSellerID($conn, $auction_id)
    {

        // Enable error reporting for debugging
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // Validate auction_id
        if (empty($auction_id) || !is_numeric($auction_id)) {
            return false; // Invalid auction ID
        }

        // Prepare the SQL query
        $sellerIDQuery = "SELECT userID FROM Auctions WHERE auctionID = ? LIMIT 1";
        $stmt = $conn->prepare($sellerIDQuery);

        if (!$stmt) {
            // Handle SQL preparation error
            die("Database error: " . $conn->error);
        }

        // Bind parameters and execute the query
        $stmt->bind_param("i", $auction_id);
        $stmt->execute();

        // Fetch the result
        $result = $stmt->get_result();
        if ($result && $row = $result->fetch_assoc()) {
            return $row['userID']; // Return the seller ID
        }

        // Return false if no result found
        return false;
    }




?>



<?php

function sendBuyerMessage($conn, $auction_id, $buyer_id, $seller_id, $buyer_message)
{
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    include_once("utilities.php");

    $errors = [];

    /*----------Blank value errors----------*/
    // Checks if all required fields are blank
    if (empty($buyer_id)) {
        $errors[] = "Something went wrong... Could not get user ID.";
    }
    if (empty($auction_id)) {
        $errors[] = "Could not extract auction ID from the form.";
    }
    if (empty($buyer_message)) {
        $errors[] = "The question field cannot be empty.";
    }
    if (empty($seller_id)) {
        $errors[] = "Could not identify the seller for this auction.";
    }


    // Debugging outputs
    // echo "Auction ID: " . htmlspecialchars($auction_id) . "<br>";
    // echo "Buyer ID: " . htmlspecialchars($buyer_id) . "<br>";
    // echo "Seller ID: " . htmlspecialchars($seller_id) . "<br>";
    // echo "Buyer Message: " . htmlspecialchars($buyer_message) . "<br>";


    // Display errors if any
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




    // Proceed to insert question into the database
    $questionQuery = "INSERT INTO Messages (auction_id, buyer_id, seller_id, buyer_message) VALUES (?, ?, ?, ?)";

    // Check the database connection
    if (!$conn) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare($questionQuery);

    // Check if the query preparation was successful
    if (!$stmt) {
        echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($conn->error) . '</div>';
        $conn->close();
        exit();
    }

    // Bind parameters (auction_id: INT, buyer_id: INT, buyer_message: STRING, seller_id: INT)
    $stmt->bind_param("iiis", $auction_id, $buyer_id, $seller_id, $buyer_message);

    // Execute the statement and handle success or failure
    if ($stmt->execute()) {
        // Success: Redirect to forum page
        return TRUE;
        exit();
    } else {
        // Error during execution
        echo '<div class="alert alert-danger">Error submitting question: ' . htmlspecialchars($stmt->error) . '</div>';
        $conn->close();
        return FALSE;

        exit();
    }
}

?>



<?php
function sendSellerMessage($conn, $message_id, $seller_message)
{

    include_once("utilities.php");


    /*----------Blank value errors----------*/
    //Checks if all required fields are blank
    $errors = [];

    if (empty($message_id) || intval($message_id) <= 0) {
        $errors[] = "Invalid message ID.";
    }
 
    if (empty($seller_message)) {
        $errors[] = "The reply cannot be empty.";
    }

    // Display errors if any
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
    

    // Prepare SQL to update the seller_message column
    $updateMessageQuery = "UPDATE Messages
        SET 
            seller_message = ?,
            sent_date_seller = NOW()
        WHERE 
            message_id = ?
    ";



    if (!$conn) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare($updateMessageQuery);

    if (!$stmt) {
        echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($conn->error) . '</div>';
        return;
    }

    $stmt->bind_param("si", $seller_message, $message_id);

    // Execute the statement and handle success or failure
    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Reply sent successfully!</div>';
        return TRUE;
    } else {
        echo '<div class="alert alert-danger">Error sending reply: ' . htmlspecialchars($stmt->error) . '</div>';
        return FALSE;
    }
}
?>



<?php
function getMessagesForAuction($conn, $auction_id)
{

    include_once("utilities.php");

    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    /*----------Blank value errors----------*/
    //Checks if all required fields are blank
    
    $errors = [];

    if (empty($auction_id) || intval($auction_id) <= 0) {
        $errors[] = "Invalid auction ID.";
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



    // SQL query to fetch messages for the auction
    $messageQuery = "
        SELECT 
            m.message_id,
            m.buyer_id,
            u_buyer.firstName AS buyer_name,
            u_buyer.lastName AS buyer_lastname,
            m.buyer_message,
            m.seller_message,
            m.sent_date_buyer,
            m.sent_date_seller,
            GREATEST(COALESCE(m.sent_date_seller, m.sent_date_buyer), m.sent_date_buyer) AS latest_date
        FROM 
            Messages m
        JOIN 
            Users u_buyer ON m.buyer_id = u_buyer.userID
        WHERE 
            m.auction_id = ?
        ORDER BY 
            latest_date DESC
    ";


    

    if (!$conn) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare($messageQuery);
    


    if (!$stmt) {
        echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($conn->error) . '</div>';
        return [];
    }

    $stmt->bind_param("i", $auction_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all messages
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    $stmt->close();

    return $messages;
}
?>
