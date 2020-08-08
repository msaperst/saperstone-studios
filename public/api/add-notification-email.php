<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
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

$email = $api->retrieveValidatedPost('email', 'Email', FILTER_VALIDATE_EMAIL );
if( is_array( $email ) ) {
    echo $email['error'];
    exit();
}

// update our mysql database
$sql->executeStatement( "INSERT INTO `notification_emails` (`album`, `user`, `email`) VALUES ('$album', '$user_id', '$email');" );
$sql->disconnect ();
exit ();