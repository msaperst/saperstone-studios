<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";

$sql = new Sql ();

$user = new User ($sql);

$user_id;
if (! $user->isLoggedIn ()) {
    $user_id = getClientIP();
} else {
    $user_id = $user->getId ();
}

$album = "";
if (isset ( $_POST ['album'] ) && $_POST ['album'] != "") {
    $album = ( int ) $_POST ['album'];
} else {
    if (! isset ( $_POST ['album'] )) {
        echo "Album id is required!";
    } elseif ($_POST ['album'] == "") {
        echo "Album id cannot be blank!";
    } else {
        echo "Some other Album id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM albums WHERE id = $album;";
$album_info = $sql->getRow( $sql );
if (! $album_info ['id']) {
    echo "That ID doesn't match any albums";
    $conn->disconnect ();
    exit ();
}

$email = "";
if (isset ( $_POST ['email'] ) && $_POST ['email'] != "") {
    $email = $sql->escapeString( $_POST ['email'] );
} else {
    if (! isset ( $_POST ['email'] )) {
        echo "Email is required!";
    } elseif ($_POST ['email'] == "") {
        echo "Email cannot be blank!";
    } else {
        echo "Some other email error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

// update our mysql database
$sql = "INSERT INTO `notification_emails` (`album`, `user`, `email`) VALUES ('$album', '$user_id', '$email');";
mysqli_query ( $conn->db, $sql );

$conn->disconnect ();
exit ();