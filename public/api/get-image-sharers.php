<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ($sql);

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "You do not have appropriate rights to perform this action";
    }
    $conn->disconnect ();
    exit ();
}

if (isset ( $_GET ['album'] )) {
    $album = ( int ) $_GET ['album'];
} else {
    echo "Album is not provided";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_GET ['image'] )) {
    $image = ( int ) $_GET ['image'];
} else {
    echo "Image is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM `albums_for_users` LEFT JOIN `share_rights` ON `albums_for_users`.`user` = `share_rights`.`user` WHERE `albums_for_users`.`album` = '$album' AND ( `share_rights`.`album` = '$album' OR `share_rights`.`album` = '*' ) AND ( `share_rights`.`image` = '$image' OR `share_rights`.`image` = '*' );";
$result = mysqli_query ( $conn->db, $sql );
$response = array ();
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response [] = $r;
}
$sql = "SELECT * FROM `share_rights` WHERE `user` = '0' AND ( `album` = '$album' OR `album` = '*' ) AND ( `image` = '$image' OR `image` = '*' );";
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $response [] = $r;
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();