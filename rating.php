<?php include_once("header.php") ?>
<?php include_once("db_connection.php") ?>

<?php
// If user is not logged in or not a buyer, they should not be able to use this page.

if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] != 'Buyer') {
    header('Location: browse.php');
}

?>

<div class="container">
    <h1>Rate Item: </h1>
    <?php
    $auctionID = $_GET['auctionID'] ?? null;
    $userID = $_SESSION['userID'];

    $sql = "SELECT itemName, itemDescription
            FROM Items
            INNER JOIN Auctions USING (itemID)
            WHERE Auctions.auctionID = $auctionID";

    $result = $conn->query($sql);

    if ($result === false) {
        // Output error message
        echo "Error in query: " . $conn->error;
    } else {
        // Output data
        while ($row = $result->fetch_assoc()) {
            echo '</br><h2>' . $row['itemName'] . '</h2></br>';
            echo '<h5>' . $row['itemDescription'] . '</h5></br>';
        }
    }

    $conn->close();
    ?>

    <div style="max-width: 800px; margin: 10px auto">
        <div class="card">
            <div class="card-body">
                <form method="post" action="rating_result.php">

                    <!--------------------- Rating out of 5 ------------------------->

                    <div class="form-group row">
                        <label for="rating" class="col-sm-2 col-form-label text-right">Rating</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="rating" name="rating">
                                <option selected disabled>Choose...</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                            <small id="categoryHelp" class="form-text text-muted"><span class="text-danger">*
                                    Required.</span> Select a rating for this item.</small>
                        </div>
                    </div>
                    <!--------------------- Rating Comment ------------------------->

                    <div class="form-group row">
                        <label for="auctionDetails" class="col-sm-2 col-form-label text-right">Comment</label>
                        <div class="col-sm-10">
                            <input type="text" textarea class="form-control" id="auctionDetails" name="auctionDetails"
                                rows="4"></textarea>
                            <small id="detailsHelp" class="form-text text-muted">Comment to provide feedback to the
                                seller and help other buyers.</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary form-control">Submit Rating</button>
                </form>
            </div>
        </div>
    </div>

</div>

<?php include_once("footer.php") ?>