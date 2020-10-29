<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
    $comment = Comment::withParams($_POST);
    echo $comment->create();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();