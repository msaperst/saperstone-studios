<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ($sql);

if (! $user->isLoggedIn ()) {
    echo "User must be logged in to add to their cart";
    $conn->disconnect ();
    exit ();
}

// empty out our old cart for this image
$sql = "DELETE FROM `cart` WHERE `user` = '{$user->getId()}';";
mysqli_query ( $conn->db, $sql );

// for each product, add it back in
if (isset ( $_POST ['images'] ) && is_array ( $_POST ['images'] )) {
    foreach ( $_POST ['images'] as $image ) {
        $album = ( int ) $image ['album'];
        $image = ( int ) $image ['image'];
        $product = ( int ) $image ['product'];
        $count = ( int ) $image ['count'];
        $sql = "INSERT INTO `cart` (`user`, `album`, `image`, `product`, `count`) VALUES ( '{$user->getId()}', '$album', '$image', '$product', '$count');";
        mysqli_query ( $conn->db, $sql );
    }
}

$sql = "SELECT SUM(`count`) AS total FROM `cart` WHERE `user` = '{$user->getId()}';";
$result = $sql->getRow( $sql );
echo $result ['total'];

$conn->disconnect ();
exit ();