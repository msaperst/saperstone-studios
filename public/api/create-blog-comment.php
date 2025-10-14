<?php
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

try {
    $comment = Comment::withParams($_POST);
    echo $comment->create();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
exit ();