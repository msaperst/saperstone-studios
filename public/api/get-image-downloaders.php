<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

if (isset ($_GET ['album'])) {
    $album = ( int )$_GET ['album'];
} else {
    echo "Album is not provided";
    $conn->disconnect();
    exit ();
}

if (isset ($_GET ['image'])) {
    $image = ( int )$_GET ['image'];
} else {
    echo "Image is not provided";
    $conn->disconnect();
    exit ();
}

$sql = "SELECT * FROM `albums_for_users` LEFT JOIN `download_rights` ON `albums_for_users`.`user` = `download_rights`.`user` WHERE `albums_for_users`.`album` = '$album' AND ( `download_rights`.`album` = '$album' OR `download_rights`.`album` = '*' ) AND ( `download_rights`.`image` = '$image' OR `download_rights`.`image` = '*' );";
$result = mysqli_query($conn->db, $sql);
$response = array();
while ($r = mysqli_fetch_assoc($result)) {
    $response [] = $r;
}
$sql = "SELECT * FROM `download_rights` WHERE `user` = '0' AND ( `album` = '$album' OR `album` = '*' ) AND ( `image` = '$image' OR `image` = '*' );";
$result = mysqli_query($conn->db, $sql);
while ($r = mysqli_fetch_assoc($result)) {
    $response [] = $r;
}
echo json_encode($response);

$conn->disconnect();
exit ();