<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);
$sql->disconnect();

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

$comment->delete();
exit ();