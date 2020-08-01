<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceLoggedIn();

$album = "";
if (isset ( $_POST ['album'] ) && $_POST ['album'] != "") {
    $album = ( int ) $_POST ['album'];
} else {
    if (! isset ( $_POST ['album'] )) {
        echo "Album id is required!";
    } elseif ($_POST ['album'] != "") {
        echo "Album id cannot be blank!";
    } else {
        echo "Some other Album id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM albums WHERE id = $album;";
$album_info = $sql->getRow( $sql );
if (! $album_info ['id']) {
    echo "That ID doesn't match any albums";
    $conn->disconnect ();
    exit ();
}

$sequence = "";
if (isset ( $_POST ['image'] ) && $_POST ['image'] != "") {
    $sequence = ( int ) $_POST ['image'];
} else {
    if (! isset ( $_POST ['image'] )) {
        echo "Image id is required!";
    } elseif ($_POST ['image'] != "") {
        echo "Image id cannot be blank!";
    } else {
        echo "Some other Image id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM album_images WHERE album = $album AND sequence = $sequence;";
$album_info = $sql->getRow( $sql );
if (! $album_info ['title']) {
    echo "That image doesn't match anything";
    $conn->disconnect ();
    exit ();
}

// empty out our old cart for this image
$sql = "DELETE FROM `cart` WHERE `user` = '{$user->getId()}' AND `album` = '$album' and `image` = '$sequence'";
mysqli_query ( $conn->db, $sql );

// for each product, add it back in
if (isset ( $_POST ['products'] ) && is_array ( $_POST ['products'] )) {
    foreach ( $_POST ['products'] as $product => $count ) {
        $product = ( int ) $product;
        $count = ( int ) $count;
        $sql = "INSERT INTO `cart` (`user`, `album`, `image`, `product`, `count`) VALUES ( '{$user->getId()}', '$album', '$sequence', '$product', '$count');";
        mysqli_query ( $conn->db, $sql );
    }
}

$sql = "SELECT SUM(`count`) AS total FROM `cart` WHERE `user` = '{$user->getId()}';";
$result = $sql->getRow( $sql );
echo $result ['total'];

$conn->disconnect ();
exit ();