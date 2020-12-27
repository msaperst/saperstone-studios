<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
    $blog = Blog::withId($_GET['post']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
echo json_encode($blog->getDataArray());
exit ();