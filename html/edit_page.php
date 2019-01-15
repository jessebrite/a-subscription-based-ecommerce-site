<?php 

// This page is used by an administrator to edit a selected page.

// Require the configuration before any PHP code as the configuration controls error reporting:
require ('./includes/config.inc.php');

// If the user isn't logged in as an administrator, redirect them:
redirect_invalid_user('user_admin');

// Include the header file:
$page_title = 'Edit Page';
include ('./includes/header.html');

// Require the database connection:
require_once __DIR__ . ('/includes/mysql.inc.php');
$stmt = $dbc->stmt_init();

// For storing errors:
$edit_page_errors = array();

// Validate the category ID:
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT, array('min_range' => 1))) {

	// Get the page info:
	$sql = 'SELECT id, category_id, title, description, content FROM pages WHERE id = ?';
	// $result = $dbc->query($sql);
	if ($stmt->prepare($sql)) {
		$stmt->bind_param('i', $_GET['id']); 
		// execute the query and fetch the result
        $OK = $stmt->execute();
        // bind the result to variables
        $stmt->bind_result($id, $category_id, $title, $description, $content);
        $stmt->fetch();
        // free the database resources for the second query
        $stmt->free_result();
        //  get categories associated with the article
        $sql = 'SELECT category_id FROM page2cat WHERE page_id = ?';
        if ($stmt->prepare($sql)) {
            $stmt->bind_param('i', $_GET['id']);
            $OK = $stmt->execute();
            $stmt->bind_result($category);
            // loop though the results to store them in an array
            $selected_categories = [];
            while ($stmt->fetch()) {
                $selected_categories[] = $category;
            }
        }
/*	if ($stmt->num_rows == 1) { // Problem!
			$page_title = 'Error!';
			// include ('./includes/header.html');
			echo '<p class="error">Sorry, this page does not exist.</p>';
			include ('./includes/footer.html');
			exit();
		}
*/
	}

	// Check for a form submission:
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {	
		
		// Check for a title:
		if (!empty($_POST['title'])) {
			$title = mysqli_real_escape_string($dbc, strip_tags($_POST['title']));
		} else {
			$edit_page_errors['title'] = 'Please enter the title!';
		}
		
		// Check for a category:
		if (filter_var($_POST['category'], FILTER_VALIDATE_INT, array('min_range' => 1))) {
			$category_id = $_POST['category'];
		} else { // No category selected.
			$edit_page_errors['category'] = 'Please select a category!';
		}

		// Check for a description:
		if (!empty($_POST['description'])) {
			$description = mysqli_real_escape_string($dbc, strip_tags($_POST['description']));
		} else {
			$edit_page_errors['description'] = 'Please enter the description!';
		}
			
		// Check for the content:
		if (!empty($_POST['content'])) {
			$allowed = '<div><p><span><br><a><img><h1><h2><h3><h4><ul><ol><li><blockquote>';
			$content = mysqli_real_escape_string($dbc, strip_tags($_POST['content'], $allowed));
		} else {
			$edit_page_errors['content'] = 'Please enter the content!';
		}
			
		if (empty($edit_page_errors)) { // If everything's OK.
		
			// Update the page in the database:
			$sql = "UPDATE  pages SET title = '$title', category_id = '$category_id', description = '$description', content = '$content' WHERE id = " . $_GET['id'];
			$result = mysqli_query ($dbc, $sql);

			if (mysqli_affected_rows($dbc) == 1) { // If it ran OK.
			
				// Print a message:
				echo '<h4>The page has been updated!</h4>';
				
				// Clear $_POST:
				$_POST = array();
				
				// Send an email to the administrator to let them know new content was updated?
				
			} else { // If it did not run OK.
				trigger_error('The page could not be updated due to a system error. We apologize for any inconvenience.');
			}
			
		} // End of $edit_page_errors IF.
	
	} // End of the main form submission conditional.

	// Need the form functions script, which defines create_form_input():
require ('includes/form_functions.inc.php');
if ($_GET['id'] == 0) { ?>
        <p class="error">Invalid request: record does not exist.</p>
    <?php } else { ?>
<h3>Update a Content Page of the site</h3>
<form action="" method="post" accept-charset="utf-8">

	<fieldset><legend>Edit the page:</legend>	
		<p><label for="title"><strong>Title: </strong><?php if (array_key_exists('title', $edit_page_errors)) echo ' <span class="error">' . $edit_page_errors['title'] . '</span>'; ?></label><br>
        <input name="title" type="text" id="title" value="<?= htmlentities($title); ?>"></p>	
	<p><label for="category"><strong>Category</strong></label><br>
	<select name="category"<?php if (array_key_exists('category', $edit_page_errors)) echo ' class="error"'; ?>>
	<option>Select One</option>
	<?php // Retrieve all the categories and add to the pull-down menu:
	$sql = "SELECT id, category FROM categories ORDER BY category ASC";		
	$result = $dbc->query($sql);
		while ($row = $result->fetch_assoc()) { ?>
                <option value="<?= $row['id']; ?>" <?php
                if (isset($selected_categories) && in_array($row['id'], $selected_categories)) {
                    echo 'selected';
                } ?>><?= $row['category']; ?></option>
            <?php } ?>
	</select><?php if (array_key_exists('category', $edit_page_errors)) echo ' <span class="error">' . $edit_page_errors['category'] . '</span>'; ?></p>
	
	<p><label for="description"><strong>Description: </strong><?php if (array_key_exists('description', $edit_page_errors)) echo ' <span class="error">' . $edit_page_errors['description'] . '</span>'; ?></label><br>
        <textarea name="description" id="description" cols="55" rows="5"><?= htmlentities($description); ?></textarea></p>
	
	<p><label for="content"><strong>Content: </strong><?php if (array_key_exists('content', $edit_page_errors)) echo ' <span class="error">' . $edit_page_errors['content'] . '</span>'; ?></label><br>
        <textarea name="content" id="content"><?= htmlentities($content); ?></textarea></p>
	
	<p><input type="submit" name="submit_button" value="Update this page" id="submit_button" class="formbutton"></p>	
	</fieldset>
</form>
<?php } ?>
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
<?php } else { // No valid ID.
	$page_title = 'Error!';
	echo '<p class="error">Sorry, this page does not exist.</p>';
} // End of primary IF.

// Include the HTML footer:
include ('./includes/footer.html');
?>