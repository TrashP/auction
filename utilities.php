<?php

// display_time_remaining:
// Helper function to help figure out what time to display
function display_time_remaining($interval)
{

  if ($interval->days == 0 && $interval->h == 0) {
    // Less than one hour remaining: print mins + seconds:
    $time_remaining = $interval->format('%im %Ss');
  } else if ($interval->days == 0) {
    // Less than one day remaining: print hrs + mins:
    $time_remaining = $interval->format('%hh %im');
  } else {
    // At least one day remaining: print days + hrs:
    $time_remaining = $interval->format('%ad %hh');
  }

  return $time_remaining;

}

// print_listing_li:
// This function prints an HTML <li> element containing an auction listing
function print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time, $auction_id, $rating = null)
{
  // Truncate long descriptions
  if (strlen($desc) > 250) {
    $desc_shortened = substr($desc, 0, 250) . '...';
  } else {
    $desc_shortened = $desc;
  }

  // Fix language of bid vs. bids
  if ($num_bids == 1) {
    $bid = ' bid';
  } else {
    $bid = ' bids';
  }

  // Calculate time to auction end
  $now = new DateTime();
  $auctionEndDate = $end_time;
  $timeDiff = max(0, $auctionEndDate->getTimestamp() - $now->getTimestamp());

  // Display user rating
  if ($rating == null) {
    $rating = "Seller Rating: N/A";
  } else {
    $rating = "Seller Rating: " . bcdiv($rating, 1, 1) . "/5";
  }

  // Print HTML
    echo '
    <li class="list-group-item d-flex justify-content-between">
        <div class="p-2 mr-5">
            <h5>
                <a href="listing.php?itemID=' . $item_id . '&auctionID=' . $auction_id . '">' . $title . '</a>
            </h5>
            ' . $desc_shortened . '<br>' . $rating . '
        </div>
        <div class="text-center text-nowrap">
            <span style="font-size: 1.5em">£' . number_format($price, 2) . '</span>
            <br>' . $num_bids . $bid . '<br>';

  if ($timeDiff === 0) {
      // Auction has ended
      echo '<td>Auction has Ended</td>';
  } else {
      // Pass remaining time to JavaScript for dynamic countdown
      echo '<td><span class="countdown" data-time="' . $timeDiff . '"></span></td>';
  }

  echo '
        </div>
    </li>';

}

function print_listing_rating($item_id, $title, $desc, $price, $auction_id, $rating = null)
{
  // Truncate long descriptions
  if (strlen($desc) > 250) {
    $desc_shortened = substr($desc, 0, 250) . '...';
  } else {
    $desc_shortened = $desc;
  }

  // Display user rating
  if ($rating == null) {
    $rating = "Seller Rating: N/A";
  } else {
    $rating = "Seller Rating: " . bcdiv($rating, 1, 1) . "/5";
  }

  // Print HTML
  echo ('
    <li class="list-group-item d-flex justify-content-between">
    <div class="p-2 mr-5"><h5><a href="listing.php?itemID=' . $item_id . '&auctionID=' . $auction_id . '">' . $title . '</a></h5>' . $desc_shortened . '</br>' . $rating . '</div>
    <div class="text-center text-nowrap"><span style="font-size: 1.5em">£' . number_format($price, 2) . '</span><br/>' . '<a href="rating.php?auctionID=' . $auction_id . '">Rate</a>' . '</br><a href="points.php?auctionID=' . $auction_id . '">Claim Points</a>' . '</div>
  </li>'
  );
}

function print_listing_seller($item_id, $title, $desc, $price, $auction_id)
{
  // Truncate long descriptions
  if (strlen($desc) > 250) {
    $desc_shortened = substr($desc, 0, 250) . '...';
  } else {
    $desc_shortened = $desc;
  }

  // Print HTML
  echo ('
    <li class="list-group-item d-flex justify-content-between">
    <div class="p-2 mr-5"><h5><a href="listing.php?itemID=' . $item_id . '&auctionID=' . $auction_id . '">' . $title . '</a></h5>' . $desc_shortened . '</br></div>
    <div class="text-center text-nowrap"><span style="font-size: 1.5em">£' . number_format($price, 2) . '</span>' . '</br><a href="points.php?auctionID=' . $auction_id . '">Claim Points</a>' . '</div>
  </li>'
  );
}

?>



<script>
  document.addEventListener("DOMContentLoaded", function () {
    initializeCountdowns();
  });

  /**
   * Initializes all countdown elements and starts their timers.
   */
  function initializeCountdowns() {
    const countdownElements = document.querySelectorAll(".countdown");

    countdownElements.forEach((element) => {
      const remainingTime = parseInt(element.getAttribute("data-time"), 10);

      if (!isNaN(remainingTime) && remainingTime > 0) {
        startCountdown(element, remainingTime);
      } else {
        element.textContent = "Auction has Ended";
      }
    });
  }

  /**
   * Starts the countdown for a given element.
   * @param {HTMLElement} element - The HTML element to update.
   * @param {number} remainingTime - The remaining time in seconds.
   */
  function startCountdown(element, remainingTime) {
    function updateCountdown() {
      if (remainingTime > 0) {
        const timeFormatted = formatTime(remainingTime);
        element.textContent = timeFormatted;

        remainingTime--;
        setTimeout(updateCountdown, 1000);
      } else {
        element.textContent = "Auction has Ended";
      }
    }

    updateCountdown();
  }

  /**
   * Formats a given time in seconds into days, hours, minutes, and seconds.
   * @param {number} seconds - The time in seconds to format.
   * @returns {string} - The formatted time string.
   */
  function formatTime(seconds) {
    const days = Math.floor(seconds / (60 * 60 * 24));
    const hours = Math.floor((seconds % (60 * 60 * 24)) / (60 * 60));
    const minutes = Math.floor((seconds % (60 * 60)) / 60);
    const secs = seconds % 60;

    return `${days}d ${hours}h ${minutes}m ${secs}s`;
  }
</script>