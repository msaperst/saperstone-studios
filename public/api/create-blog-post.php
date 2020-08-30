<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $blog = Blog::withParams($_POST);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    echo $blog->create();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit();