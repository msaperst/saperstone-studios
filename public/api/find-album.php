<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$code = $api->retrieveGetString('code', 'Album code');
if( is_array( $code ) ) {
    echo $code['error'];
    exit();
}

$r = $sql->getRow( "SELECT * FROM albums WHERE code = '$code';" );
if ($r ['id']) {
    $_SESSION ["searched"] [$r ['id']] = md5( "ablum" . $code );
    $preferences = json_decode( $_COOKIE['CookiePreferences'] );
    if ( is_array( $preferences ) && in_array( "preferences", $preferences ) ) {
        $searched = json_decode( $_COOKIE ["searched"] );
        $searched [$r ['id']] = md5( "ablum" . $code );
        $_COOKIE ["searched"] = json_encode( $searched );
        setcookie ( 'searched', json_encode( $searched ), time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
    }
    echo $r ['id'];
} else {
    echo "That code does not match any albums";
    $sql->disconnect ();
    exit ();
}

if ($user->isLoggedIn() && isset ( $_GET ['albumAdd'] ) && $_GET ['albumAdd'] == 1) {
    $s = $sql->getRow( "SELECT * FROM albums_for_users WHERE user = '" . $user->getId () . "' AND album = '" . $r ['id'] . "';" );
    if (! $s ['user']) {
        $sql->executeStatement( "INSERT INTO albums_for_users ( `user`, `album` ) VALUES ( '" . $user->getId () . "', '" . $r ['id'] . "' );" );
    }
}

$sql->disconnect ();
exit ();
