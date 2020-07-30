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
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['category'] ) && $_POST ['category'] != "") {
    $category = $sql->escapeString( $_POST ['category'] );
} else {
    echo "Category is not provided";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['name'] ) && $_POST ['name'] != "") {
    $name = $sql->escapeString( $_POST ['name'] );
} else {
    echo "Name is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "INSERT INTO `product_types` (`id`, `category`, `name`) VALUES (NULL, '$category', '$name');";
mysqli_query ( $conn->db, $sql );
$last_id = mysqli_insert_id ( $conn->db );

echo $last_id;

$conn->disconnect ();
exit ();