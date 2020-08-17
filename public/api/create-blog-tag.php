<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

$tag = $api->retrievePostString('tag', 'Blog tag');
if (is_array($tag)) {
    echo $tag['error'];
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