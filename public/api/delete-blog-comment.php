<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceLoggedIn();

try {
    $comment = new Comment($_POST['comment']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}


// check our user permissions
if (!$comment->canUserGetData()) {
    header('HTTP/1.0 403 Unauthorized');
    exit ();
}

try {
$comment->delete();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();