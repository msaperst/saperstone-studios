<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceLoggedIn();

$album = $api->retrieveGetInt('album', 'Album id');
if (is_array($album)) {
    echo $album['error'];
    exit();
}
$album_info = $sql->getRow("SELECT * FROM albums WHERE id = $album;");
if (!$album_info ['id']) {
    echo "Album id does not match any albums";
    $sql->disconnect();
    exit ();
}

$sequence = $api->retrieveGetInt('image', 'Image id');
if (is_array($sequence)) {
    echo $sequence['error'];
    exit();
}
$image_info = $sql->getRow("SELECT * FROM album_images WHERE album = $album AND sequence = $sequence;");
if (!$image_info ['id']) {
    echo "Image id does not match any images";
    $sql->disconnect();
    exit ();
}

// empty out our old cart for this image
$cart = $sql->getRows("SELECT * FROM `cart` WHERE `user` = '{$user->getId()}' AND `album` = '$album' and `image` = '$sequence'");
echo json_encode($cart);
$sql->disconnect();
exit ();