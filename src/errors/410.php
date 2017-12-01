<?php
$title = "410";
$subtitle = "Gone";

$message = "The requested resource is no longer available at the server and no forwarding address is known. This condition is expected to be considered permanent.\n";

require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/error.php";
?>
