<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    if (!isset ($_POST['post'])) {
        throw new BadBlogException("Blog id is required");
    }
    $blog = Blog::withId($_POST['post']);
    $blog->delete();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();
