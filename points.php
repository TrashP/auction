<?php include_once("header.php") ?>
<?php include_once("db_connection.php"); ?>

<div class="container my-5">
    <?php
    include_once 'db_connection.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Start the session only if it hasn't been started already
    }

    $userID = $_SESSION['userID'];

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $auctionID = $_GET['auctionID'];

        // Check if user already received points for this auction item
        $stmt = $conn->prepare("SELECT EXISTS(
                                SELECT 1
                                FROM Points
                                WHERE userID = ? AND auctionID = ?);");

        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("ii", $userID, $auctionID);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->fetch_array();

        if ($exists[0]) {
            echo "<h5>You already received points for this auction!</h5></br>";
        } else {
            // Insert row in Points table
            $stmt = $conn->prepare("SELECT MAX(bidAmountGBP) AS currentPrice
                                    FROM Bids
                                    WHERE auctionID = ?");

            if ($stmt === false) {
                die("Error preparing statement: " . $conn->error);
            }

            $stmt->bind_param("i", $auctionID);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $price = $row['currentPrice'];
            $points = floor($price / 100);

            $stmt = $conn->prepare("INSERT INTO Points (userID, auctionID, points)
                                    VALUES (?, ?, ?)");

            if ($stmt === false) {
                die("Error preparing statement: " . $conn->error);
            }

            $stmt->bind_param("iii", $userID, $auctionID, $points);
            if ($stmt->execute()) {
                echo "<h5>Data inserted successfully!</h5></br>";
            } else {
                echo "Error inserting data: " . $stmt->error . "</br>";
            }
        }
        echo "Click  <a href='browse.php'>here</a> to go back to Browse.</br>";
    }

    $conn->close();
    ?>

</div>

<?php include_once("footer.php") ?>