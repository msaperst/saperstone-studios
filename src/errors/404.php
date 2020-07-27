<?php
$title = "404";
$subtitle = "Not Found";

$message = "Looks like you got turned around. The server has not found anything matching the Request-URI.\n";

require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/error.php";
?>
