<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);
$sql->disconnect();

$api->forceAdmin();

try {
    $gallery = new Gallery($_POST['gallery']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    $image = new Image($gallery, $_POST['image']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$image->delete();
exit ();