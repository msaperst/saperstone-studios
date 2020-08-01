<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$id = $api->retrievePostInt('id', 'User id');
if( is_array( $id ) ) {
    echo $id['error'];
    exit();
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