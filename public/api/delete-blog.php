<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new CurrentUser ($sql);
$api = new Api ($sql, $user);
$sql->disconnect();

$api->forceAdmin();

try {
    $blog = new Blog($_POST['post']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$blog->delete();
exit ();