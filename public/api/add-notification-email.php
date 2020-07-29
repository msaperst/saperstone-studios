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
        echo "Album id is required";
    } elseif ($_POST ['album'] == "") {
        echo "Album id can not be blank";
    } else {
        echo "Some other album id error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$album_info = $sql->getRow( "SELECT * FROM albums WHERE id = $album;" );
if (! $album_info ['id']) {
    echo "Album id does not match any albums";
    $sql->disconnect ();
    exit ();
}

$email = "";
if (isset ( $_POST ['email'] ) && $_POST ['email'] != "") {
    $email = $sql->escapeString( $_POST ['email'] );
} else {
    if (! isset ( $_POST ['email'] )) {
        echo "Email is required";
    } elseif ($_POST ['email'] == "") {
        echo "Email can not be blank";
    } else {
        echo "Some other email error occurred";
    }
    $sql->disconnect ();
    exit ();
}

// update our mysql database
$sql->executeStatement( "INSERT INTO `notification_emails` (`album`, `user`, `email`) VALUES ('$album', '$user_id', '$email');" );
$sql->disconnect ();
exit ();
?>