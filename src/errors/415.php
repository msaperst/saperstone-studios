<?php
$title = "415";
$subtitle = "Unsupported Media Type";

$message = "Our server is refusing to service your request because the entity of the request is in a format not supported by the requested resource for the requested method.\n";

require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/error.php";
?>
