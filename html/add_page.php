<?php
// This page is used by an administrator to create a specific page of HTML content.

// Require the configuration before any PHP code as the configuration controls error reporting:
require ('./includes/config.inc.php');

// Initialize the missing fields to an empty array
$missing = [];
// Initialize OK and set if to false
// If the all validation is passed, it will be set to true
$OK = false;
// Initialize done
$done = false;

// If the user isn't logged in as an administrator, redirect them:
redirect_invalid_user('user_admin');

// Include the header file:
$page_title = 'Add a Site Content Page';
include ('./includes/header.html');

// Require the database connection:
require_once __DIR__ . ('/includes/mysql.inc.php');

// Initialze prepared statement
$stmt = $dbc->stmt_init();
// For storing errors:
$add_page_errors = array();

// Check for a form submission:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// Only fields
	$expected = ['title', 'category', 'description', 'content'];
	// required fields
	$required = ['title', 'category', 'description', 'content'];
	require ('./includes/process_form.php');
	
	// Check for a title:
	if (!empty($_POST['title'])) {
		$title = mysqli_real_escape_string($dbc, strip_tags($_POST['title']));
	} else {
		$add_page_errors['title'] = 'Please enter the title';
	}
	
	// Check for a category:
	if (filter_var($_POST['category'], FILTER_VALIDATE_INT, array('min_range' => 1))) {
		$category_id = $_POST['category'];
	} else { // No category selected.
		$add_page_errors['category'] = 'Please select a category';
	}

	// Check for a description:
	if (!empty($_POST['description'])) {
		$description = mysqli_real_escape_string($dbc, strip_tags($_POST['description']));
	} else {
		$add_page_errors['description'] = 'Please enter the description';
	}
		
	// Check for the content:
	if (!empty($_POST['content'])) {
		$allowed = '<div><p><span><br><a><img><h1><h2><h3><h4><ul><ol><li><blockquote>';
		$content = mysqli_real_escape_string($dbc, strip_tags($_POST['content'], $allowed));
	} else {
		$add_page_errors['content'] = 'Please enter the content';
	}

	if (!$missing) {
		$OK = true;
	}
			
	if (empty($add_page_errors) || $OK) { // If everything's OK.
	
		// Add the page to the database:
		$sql = "INSERT INTO pages (category_id, title, description, content) VALUES (?,?,?,?)";
		// Prepare statement
		if ($stmt->prepare($sql)) {
			$stmt->bind_param('isss', $category_id, $title, $description, $content);
			$stmt->execute();
		}

		if ($stmt->affected_rows > 0) { // If it ran OK.
			$done = true;
		
			// Print a message:
			echo '<h4>The page has been added!</h4>';
			
			// Clear $_POST:
			$_POST = array();
			
			// Send an email to the administrator to let them know new content was added?
			
		} else { // If it did not run OK.
			trigger_error('The page could not be added due to a system error. We apologize for any inconvenience.');
		}
		
	} // End of $add_page_errors IF.

    // if the page entry was inserted successfully, check for categories
    if ($done && isset($category_id)) {
        // get the page's primary key
        $page_id = mysqli_insert_id($dbc);
        // foreach ($cat as $cat_id) {
            if (is_numeric($category_id)) {
                $value = "($page_id,  " . (int) $category_id . ')';
            }
        // }
        if ($value) {
            $sql = 'INSERT INTO page2cat (page_id, category_id)
                    VALUES ' . ($value);
            // execute the query and get error message if it fails
            if (!$dbc->query($sql)) {
                $add_page_errors = $dbc->error;
            }
        }
    }

} // End of the main form submission conditional.

// Need the form functions script, which defines create_form_input():
require ('includes/form_functions.inc.php');
?>
<h3>Add a Site Content Page</h3><?php if ($missing || $add_page_errors) { ?>
            <p class="error">Please fix the item(s) indicated below:</p><?php } ?>
<form action="add_page.php" method="post" accept-charset="utf-8">

	<fieldset><legend>Fill out the form to add a page of content:</legend>
	
		<p><label for="title"><strong>Title: </strong><?php if ($missing && in_array('title', $missing)) { ?>
<span class="error">Please enter the title!</span>
<?php } ?></label><br /><?php create_form_input('title', 'text', $missing); ?></p>
	
	<p><label for="category"><strong>Category</strong></label><br />
	<select name="category">
	<option>Select One</option>
	<?php // Retrieve all the categories and add to the pull-down menu:
	// get categories
	#####################################################################
	#  When no item is selected from categories, it throws an exception #
	#  Instead of prompting the user to select Category                 #
	#  Work needs to be done on that                                    #
	#####################################################################
            $getCats = 'SELECT id, category FROM categories ORDER BY category';
            $category =  $dbc->query($getCats);
            while ($row = $category->fetch_assoc()) {
            ?>
            <option value="<?= $row['id']; ?>" <?php if (isset($_POST['category']) && ($_POST['category'] == $row['id']) ) echo ' selected="selected"';
             ?>><?= $row['category']; ?></option>
            <?php } ?>
	</select><?php if (array_key_exists('category', $add_page_errors)) echo ' <span class="error">' . $add_page_errors['category'] . '</span>'; ?></p>	
	<p><label for="description"><strong>Description: </strong><?php if ($missing && in_array('description', $missing)) { ?>
<span class="error">Please enter the description!</span>
<?php } ?></label><br /><?php create_form_input('description', 'textarea', $missing); ?></p>	
	<p><label for="content"><strong>Content: </strong><?php if ($missing && in_array('content', $missing)) { ?>
<span class="error">Please enter the content!</span>
<?php } ?></label><br /><?php create_form_input('content', 'textarea', $missing); ?></p>
	
	<p><input type="submit" name="submit_button" value="Add This Page" id="submit_button" class="formbutton" /></p>	
	</fieldset>
</form> 

<script type="text/javascript" src="./tiny_mce/tinymce.min.js"></script>
<script type="text/javascript">
	tinyMCE.init({
		// General options
		// selector: 'textarea',
		mode : "exact",
		elements : "content",
		theme : "modern",
		width : 800,
		height : 400,
		plugins : "advlist, autoresize, autosave, code, codesample, colorpicker, contextmenu, emoticons, fullscreen, link, spellchecker, textcolor, media, paste, preview, save, searchreplace, visualchars, wordcount",
		
		// Stripped down version of the  website CSS
		// content_css : "./css/styles.css",

	});
</script>
<!-- /TinyMCE -->

<?php /* PAGE CONTENT ENDS HERE! */

// Include the footer file to complete the template:
include ('./includes/footer.html');
?>