<?php

// The user comes to this page after completing their PayPal transaction (in theory).
// Four lines are used in an earlier version but commented out later.

// Require the configuration before any PHP code as the configuration controls error reporting:
require ('./includes/config.inc.php');
// The config file also starts the session.

// If the user hasn't just registered, redirect them:
//redirect_invalid_user('reg_user_id');
// Above line commented out in later version of this script.

// Require the database connection:
require (MYSQL);

// Include the header file:
$page_title = 'Thanks!';
include ('./includes/header.html');

// Update the users table:
// $sql = "UPDATE users SET date_expires = ADDDATE(date_expires, INTERVAL 1 YEAR) WHERE 	id = {$_SESSION['reg_user_id']}";
// $result = mysqli_query ($dbc, $sql);
// Above lines commented out in later version of this script.

// Unset the session var:
//unset($_SESSION['reg_user_id']);
// Above line commented out in later version of this script.

?><h3>Thank You!</h3>
<p>Thank you for your payment! You may now access all of the site's content for the next year! <strong>Note: Your access to the site will automatically be renewed via PayPal each year. To disable this feature, or to cancel your account, see the "My preapproved purchases" section of your PayPal Profile page.</strong></p>


<script src="https://www.paypalobjects.com/api/checkout.js"></script>

<div id="paypal-button-container"></div>

<script>

    // Render the PayPal button

    paypal.Button.render({

        // Set your environment

        env: 'sandbox', // sandbox | production

        // Specify the style of the button

        style: {
            label: 'checkout',
            size:  'small',    // small | medium | large | responsive
            shape: 'pill',     // pill | rect
            color: 'gold'      // gold | blue | silver | black
        },

        // PayPal Client IDs - replace with your own
        // Create a PayPal app: https://developer.paypal.com/developer/applications/create

        client: {
            sandbox:    'AZDxjDScFpQtjWTOUtWKbyN_bDt4OgqaF4eYXlewfBP4-8aqX3PiV8e1GWU6liB2CUXlkA59kJXE7M6R',
            production: '<insert production client id>'
        },

        payment: function(data, actions) {
            return actions.payment.create({
                payment: {
                    transactions: [
                        {
                            amount: { total: '0.01', currency: 'USD' }
                        }
                    ]
                }
            });
        },

        onAuthorize: function(data, actions) {
            return actions.payment.execute().then(function() {
                window.alert('Payment Complete!');
            });
        }

    }, '#paypal-button-container');

</script>
    

<h3>Lorem Ipsum</h3>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent consectetur volutpat nunc, eget vulputate quam tristique sit amet. Donec suscipit mollis erat in egestas. Morbi id risus quam. Sed vitae erat eu tortor tempus consequat. Morbi quam massa, viverra sed ullamcorper sit amet, ultrices ullamcorper eros. Mauris ultricies rhoncus leo, ac vehicula sem condimentum vel. Morbi varius rutrum laoreet. Maecenas vitae turpis turpis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Fusce leo turpis, faucibus et consequat eget, adipiscing ut turpis. Donec lacinia sodales nulla nec pellentesque. Fusce fringilla dictum purus in imperdiet. Vivamus at nulla diam, sagittis rutrum diam. Integer porta imperdiet euismod.</p>

<?php // Include the HTML footer:
include ('./includes/footer.html');
?>