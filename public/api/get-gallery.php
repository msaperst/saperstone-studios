<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

try {
    $gallery = new Gallery($_GET['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

//TODO - update this once Image class is completed
$sql = new Sql();
echo json_encode($sql->getRow("SELECT title FROM galleries WHERE galleries.id = {$gallery->getId()}"));
$sql->disconnect();
exit ();