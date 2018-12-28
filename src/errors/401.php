<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$user = new User ();

$title = "401";
$subtitle = "Unauthorized";

$message = "Your request requires user authentication.<br/>\n";
if (! $user->isLoggedIn ()) {
    $message .= "Please <a href='javascript:void(0);' data-toggle='modal' data-target='#login-modal'>Login</a> to access that page.<br/>\n";
} else {
    $message .= "Despite being logged in, your credentials do not give you access to this section of the site.<br/>\n";
}

require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/error.php";
?>
