<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

try {
    $blog = new Blog($_GET['post']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

echo json_encode($blog->getDataArray());
exit ();