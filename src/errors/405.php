<?php
$title = "405";
$subtitle = "Method Not Allowed";

$message = "The method specified in the Request-Line is not allowed for the resource identified by the Request-URI.\n";

require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/error.php";
?>
