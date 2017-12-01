<?php
$title = "500";
$subtitle = "Internal Server Error";

$message = "Our server encountered an unexpected condition which prevented it from fulfilling your request.\n";

require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/error.php";
?>
