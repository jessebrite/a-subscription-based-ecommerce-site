<?php
// Suppresing warning and notice messages
error_reporting(E_ALL ^ E_WARNING | E_NOTICE); // This seems not to be working though

// This page is used by an administrator to add a PDF to the site.

// Require the configuration before any PHP code as the configuration controls error reporting:
require ('./includes/config.inc.php');

// Initialize the missing fields to an empty array
$missing = [];
// Initialize OK and set if to false
// If the all validation is passed, it will be set to true
$OK = false;

// If the user isn't logged in as an administrator, redirect them:
redirect_invalid_user('user_admin');

// Include the header file:
$page_title = 'Add a PDF';
include ('./includes/header.html');

// Require the database connection:
require_once __DIR__ . ('/includes/mysql.inc.php');

// For storing errors:
$add_pdf_errors = array();

// Maximum file size
$max = 1048576;

// Check for a form submission:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {	
	// Only fields
	$expected = ['title', 'description', 'pdf'];
	// required fields
	$required = ['title', 'description', 'pdf'];
	require ('./includes/process_form.php');

	
	// Check for a PDF:
	if (is_uploaded_file ($_FILES['pdf']['tmp_name']) && ($_FILES['pdf']['error'] == UPLOAD_ERR_OK)) {
		
		$file = $_FILES['pdf'];
		
		$size = ROUND($file['size']/1024);

		// Validate the file size:
		if ($size > 1024) {
			$add_pdf_errors['pdf'] = 'The uploaded file was too large.';
		} 

		// Validate the file type:
		if ( ($file['type'] != 'application/pdf') && (substr($file['name'], -4) != '.pdf') ) {
			$add_pdf_errors['pdf'] = 'The uploaded file was not a PDF.';
		} 
		
		// Move the file over, if no problems:
		if (!array_key_exists('pdf', $add_pdf_errors)) {

			// Create a tmp_name for the file:
			$tmp_name = sha1($file['name'] . uniqid('',true));
			
			// Move the file to its proper folder but add _tmp, just in case:
			$destination =  PDFS_DIR . $tmp_name . '_tmp';

			if (move_uploaded_file($file['tmp_name'], $destination)) {
				
				// Store the data in the session for later use:
				$_SESSION['pdf']['tmp_name'] = $tmp_name;
				$_SESSION['pdf']['size'] = $size;
				$_SESSION['pdf']['file_name'] = $file['name'];
				
				// Print a message:
				echo '<h4>The file has been uploaded!</h4>';
				
			} else {
				trigger_error('The file could not be moved.');
				unlink ($file['tmp_name']);				
			}

		} // End of array_key_exists() IF.
		#####################################################################
		# Files that are very large (30Mb>=) ougth to be handled differently#
		# 		Yet to come up with a better idea 							#
		#####################################################################
		
	} elseif (!isset($_SESSION['pdf'])) { // No current or previous uploaded file.
		switch ($_FILES['pdf']['error']) {
			case 1:
			case 2:
				$add_pdf_errors['pdf'] = 'The uploaded file was too large.';
				break;
			case 3:
				$add_pdf_errors['pdf'] = 'The file was only partially uploaded.';
				break;
			case 6:
			case 7:
			case 8:
				$add_pdf_errors['pdf'] = 'The file could not be uploaded due to a system error.';
				break;
			case 4:
			default: 
				$add_pdf_errors['pdf'] = 'No file was uploaded.';
				break;
		} // End of SWITCH.

	} // End of $_FILES IF-ELSEIF-ELSE.
	if (!$missing) {
		$OK = true;
	}
	
	if (empty($add_pdf_errors) && $OK) { // If everything's OK.
		
		// Add the PDF to the database:
		$file_name = mysqli_real_escape_string($dbc, $_SESSION['pdf']['file_name']);
		$tmp_name = mysqli_real_escape_string($dbc, $_SESSION['pdf']['tmp_name']);
		$size = (int) $_SESSION['pdf']['size'];
		$sql = "INSERT INTO pdfs (tmp_name, title, description, file_name, size) VALUES ('$tmp_name', '$title', '$description', '$file_name', $size)";
		#######################################
		#	Use prepared statement instead    #
		#######################################
		$result = mysqli_query ($dbc, $sql);
		if (mysqli_affected_rows($dbc) == 1) { // If it ran OK.
			
			// Rename the temporary file:
			$original =  PDFS_DIR . $tmp_name . '_tmp';
			$destination =  PDFS_DIR . $tmp_name;
			rename($original, $destination);

			// Print a message:
			echo '<h4>The PDF has been added!</h4>';
		
			// Clear $_POST:
			$_POST = array();
			
			// Clear $_FILES:
			$_FILES = array();
			
			// Clear $file and $_SESSION['pdf']:
			unset($file, $_SESSION['pdf']);
					
		} else { // If it did not run OK.
			trigger_error('The PDF could not be added due to a system error. We apologize for any inconvenience.');
			unlink ($destination);
		}
				
	} // End of $errors IF.
	
} else { // Clear out the session on a GET request:
	unset($_SESSION['pdf']);	
} // End of the submission IF.

// Need the form functions script, which defines create_form_input():
require ('includes/form_functions.inc.php');
?><h3>Add a PDF</h3><?php if ($missing || $add_pdf_errors) { ?>
            <p class="error">Please fix the item(s) indicated below:</p><?php } ?>
<form enctype="multipart/form-data" action="add_pdf.php" method="post" accept-charset="utf-8">

	<input type="hidden" name="MAX_FILE_SIZE" value="<?= $max; ?>" />
	
	<fieldset><legend>Fill out the form to add a PDF to the site:</legend>
			<p><label for="title"><strong>Title: </strong><?php if ($missing && in_array('title', $missing)) { ?>
<span class="error">Please enter the title</span>
<?php } ?></label><br /><?php create_form_input('title', 'text', $missing); ?></p>
			<p><label for="description"><strong>Description: </strong><?php if ($missing && in_array('description', $missing)) { ?>
<span class="error">Please enter your name</span>
<?php } ?></label><br /><?php create_form_input('description', 'textarea', $missing); ?></p>
		<p><label for="pdf"><strong>PDF</strong></label><br /><?php echo '<input type="file" name="pdf" id="pdf"';
		
			// Check for an error:
			if (array_key_exists('pdf', $add_pdf_errors)) {
				
				echo ' class="error" /> <span class="error">' . $add_pdf_errors['pdf'] . '</span>';
				
			} else { // No error.

				echo ' />';

				// If the file exists (from a previous form submission but there were other errors),
				// store the file info in a session and note its existence:		
				if (isset($_SESSION['pdf'])) {
					echo " Currently '{$_SESSION['pdf']['file_name']}'";
				}

			} // end of errors IF-ELSE.
		 ?><small>PDF only, 1MB Limit</small></p>

	<p><input type="submit" name="submit_button" value="Add This PDF" id="submit_button" class="formbutton" /></p>
	
	
	</fieldset>

</form> 

<?php // Include the HTML footer:
include ('./includes/footer.html');
?>