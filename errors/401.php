<?php
if (session_status () != PHP_SESSION_ACTIVE) {
    session_name ( 'ssLogin' );
    // Starting the session
    
    session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
    // Making the cookie live for 2 weeks
    
    session_start ();
    // Start our session
}

include_once "../php/user.php"; $user = new user();

$title = "401";
$subtitle = "Unauthorized";

$message = "Your request requires user authentication.<br/>\n";
if (! $user->isLoggedIn ()) {
    $message .= "Please <a href='javascript:void(0);' data-toggle='modal' data-target='#login-modal'>Login</a> to access that page.<br/>\n";
} else {
    $message .= "Despite being logged in, your credentials do not give you access to this section of the site.<br/>\n";
}

require ('template.php');
?>
