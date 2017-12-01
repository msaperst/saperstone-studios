<?php
$title = "403";
$subtitle = "Forbidden";

$message = "We understood your request, but cannot fulfill it. Authorization will not help and your request SHOULD NOT be repeated.\n";

require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/error.php";
?>
