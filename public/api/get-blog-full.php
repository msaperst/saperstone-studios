<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);
$sql->disconnect();

try {
    $blog = new Blog($_GET['post']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

echo json_encode($blog->getDataArray());
exit ();