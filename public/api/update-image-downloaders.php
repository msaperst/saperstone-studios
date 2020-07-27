<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['album'] )) {
    $album = $sql->escapeString( $_POST ['album'] );
} else {
    echo "Album is not provided";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['image'] )) {
    $image = $sql->escapeString( $_POST ['image'] );
} else {
    echo "Image is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "DELETE FROM `download_rights` WHERE `album` = '$album' AND `image` = '$image'";
mysqli_query ( $conn->db, $sql );

if (isset ( $_POST ['users'] )) {
    foreach ( $_POST ['users'] as $user ) {
        $user = $sql->escapeString( $user );
        $sql = "INSERT INTO `download_rights` ( `user`, `album`, `image` ) VALUES ( '$user', '$album', '$image' );";
        mysqli_query ( $conn->db, $sql );
    }
}

$conn->disconnect ();
exit ();
