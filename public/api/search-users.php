<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new CurrentUser ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$keyword = "";
if (isset ($_GET ['keyword'])) {
    $keyword = $sql->escapeString($_GET ['keyword']);
}

$sql = "SELECT * FROM users WHERE `usr` COLLATE UTF8_GENERAL_CI LIKE '%$keyword%'";
$result = mysqli_query($conn->db, $sql);
$response = array();
while ($r = mysqli_fetch_assoc($result)) {
    $response [] = $r;
}
echo json_encode($response);

$conn->disconnect();
exit ();