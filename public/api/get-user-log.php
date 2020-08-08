<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

if (isset ($_GET ['id'])) {
    $id = ( int )$_GET ['id'];
} else {
    echo "ID is not provided";
    $conn->disconnect();
    exit ();
}

$sql = "SELECT user_logs.*, albums.name FROM user_logs LEFT JOIN albums ON user_logs.album = albums.id WHERE user = $id";
$result = mysqli_query($conn->db, $sql);
$actions = array();
while ($row = mysqli_fetch_assoc($result)) {
    $actions [] = $row;
}
echo json_encode($actions);

$conn->disconnect();
exit ();