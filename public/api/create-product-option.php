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

if (isset ( $_POST ['type'] ) && $_POST ['type'] != "") {
    $type = ( int ) $_POST ['type'];
} else {
    if (! isset ( $_POST ['type'] )) {
        echo "Product type is required";
    } elseif ($_POST ['type'] == "") {
        echo "Product type can not be blank";
    } else {
        echo "Some other product type error occurred";
    }
    $sql->disconnect ();
    exit ();
}

if (isset ( $_POST ['option'] ) && $_POST ['option'] != "") {
    $option = $sql->escapeString( $_POST ['option'] );
} else {
    if (! isset ( $_POST ['option'] )) {
        echo "Product option is required";
    } elseif ($_POST ['option'] == "") {
        echo "Product option can not be blank";
    } else {
        echo "Some other product option error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$sql->executeStatement( "INSERT INTO `product_options` (`product_type`, `opt`) VALUES ('$type', '$option');" );
$sql->disconnect ();
exit ();