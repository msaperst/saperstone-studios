<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

try {
    $blog = Blog::withId($api->retrievePostString('post', 'Blog id'));
    $blog->update($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();
