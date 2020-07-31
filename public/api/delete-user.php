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
    $sql->disconnect ();
    exit ();
}

if (isset ( $_POST ['id'] ) && $_POST ['id'] != "" ) {
    $id = ( int ) $_POST ['id'];
} else {
    if (! isset ( $_POST ['id'] )) {
        echo "User id is required";
    } elseif ($_POST ['id'] == "") {
        echo "User id can not be blank";
    } else {
        echo "Some other user id error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$users_details = $sql->getRow( "SELECT * FROM users WHERE id = $id;" );
if (! $users_details ['id']) {
    echo "User id does not match any users";
    $sql->disconnect ();
    exit ();
}

$sql->executeStatement( "DELETE FROM users WHERE id='$id';" );
$sql->disconnect ();
exit ();