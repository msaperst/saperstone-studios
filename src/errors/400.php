<?php
$title = "400";
$subtitle = "Bad Request";

$message = "Your request could not be understood by the server due to malformed syntax. Please DO NOT repeat the request without modifications.\n";

require dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "templates/error.php";
?>
