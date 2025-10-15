<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api();

try {
    $blog = Blog::withId($api->retrieveGetString('post', 'Blog id'));
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
echo json_encode($blog->getDataArray());
exit ();
