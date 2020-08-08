<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
new Session();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

if (isset ( $_POST ['album'] )) {
    $album = ( int ) $_POST ['album'];
} else {
    echo "Album is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "DELETE FROM albums_for_users WHERE album = $album";
mysqli_query ( $conn->db, $sql );

if (isset ( $_POST ['users'] )) {
    foreach ( $_POST ['users'] as $user ) {
        $sql = "INSERT INTO albums_for_users ( `user`, `album` ) VALUES ( '$user', '$album' );";
        mysqli_query ( $conn->db, $sql );
    }
}

$conn->disconnect ();
exit ();