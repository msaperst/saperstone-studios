<?php
require_once "../php/sql.php";

if (isset ( $_POST ['user'] )) {
    $user = $_POST ['user'];
} else {
    echo "User is not provided";
    exit ();
}

$sql = "DELETE FROM albums_for_users WHERE user = $user";
mysqli_query ( $db, $sql );

if( isset( $_POST['albums'] ) ) {
    foreach ( $_POST ['albums'] as $album ) {
        $sql = "INSERT INTO albums_for_users ( `user`, `album` ) VALUES ( '$user', '$album' );";
        mysqli_query ( $db, $sql );
    }
}