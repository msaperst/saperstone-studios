<?php
$title = "408";
$title = "Request Timeout";

$message = "We could not produce this resource within the time limit that we were prepared to wait for. You may repeat the request without modifications at any later time.\n";

require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/error.php";
?>
