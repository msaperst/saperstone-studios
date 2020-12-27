<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $tag = $api->retrievePostString('tag', 'Blog tag');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
$row = $sql->getRow("SELECT * FROM `tags` WHERE `tag` = '$tag';");
if ($row ['id']) {
    echo "Blog tag already exists";
    $sql->disconnect();
    exit ();
}

$last_id = $sql->executeStatement("INSERT INTO tags ( tag ) VALUES ('$tag');");
echo $last_id;
$sql->disconnect();
exit ();