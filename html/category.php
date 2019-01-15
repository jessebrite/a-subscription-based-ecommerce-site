<?php

// This page displays the articles listed within a given category.
// This script is created in Chapter 5.

// Require the configuration before any PHP code as the configuration controls error reporting:
require_once ('./includes/config.inc.php');
// The config file also starts the session.

// Require the database connection:
require_once __DIR__ . ('/includes/mysql.inc.php');

// Validate the category ID:
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT, array('min_range' => 1))) {

	// Get the category title:
	$sql = 'SELECT category FROM categories WHERE id = ' . $_GET['id'];
	$result = $dbc->query($sql); // Use prepared statement instead
	if ($result->num_rows != 1) { // Problem!
		$page_title = 'Error!';
		include ('./includes/header.html');
		echo '<p class="error">Sorry, this page does not exist.</p>';
		include ('./includes/footer.html');
		exit();
	}
	
	// Fetch the category title and use it as the page title:
	list ($page_title) = (int) $result->fetch_array();
	include ('./includes/header.html');
	echo "<h3>$page_title</h3>";
	
	// Print a message if they're not an active user:
	// Change the message based upon the user's status:
	if (isset($_SESSION['user_admin'])) {
		// Admin area
	} elseif (isset($_SESSION['user_id']) && !isset($_SESSION['user_not_expired'])) {
		echo '<p class="error">Thank you for your interest in this content. Unfortunately your account has expired. Please <a href="renew.php">renew your account</a> in order to access site content.</p>';
	} elseif (!isset($_SESSION['user_id'])) {
		echo '<p class="error">Thank you for your interest in this content. You must be logged in as a registered user to view site content.</p>';
	}

	// Paginate the pages
	$page_no = $_GET['id'];
	$no_of_records_per_page = 1;
	$offset = ($page_no - 1) * $no_of_records_per_page;
	// Get the number of total pages
	$sql = 'SELECT COUNT(*) FROM categories';
	$result = $dbc->query($sql);
	$total_rows = $result->fetch_array()[0];
	$total_pages = ceil($total_rows / $no_of_records_per_page);
	// Get the pages associated with this category:
	$query = 'SELECT id, title, description FROM pages WHERE category_id = ' . $_GET['id'] . ' ORDER BY date_created DESC LIMIT 5';
	######################################
	#  Use prepared statement in future  #
	######################################
	 $result = $dbc->query($query);
		if ($result->num_rows > 0) { // Pages available!
			
			// Fetch each record:
			while ($row = $result->fetch_assoc()) {

				// Display each record:
				echo "<div><h4><a href=\"page.php?id={$row['id']}\">{$row['title']}</a></h4><p>{$row['description']}</p></div>\n";

			} // End of WHILE loop.
			
		} else { // No pages available.
			echo '<p>There are currently no pages of content associated with this category. Please check back again!</p>';
		}

	} else { // No valid ID.
		$page_title = 'Error!';
		include ('./includes/header.html');
		echo '<p class="error">Sorry, this page does not exist.</p>';
	} // End of primary IF.


	?>
<!-- Page pagination link >
	<ul class="pagination">
	    <li><a href="page.php?id=1">First</a></li>
	    <li class="<?php if($page_no <= 1){ echo 'disabled'; } ?>">
	        <a href="page.php<?php if($page_no <= 1){ echo '#'; } else { echo "?id=".($page_no - 1); } ?>">Prev</a>
	    </li>
	    <li class="<?php if($page_no >= $total_pages){ echo 'disabled'; } ?>">
	        <a href="page.php<?php if($page_no >= $total_pages){ echo '#'; } else { echo "?id=".($page_no + 1); } ?>">Next</a>
	    </li>
	    <li><a href="page.php?id=<?php echo $total_pages; ?>">Last</a></li>
	</ul>
-->
<?php


// } // End of supposed prepared statement

// Include the HTML footer:
include ('./includes/footer.html');
?>