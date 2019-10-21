<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$conn = new Sql ();
$conn->connect ();

$user = new User ();

if (isset ( $_GET ['code'] ) && $_GET ['code'] != "") {
    $code = mysqli_real_escape_string ( $conn->db, $_GET ['code'] );
} else {
    if (! isset ( $_GET ['code'] )) {
        echo "Album code is required!";
    } elseif ($_GET ['code'] == "") {
        echo "Album code cannot be blank!";
    } else {
        echo "Some other album code error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM albums WHERE code = '$code';";
$r = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if ($r ['id']) {
    echo $r ['id'];
    $_SESSION ["searched"] [$r ['id']] = 1;
} else {
    echo "That code doesn't match any albums";
    $conn->disconnect ();
    exit ();
}

if ($user->isLoggedIn() && isset ( $_GET ['albumAdd'] ) && $_GET ['albumAdd'] == 1) {
    $sql = "SELECT * FROM albums_for_users WHERE user = '" . $user->getId () . "' AND album = '" . $r ['id'] . "';";
    $s = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
    if (! $s ['user']) {
        $sql = "INSERT INTO albums_for_users ( `user`, `album` ) VALUES ( '" . $user->getId () . "', '" . $r ['id'] . "' );";
        mysqli_query ( $conn->db, $sql );
    }
}

$conn->disconnect ();
exit ();
