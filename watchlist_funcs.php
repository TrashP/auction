<?php include_once("header.php") ?>
 
<?php
  if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] != 'Seller') {
    header('Location: browse.php');
  }
?>

<?php

require 'db_connection.php';;

if (!isset($_POST['functionname']) || !isset($_POST['arguments'])) {
  return;
}

// Extract arguments from the POST variables:
$item_id = $_POST['arguments'];

if ($_POST['functionname'] == "add_to_watchlist") {
  // TODO: Update database and return success/failure.

  $res = "success";
}
else if ($_POST['functionname'] == "remove_from_watchlist") {
  // TODO: Update database and return success/failure.

  $res = "success";
}

// Note: Echoing from this PHP function will return the value as a string.
// If multiple echo's in this file exist, they will concatenate together,
// so be careful. You can also return JSON objects (in string form) using
// echo json_encode($res).
echo $res;

?>

<?php include_once("footer.php")?>