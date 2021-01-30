<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

try {
    $blog = Blog::withId($_POST ['post']);
    $blog->update($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();