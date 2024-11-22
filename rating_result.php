<?php include_once("header.php") ?>

<div class="container my-5">
    <?php
    include_once 'db_connection.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Start the session only if it hasn't been started already
    }

    $userID = $_GET['userID'];
    $auctionID = $_GET['auctionID'];
    $error_messages = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $rating = $_POST['rating'];
        $comment = $_POST['comment'];

        // Validate rating
        if (empty($rating)) {
            $error_messages[] = "Rating is required.";
        }

        // If no errors, process the form
        if (empty($error_messages)) {
            // Save data to database
    
            //Prepare an SQL statement
            $stmt = $conn->prepare("INSERT INTO Ratings (userID, auctionID, rating, comment) 
                                            VALUES (?, ?, ?, ?)
                                            ON DUPLICATE KEY UPDATE
                                                rating = VALUES(rating),
                                                comment = VALUES(comment);");
            if ($stmt === false) {
                die("Error preparing statement: " . $conn->error);
            }

            // Bind parameters
            $stmt->bind_param("iiis", $userID, $auctionID, $rating, $comment);

            // Execute the statement
            if ($stmt->execute()) {
                echo "Data inserted successfully</br>";
            } else {
                echo "Error inserting data: " . $stmt->error . "</br>";
            }

            echo "Click  <a href='mybids.php'>here</a> to go back to myBids.</br>";

            $stmt->close();

        } else {
            header('Location: rating.php?error=' . $error_messages[0] . '&auctionID=' . $auctionID);
        }
    }

    $conn->close();
    ?>

</div>

<?php include_once("footer.php") ?>