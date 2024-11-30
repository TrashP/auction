<?php
    require_once 'db_connection.php'; 
    require_once 'utilities.php';     
    require_once "forum_helper.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $seller_message = $_POST['seller_message'] ?? null;
    $message_id = $_POST['message_id'] ?? null;

    $item_name = $_GET['itemName'] ?? null;
    $auction_id = $_GET['auctionID'] ?? null;

    $errors = [];

    // Validate form data
    if (empty($seller_message)) {
        $errors[] = "Reply message cannot be empty.";
    }
    if (empty($message_id) || !is_numeric($message_id)) {
        $errors[] = "Invalid message ID.";
    }
    if (empty($item_name)) {
        $errors[] = "Invalid item name.";
    }
    if (empty($auction_id) || !is_numeric($auction_id)) {
        $errors[] = "Invalid auction ID.";
    }

    if (!empty($errors)) {
        // Display errors and stop execution
        echo '<div class="alert alert-danger"><ul>';
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo '</ul></div>';
        exit();
    }

    // call the sendSellerMessage method 
    $result = sendSellerMessage($conn, $message_id, $seller_message);
    

    if ($result) {
        echo '<div class="alert alert-success">Reply submitted successfully!</div>';
        // Redirect back to the forum page
        header("Location: forum.php?auctionID=" . urlencode($auction_id) . "&itemName=" . urlencode($item_name));
        exit();
    } else {
        echo '<div class="alert alert-danger">Error submitting reply: ' . $stmt->error . '</div>';
    }

    $stmt->close();
}
?>
