<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$start = 0;
$howMany = 999999999999999999;

$albumId = $api->retrieveGetInt('albumId', 'Album id');
if( is_array( $albumId ) ) {
    echo json_encode( $albumId );
    exit();
}
$album_info = $sql->getRow( "SELECT * FROM albums WHERE id = $albumId;" );
if (! $album_info ['id']) {
    echo json_encode( array( 'error' => "Album id does not match any albums" ) );
    $sql->disconnect ();
    exit ();
}

if (isset ( $_GET ['start'] )) {
    $start = ( int ) $_GET ['start'];
}
if (isset ( $_GET ['howMany'] )) {
    $howMany = ( int ) $_GET ['howMany'];
}

// logic found here: https://www.draw.io/#G1oa_DMoW0-na6KNuex0oligsw1jdRbqaI
if( $user->isAdmin () ) {
    // if admin, do nothing, you shall pass onwards
} else if ($album_info ['code'] &&  // if an album code exists
        ( ( $_SESSION ['searched'] != null && $_SESSION ['searched'] [$albumId] == md5( "album" . $album_info ['code'] ) ) || // and it's stored in your session
        ( $_COOKIE ['searched'] != null && json_decode( $_COOKIE ['searched'], true ) [$albumId] == md5( "album" . $album_info ['code'] ) ) ) ) { // or it's stored in your cookies
    // if you successfully searched for the album, do nothing, you shall pass onwards
} else if ( $user->isLoggedIn() ) {
    $albumUsers = $sql->getRows( "SELECT * FROM albums_for_users WHERE user = '" . $user->getId () . "';" );
    $albums = array();
    foreach( $albumUsers as $albumUser ) {
        array_push( $albums, $albumUser['album'] );
    }
    if (in_array ( $albumId, $albums )) {
        // user is logged in, and user has access to album, do nothing, you shall pass onwards
    } else {
        header ( 'HTTP/1.0 403 Unauthorized' );
        $sql->disconnect ();
        exit ();
    }
} else {
    header ( 'HTTP/1.0 403 Unauthorized' );
    $sql->disconnect ();
    exit ();
}

$images = $sql->getRows( "SELECT album_images.* FROM `album_images` JOIN `albums` ON album_images.album = albums.id WHERE albums.id = '$albumId' ORDER BY `sequence` LIMIT $start,$howMany;" );
echo json_encode ( $images );
$sql->disconnect ();
exit ();
?>