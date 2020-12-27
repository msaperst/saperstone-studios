<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $blog = Blog::withId($_POST['post']);
    $blog->delete();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();