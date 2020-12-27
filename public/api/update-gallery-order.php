<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $gallery = Gallery::withId($_POST ['id']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql();
$imgs = $sql->getRowCount("SELECT * FROM gallery_images WHERE gallery = {$gallery->getId()}");
if (isset ($_POST ['imgs']) && is_array($_POST ['imgs']) && sizeof($_POST ['imgs']) == $imgs) {
    $imgs = $_POST ['imgs'];
} else {
    echo "Gallery images are not in the correct format";
    $sql->disconnect();
    exit ();
}

for ($x = 0; $x < sizeof($imgs); $x++) {
    $img = $imgs [$x];
    $sql->executeStatement("UPDATE gallery_images SET sequence=$x WHERE id='" . (int)$img ['id'] . "';");
}
$sql->disconnect();
exit ();