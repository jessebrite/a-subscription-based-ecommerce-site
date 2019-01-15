<?php
$live = false;
$contact_email = 'you@example.com';
// cpnstansts
define ('BASE_URI', 'http://htdocs/shared/ecommerce1/');
define ('BASE_URL', 'www.example.com/');
define ('MYSQL', 'http://htdocs/shared/ecommerce1/includes/connection.php');
// Start session
session_start();

function my_error_handler($e_number, $e_message, $e_file, $e_line, $e_vars) {
	global $live, $contact_email;
	$message = "An error occured in script '$e_file' on line $e_line: \n$e_message\n";
	$message .= "<pre>" . print_r(debug_backtrace(), 1) . "</pre>\n";
	// If the site isn't live, show the error message in the browser
	if (!$live) {
		echo '<div class="error">' . nl2br($message) . '</div>';
	} else {
		error_log($message, 1, $contact_email, 'From:admin@example.com');
		if ($e_number != E_NOTICE) {
			echo '<div class="error">A system error occured. We apologise for the inconvinience.</div>';
		}
	} // End of $live IF-ELSE
	return true;
} // End of my_error_handler() definition
set_error_handler('my_error_handler');