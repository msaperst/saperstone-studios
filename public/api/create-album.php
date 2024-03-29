<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$systemUser = User::fromSystem();

// ensure we are logged in appropriately
if (!$systemUser->isAdmin() && $systemUser->getRole() != "uploader") {
    header('HTTP/1.0 401 Unauthorized');
    if ($systemUser->isLoggedIn()) {
        echo "You do not have appropriate rights to perform this action";
    }
    exit ();
}

try {
    $album = Album::withParams($_POST);
    echo $album->create();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();