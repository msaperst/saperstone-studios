<?php
$title = "505";
$subtitle = "HTTP Version Not Supported";

$message = "Our server refuses to support the HTTP protocol version that was used in the request message.\n";

require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/error.php";
?>
