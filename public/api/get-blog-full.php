<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
    if (!isset ($_GET['post'])) {
        throw new BadBlogException("Blog id is required");
    }
    $blog = Blog::withId($_GET['post']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
echo json_encode($blog->getDataArray());
exit ();