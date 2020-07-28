<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ($sql);

$id = "";
if (isset ( $_GET ['id'] ) && $_GET ['id'] != "") {
    $id = ( int ) $_GET ['id'];
} else {
    if (! isset ( $_GET ['id'] )) {
        echo "Album id is required!";
    } elseif ($_GET ['id'] == "") {
        echo "Album id cannot be blank!";
    } else {
        echo "Some other Album id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM albums WHERE id = $id;";
$album_info = $sql->getRow( $sql );
if (! $album_info ['id']) {
    echo "That ID doesn't match any albums";
    $conn->disconnect ();
    exit ();
}

// only admin users and uploader users who own the album can make updates
if (! ($user->isAdmin () || ($user->getRole () == "uploader" && $user->getId () == $album_info ['owner']))) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM albums WHERE id = $id;";
$result = mysqli_query ( $conn->db, $sql );
$r = mysqli_fetch_assoc ( $result );
$r ['date'] = substr ( $r ['date'], 0, 10 );
if ($r ['code'] == NULL) {
    $r ['code'] = "";
}
echo json_encode ( $r );

$conn->disconnect ();
exit ();