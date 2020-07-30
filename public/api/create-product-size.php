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

if (isset ( $_POST ['size'] ) && $_POST ['size'] != "") {
    $size = $sql->escapeString( $_POST ['size'] );
} else {
    if (! isset ( $_POST ['size'] )) {
        echo "Product size is required";
    } elseif ($_POST ['size'] == "") {
        echo "Product size can not be blank";
    } else {
        echo "Some other product size error occurred";
    }
    $sql->disconnect ();
    exit ();
}

if (isset ( $_POST ['cost'] ) && $_POST ['cost'] != "") {
    $cost = floatval( str_replace( '$', '', $_POST ['cost'] ) );
} else {
    if (! isset ( $_POST ['cost'] )) {
        echo "Product cost is required";
    } elseif ($_POST ['cost'] == "") {
        echo "Product cost can not be blank";
    } else {
        echo "Some other product cost error occurred";
    }
    $sql->disconnect ();
    exit ();
}

if (isset ( $_POST ['price'] ) && $_POST ['price'] != "") {
    $price = floatval( str_replace( '$', '', $_POST ['price'] ) );
} else {
    if (! isset ( $_POST ['price'] )) {
        echo "Product price is required";
    } elseif ($_POST ['price'] == "") {
        echo "Product price can not be blank";
    } else {
        echo "Some other product price error occurred";
    }
    $sql->disconnect ();
    exit ();
}

echo $sql->executeStatement( "INSERT INTO `products` (`id`, `product_type`, `size`, `price`, `cost`) VALUES (NULL, '$type', '$size', '$price', '$cost');" );
$sql->disconnect ();
exit ();