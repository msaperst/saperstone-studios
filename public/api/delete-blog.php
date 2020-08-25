<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $blog = new Blog($_POST['post']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
try {
    $blog->delete();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();