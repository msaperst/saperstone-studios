<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $gallery = Gallery::withId($_POST ['id']);
    $gallery->update($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();