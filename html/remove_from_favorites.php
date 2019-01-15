<?php
################################################################
#	The DELETE command has been disabled for DB user account.  #
#	To be able to delete, he command has to be enabled first.  #
################################################################

// This page removes a page of content from the list of the user's favorites.

// Require the configuration before any PHP code as the configuration controls error reporting:
require ('./includes/config.inc.php');
// The config file also starts the session.

// If the user isn't active, redirect them:
redirect_invalid_user('user_not_expired');

// Require the database connection:
require_once __DIR__ . ('/includes/mysql.inc.php');

// Validate the category ID:
if (filter_var($_GET['id'], FILTER_VALIDATE_INT, array('min_range' => 1))) {

	// Get the page info:
	$sql = 'SELECT title, description, content FROM pages WHERE id = ' . $_GET['id'];
	$result = mysqli_query($dbc, $sql);
	if (mysqli_num_rows($result) != 1) { // Problem!
		$page_title = 'Error!';
		include ('./includes/header.html');
		echo '<p class="error">Sorry, there was an error. Please try again later.</p>';
		include ('./includes/footer.html');
		exit();
	}
	
	// Fetch the page info:
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	$page_title = $row['title'];
	include ('includes/header.html');
	echo "<h3>$page_title</h3>";
	
	// Remove this favorite from the database:
	$sql = 'DELETE FROM favorite_pages WHERE user_id = ' . $_SESSION['user_id'] . ' AND page_id = ' . $_GET['id'] . ' LIMIT 1';
	$result = mysqli_query($dbc, $sql);
	if (mysqli_affected_rows($dbc) == 1) {
		echo '<p>This page has been removed from your favorites! <img src="./images/cross_48.png" border="0" width="48" height="48" align="middle" /></p>';
	} else {
		trigger_error('A system error occurred. We apologize for any inconvenience.');
	}

	// Show the page content:
	echo "<div>{$row['content']}</div>";
		
} else { // No valid ID.
	$page_title = 'Error!';
	include ('includes/header.html');
	echo '<p class="error">This page has been accessed in error.</p>';
} // End of primary IF.

// Include the HTML footer:
include ('./includes/footer.html');
?>