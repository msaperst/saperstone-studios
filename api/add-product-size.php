<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php"; $user = new user();

if ($user->getRole () != "admin") {
    header ( 'HTTP/1.0 401 Unauthorized' );
    exit ();
}

if (isset ( $_POST ['type'] ) && $_POST ['type'] != "" ) {
    $type = $_POST ['type'];
} else {
    echo "Type is not provided";
    exit ();
}

if (isset ( $_POST ['size'] ) && $_POST ['size'] != "" ) {
    $size = $_POST ['size'];
} else {
    echo "Size is not provided";
    exit ();
}

if (isset ( $_POST ['cost'] ) && $_POST ['cost'] != "" ) {
    $cost = $_POST ['cost'];
} else {
    echo "Cost is not provided";
    exit ();
}

if (isset ( $_POST ['price'] ) && $_POST ['price'] != "" ) {
    $price = $_POST ['price'];
} else {
    echo "Price is not provided";
    exit ();
}

$sql = "INSERT INTO `products` (`id`, `product_type`, `size`, `price`, `cost`) VALUES (NULL, '$type', '$size', '$price', '$cost');";
mysqli_query ( $db, $sql );
$last_id = mysqli_insert_id ( $db );

echo $last_id;
exit ();