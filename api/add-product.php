<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/user.php";

if (getRole () != "admin") {
    header ( 'HTTP/1.0 401 Unauthorized' );
    exit ();
}

if (isset ( $_POST ['category'] ) && $_POST ['category'] != "" ) {
    $category = $_POST ['category'];
} else {
    echo "Category is not provided";
    exit ();
}

if (isset ( $_POST ['name'] ) && $_POST ['name'] != "" ) {
    $name = $_POST ['name'];
} else {
    echo "Name is not provided";
    exit ();
}

$sql = "INSERT INTO `product_types` (`id`, `category`, `name`) VALUES (NULL, '$category', '$name');";
mysqli_query ( $db, $sql );
$last_id = mysqli_insert_id ( $db );

echo $last_id;
exit ();