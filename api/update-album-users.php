<?php
require_once "../php/sql.php";

if (isset ( $_POST ['album'] )) {
    $album = $_POST ['album'];
} else {
    echo "Album is not provided";
    exit ();
}

$sql = "DELETE FROM albums_for_users WHERE album = $album";
mysqli_query ( $db, $sql );

if( isset( $_POST['users'] ) ) {
    foreach ( $_POST ['users'] as $user ) {
        $sql = "INSERT INTO albums_for_users ( `user`, `album` ) VALUES ( '$user', '$album' );";
        mysqli_query ( $db, $sql );
    }
}