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
        echo "Product id is required";
    } elseif ($_POST ['id'] == "") {
        echo "Product id can not be blank";
    } else {
        echo "Some other product id error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$product_details = $sql->getRow( "SELECT * FROM product_types WHERE id = $id;" );
if (! $product_details ['id']) {
    echo "Product id does not match any products";
    $sql->disconnect ();
    exit ();
}

$sql->executeStatement( "DELETE FROM product_types WHERE id='$id';" );
$sql->executeStatement( "DELETE FROM products WHERE product_type='$id';" );
$sql->executeStatement( "DELETE FROM product_options WHERE product_type='$id';" );
$sql->disconnect ();
exit ();
?>