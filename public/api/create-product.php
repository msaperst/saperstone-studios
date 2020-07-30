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

if (isset ( $_POST ['category'] ) && $_POST ['category'] != "") {
    $category = $sql->escapeString( $_POST ['category'] );
    // check for valid categories
    $enums = $sql->getEnumValues( 'product_types', 'category' );
    if (! in_array( $category, $enums ) ) {
        echo "Product category is not valid";
        $sql->disconnect ();
        exit ();
    }
} else {
    if (! isset ( $_POST ['category'] )) {
        echo "Product category is required";
    } elseif ($_POST ['category'] == "") {
        echo "Product category can not be blank";
    } else {
        echo "Some other product category error occurred";
    }
    $sql->disconnect ();
    exit ();
}

if (isset ( $_POST ['name'] ) && $_POST ['name'] != "") {
    $name = $sql->escapeString( $_POST ['name'] );
} else {
    if (! isset ( $_POST ['name'] )) {
        echo "Product name is required";
    } elseif ($_POST ['name'] == "") {
        echo "Product name can not be blank";
    } else {
        echo "Some other product name error occurred";
    }
    $sql->disconnect ();
    exit ();
}

echo $sql->executeStatement( "INSERT INTO `product_types` (`id`, `category`, `name`) VALUES (NULL, '$category', '$name');" );
$sql->disconnect ();
exit ();
?>