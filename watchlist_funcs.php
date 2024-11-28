<?php
include_once("header.php");
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_watchlist'])) {
    $auctionID = $_POST['auctionID'];
    $userID = $_POST['userID'];
    $itemID = $_POST['itemID']; // Retrieve itemID

    // Validate that the item exists in the database
    $itemQuery = "SELECT itemID FROM Items WHERE itemID = ?";
    $itemStmt = $conn->prepare($itemQuery);
    $itemStmt->bind_param("i", $itemID);
    $itemStmt->execute();
    $itemResult = $itemStmt->get_result();

    if ($itemResult->num_rows === 0) {
        echo "<div class='alert alert-danger mt-3' role='alert'>Error: Item does not exist</div>";
        exit();
    }

    // Check if the item is already on the watchlist
    $checkQuery = "SELECT watching FROM Watchlist WHERE userID = ? AND auctionID = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ii", $userID, $auctionID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        // Toggle watching status
        $watching = $result->fetch_assoc()['watching'];
        $newStatus = !$watching;

        $updateQuery = "UPDATE Watchlist SET watching = ? WHERE userID = ? AND auctionID = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("iii", $newStatus, $userID, $auctionID);
        $updateStmt->execute();
    } else {
        // Add to watchlist if not exists
        $newStatus = true;
        $insertQuery = "INSERT INTO Watchlist (userID, auctionID, watching) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("iii", $userID, $auctionID, $newStatus);
        $insertStmt->execute();
    }

    // Redirect back to the listing page
    header("Location: listing.php?itemID=$itemID&auctionID=$auctionID");
    exit();
}

// Display watchlist for GET requests
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] == 'Seller') {
    header('Location: browse.php');
    exit();
}

// Fetch userID from session
$userID = $_SESSION['userID'];

// Query to fetch watchlisted items
$sql = "
    SELECT 
        Auctions.auctionID, 
        Items.itemName, 
        Items.itemDescription, 
        GREATEST(Auctions.startPriceGBP, IFNULL(MAX(Bids.bidAmountGBP), 0)) AS currentPrice,
        COUNT(Bids.bidID) AS numBids,
        Auctions.auctionDate
    FROM Watchlist
    INNER JOIN Auctions ON Watchlist.auctionID = Auctions.auctionID
    INNER JOIN Items ON Auctions.itemID = Items.itemID
    LEFT JOIN Bids ON Auctions.auctionID = Bids.auctionID
    WHERE Watchlist.userID = ? AND Watchlist.watching = TRUE
    GROUP BY Auctions.auctionID, Items.itemName, Items.itemDescription, Auctions.startPriceGBP, Auctions.auctionDate
    ORDER BY Auctions.auctionDate ASC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

// Display watchlist
echo "<div class='container'>";
if ($result->num_rows > 0) {
    echo "<h2 class='my-3'>My Watchlist</h2>";
    echo "<table class='table table-striped'>";
    echo "<thead><tr><th>Item Name</th><th>Description</th><th>Current Price (£)</th><th>Number of Bids</th><th>Auction End Date</th></tr></thead>";
    echo "<tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . htmlspecialchars($row['itemName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['itemDescription']) . "</td>";
        echo "<td>" . number_format($row['currentPrice'], 2) . "</td>";
        echo "<td>" . intval($row['numBids']) . "</td>";
        echo "<td>" . htmlspecialchars($row['auctionDate']) . "</td></tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<h2 class='my-3'>My Watchlist</h2><p>No items in your watchlist.</p>";
}
echo "</div>";

// Close statement and connection
$stmt->close();
$conn->close();
?>
