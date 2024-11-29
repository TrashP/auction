<?php include_once("header.php") ?>
<?php require("utilities.php") ?>
<?php include("forum_helper.php") ?>



<?php

    require 'db_connection.php';
    date_default_timezone_set('Europe/London');
    if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session only if it hasn't been started already
    }



    // User data (for demonstration, retrieved after login)
    $user_id = $_SESSION['userID'] ?? 0;
    $user_role = $_SESSION['account_type'] ?? ""; // 'Buyer' or 'Seller'
    $user_name = $_SESSION["firstName"] ?? "";
    $auction_id = $_GET['auctionID'] ?? 0; // Auction ID passed as a query parameter
    $item_name = $_GET['itemName'] ?? ""; // Auction ID passed as a query parameter
    

    /*----------Blank value errors----------*/
    //Checks if all required fields are blank
        $errors = [];

    if (empty($user_id)) {
        $errors[] = "Something went wrong... Could not get user id.";
    }
    if (empty($user_role)) {
        $errors[] = "Something went wrong... Could not get account type.";
    }

    if (empty($user_name)) {
        $errors[] = "Something went wrong... Could not get users first name.";
    }

    if (empty($auction_id)) {
        $errors[] = "Could not extract auction id from url.";
    }

    if (empty($item_name)) {
        $errors[] = "Could not extract item name from url @forum.";
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




  //get message data
  $messageData = getMessagesForAuction($conn, $auction_id);


//   print_r($messageData);



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Q&A Forum</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .buyer-message {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .seller-reply {
            background-color: #e2f0d9;
            padding: 10px;
            border-radius: 5px;
            margin-top: 5px;
            margin-left: 20px;
        }
        .reply-btn {
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2>Q&A Forum</h2>
    <hr>

    <?php if ($user_role === 'Buyer'): ?>
        <!-- Buyer question form -->
        <form action="process_buyer_question.php?itemName=<?php echo $item_name; ?>" method="POST">
            <div class="form-group">
                <label for="buyer_text">Ask a Question:</label>
                <textarea class="form-control" name="buyer_text" id="buyer_text" rows="3" required></textarea>
            </div>
            <input type="hidden" name="auction_id" value="<?php echo $auction_id; ?>">
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        <hr>
    <?php endif; ?>


    <!-- Check if there are any messages -->
    <?php if (empty($messageData)): ?>

   
        <?php if ($user_role === 'Seller'): ?>
            <p class="text-center text-muted">No questions in forum.</p>
        <?php endif; ?>

   
    <?php else: ?>
   
   
        <!-- Display existing messages -->
        <?php foreach ($messageData as $message): ?>
            
            <div class="buyer-message">
                <strong><?php echo htmlspecialchars($message['buyer_name']); ?>:</strong>
                <p><?php echo htmlspecialchars($message['buyer_message']); ?></p>
                <small><em>Posted on <?php echo htmlspecialchars($message['sent_date_buyer']); ?></em></small>

                <?php if (!empty($message['seller_message'])): ?>
                    <div class="seller-reply">
                        <strong>Seller Reply:</strong>
                        <p><?php echo htmlspecialchars($message['seller_message']); ?></p>
                        <small><em>Posted on <?php echo htmlspecialchars($message['sent_date_seller']); ?></em></small>
                    </div>
                <?php endif; ?>

                <?php if ($user_role === 'Seller' && empty($message['seller_message'])): ?>
                    <!-- Reply button for seller -->
                    <form action="process_seller_reply.php?itemName=<?php echo $item_name; ?>&auctionID=<?php echo $auction_id; ?>" method="POST" class="reply-btn">
                        <input type="hidden" name="message_id" value="<?php echo $message['message_id']; ?>">
                        <textarea class="form-control" name="seller_message" rows="2" placeholder="Write your reply..." required></textarea>
                        <button type="submit" class="btn btn-success btn-sm mt-2">Reply</button>
                    </form>
                <?php endif; ?>
            </div> 
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>

