<?php include_once("header.php")?>

<?php
//(Uncomment this block to redirect people without selling privileges away from this page)
  // If user is not logged in or not a seller, they should not be able to
  // use this page.

  if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] != 'Seller') {
    header('Location: browse.php');
  }

?>

<div class="container">

<!-- Create auction form -->
<div style="max-width: 800px; margin: 10px auto">
  <h2 class="my-3">Create new auction</h2>
  <div class="card">
    <div class="card-body">
      <!-- Note: This form does not do any dynamic / client-side / 
      JavaScript-based validation of data. It only performs checking after 
      the form has been submitted, and only allows users to try once. You 
      can make this fancier using JavaScript to alert users of invalid data
      before they try to send it, but that kind of functionality should be
      extremely low-priority / only done after all database functions are
      complete. -->
      <form method="post" action="create_auction_result.php" enctype="multipart/form-data">

      <!--------------------- Auction Title ------------------------->

        <div class="form-group row">
          <label for="auctionTitle" class="col-sm-2 col-form-label text-right">Title of Auction</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="auctionTitle" name="auctionTitle" placeholder="e.g. Black mountain bike" >
            <small id="titleHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> A short description of the item you're selling, which will display in listings.</small>
          </div>
        </div>

      <!--------------------- Auction Details ------------------------->

        <div class="form-group row">
          <label for="auctionDetails" class="col-sm-2 col-form-label text-right">Details</label>
          <div class="col-sm-10">
            <input type="text" textarea class="form-control" id="auctionDetails" name="auctionDetails" rows="4"></textarea>
            <small id="detailsHelp" class="form-text text-muted">Full details of the listing to help bidders decide if it's what they're looking for.</small>
          </div>
        </div>

        <!--------------------- Auction Quantity ---------------------->

            <div class="form-group row">
          <label for="auctionQuantity" class="col-sm-2 col-form-label text-right">Quantity</label>
          <div class="col-sm-10">
            <input type="number" class="form-control" id="auctionQuantity" name="auctionQuantity" placeholder="0">
            <small id="quantityHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Quantity of the item you're selling</small>
          </div>
        </div>

      <!--------------------- Auction Category ------------------------->

        <div class="form-group row">
          <label for="auctionCategory" class="col-sm-2 col-form-label text-right">Category</label>
          <div class="col-sm-10">
            <select class="form-control" id="auctionCategory" name="auctionCategory">
              <option selected disabled>Choose...</option>
              <option value="Art and Collectibles">Art and Collectibles</option>
              <option value="Automotive and Vehicles">Automotive and Vehicles</option>
              <option value="Books, Movies and Music">Books, Movies and Music</option>
              <option value="Business and Industrial Equipment">Business and Industrial Equipment</option>
              <option value="Charity and Fundraising">Charity and Fundraising</option>
              <option value="Electronics and Gadgets">Electronics and Gadgets</option>
              <option value="Fashion and Accessories">Fashion and Accessories</option>
              <option value="Health and Beauty">Health and Beauty</option>
              <option value="Hobbies and Crafts">Hobbies and Crafts</option>
              <option value="Home and Garden">Home and Garden</option>
              <option value="Industrial and Scientific">Industrial and Scientific</option>
              <option value="Pet Supplies">Pet Supplies</option>
              <option value="Real Estate and Property">Real Estate and Property</option>
              <option value="Sports and Outdoors">Sports and Outdoors</option>
              <option value="Toys and Games">Toys and Games</option>
              <option value="Others">Others</option>
            </select>
            <small id="categoryHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Select a category for this item.</small>
          </div>
        </div>

      <!--------------------- Auction Start Price ------------------------->

        <div class="form-group row">
          <label for="auctionStartPrice" class="col-sm-2 col-form-label text-right">Starting Price</label>
          <div class="col-sm-10">
	        <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">£</span>
              </div>
              <input type="number" class="form-control" id="auctionStartPrice" name="auctionStartPrice">
            </div>
            <small id="startBidHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Initial bid amount.</small>
          </div>
        </div>
      
      <!--------------------- Auction Reserve Price ------------------------->

        <div class="form-group row">
          <label for="auctionReservePrice" class="col-sm-2 col-form-label text-right">Reserve price</label>
          <div class="col-sm-10">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">£</span>
              </div>
              <input type="number" class="form-control" id="auctionReservePrice" name="auctionReservePrice">
            </div>
            <small id="reservePriceHelp" class="form-text text-muted">Optional. Auctions that end below this price will not go through. This value is not displayed in the auction listing.</small>
          </div>
        </div>

      <!--------------------- Auction End Date ------------------------->

        <div class="form-group row">
          <label for="auctionEndDate" class="col-sm-2 col-form-label text-right">End date</label>
          <div class="col-sm-10">
            <input type="datetime-local" class="form-control" id="auctionEndDate" name="auctionEndDate">
            <small id="endDateHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Day for the auction to end.</small>
          </div>
        </div>

      <!--------------------- Auction Upload Photos -------------------->

      <div class="form-group row">
          <label for="uploadPhoto" class="col-sm-2 col-form-label text-right">Upload Photo</label>
          <div class="col-sm-10">
            <!-- <div class="input-group">
              <div class="input-group-prepend"> -->
              <!-- </div> -->
              <input type="file" class="form-control" id="uploadPhoto" name="uploadPhoto" accept="image/*">
            <!-- </div> -->
            <small id="uploadPhotoHelp" class="form-text text-muted"><span class="text-danger">* Required. </span> Upload a photo to display in your auction listing. Supported file types: JPG, JPEG, PNG</small>
          </div>
        </div>

        <button type="submit" class="btn btn-primary form-control">Create Auction</button>
      </form>
    </div>
  </div>
</div>

</div>


<?php include_once("footer.php")?>