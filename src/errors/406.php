<?php
$title = "406";
$subtitle = "Not Acceptable";

$message = "The resource identified by your request is only capable of generating response entities which have content characteristics not acceptable according to the accept headers sent in the request.\n";

require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "templates/error.php";
?>
