<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'session.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'user.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'api.php';
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$user_id = $user->getIdentifier();

$album = $api->retrievePostInt('album', 'Album id');
if( is_array( $album ) ) {
    echo $album['error'];
    exit();
}
$album_info = $sql->getRow( "SELECT * FROM albums WHERE id = $album;" );
if (! $album_info ['id']) {
    echo "Album id does not match any albums";
    $sql->disconnect ();
    exit ();
}

$sequence = $api->retrievePostInt('image', 'Image id');
if( is_array( $sequence ) ) {
    echo $sequence['error'];
    exit();
}
$image_info = $sql->getRow( "SELECT * FROM album_images WHERE album = $album AND sequence = $sequence;" );
if (! $image_info ['id']) {
    echo "Image id does not match any images";
    $sql->disconnect ();
    exit ();
}

if ($user->isLoggedIn ()) {
    // update our user records table
    $sql->executeStatement( "INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Set Favorite', '$sequence', $album );" );
}

// update our mysql database
$sql->executeStatement( "INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('$user_id', '$album', '$sequence');" );
// get our new favorite count for the album
echo $sql->getRow( "SELECT COUNT(*) AS total FROM `favorites` WHERE `user` = '$user_id' AND `album` = '$album';" ) ['total'];
$sql->disconnect ();
exit ();
?>