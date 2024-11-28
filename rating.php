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
                <form method="post"
                    action="<?php echo 'rating_result.php?userID=' . $userID . '&auctionID=' . $auctionID; ?>">

                    <!--------------------- Rating out of 5 ------------------------->

                    <div class="form-group row">
                        <label for="rating" class="col-sm-2 col-form-label text-right">Rating</label>
                        <div class="col-sm-10">
                            <select required class="form-control" id="rating" name="rating">
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
                        <label for="comment" class="col-sm-2 col-form-label text-right">Comment</label>
                        <div class="col-sm-10">
                            <input value="<?= isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : ''; ?>"
                                type="text" textarea class="form-control" id="comment" name="comment"
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

    <?php
    $error = $_GET['error'] ?? 'unknown_error';
    if ($error === "Rating is required.") {
        echo $error;
    }
    ?>

</div>

<?php include_once("footer.php") ?>