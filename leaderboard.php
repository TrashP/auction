<?php include_once("header.php") ?>
<?php include_once("db_connection.php"); ?>

<div class="container">
    <h2 class="my-3">Auction Leaderboard</h2>

    <?php
    $bidsQuery = $conn->prepare("SELECT firstName, lastName, SUM(Points) as pts
                                FROM Users
                                INNER JOIN Points USING (userID)
                                GROUP BY firstName, lastName
                                ORDER BY pts
  ");


    $bidsQuery->execute();
    $bidsResult = $bidsQuery->get_result();
    ?>

    <?php if ($bidsResult->num_rows > 0): ?>

        <table class="table">
            <thead>
                <tr>
                    <th scope="col">First Name</th>
                    <th scope="col">Last Name</th>
                    <th scope="col">Points</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($data = $bidsResult->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $data['firstName'] . "</td>";
                    echo "<td>" . $data['lastName'] . "</td>";
                    echo "<td>" . $data['pts'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <?php
    else:
        echo "<tr><td>No items have been sold yet.</td></tr>";
    endif;
    ?>
</div>