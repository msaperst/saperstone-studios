<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new CurrentUser ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$id = $api->retrieveGetInt('id', 'User id');
if (is_array($id)) {
    echo $id['error'];
    exit();
}
$user_info = $sql->getRow("SELECT * FROM users WHERE id = $id;");
if (!$user_info ['id']) {
    echo "User id does not match any users";
    $sql->disconnect();
    exit ();
}

echo json_encode($sql->getRows("SELECT user_logs.*, albums.name FROM user_logs LEFT JOIN albums ON user_logs.album = albums.id WHERE user = $id"));
$sql->disconnect();
exit ();