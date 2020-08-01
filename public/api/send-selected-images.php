<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

if (! $user->isLoggedIn ()) {
    $user = getClientIP();
} else {
    $user = $user->getId ();
}

if (isset ( $_POST ['what'] )) {
    $what = $sql->escapeString( $_POST ['what'] );
} else {
    $response ['err'] = "Need to provide what you desire to download";
    echo json_encode ( $response );
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['album'] )) {
    $album = $sql->escapeString( $_POST ['album'] );
} else {
    echo "No album was provided. Please refresh this page and resubmit this request.";
    $conn->disconnect ();
    exit ();
}
$sql = "SELECT * FROM `albums` WHERE id = '$album';";
$album_info = $sql->getRow( $sql );
// if the album doesn't exist, throw a 404 error
if (! $album_info ['name']) {
    echo "The provided album does not exist. Please refresh this page and resubmit this request.";
    $conn->disconnect ();
    exit ();
}

$selected = array ();
if ($what == "favorites") {
    $sql = "SELECT album_images.* FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.sequence WHERE favorites.user = '$user' AND favorites.album = '$album';";
    $result = mysqli_query ( $conn->db, $sql );
    $desired = array ();
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $selected [] = $r ['title'];
    }
} else {
    $sql = "SELECT * FROM album_images WHERE album = '$album' AND sequence = '$what';";
    $result = mysqli_query ( $conn->db, $sql );
    $desired = array ();
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $selected [] = $r ['title'];
    }
}

$name = "";
if (isset ( $_POST ['name'] )) {
    $name = $sql->escapeString( $_POST ['name'] );
}
$email = "";
if (isset ( $_POST ['email'] )) {
    $email = $sql->escapeString( $_POST ['email'] );
}
$comment = "";
if (isset ( $_POST ['comment'] )) {
    $comment = $sql->escapeString( $_POST ['comment'] );
}

// send email
$user = new User ($sql);
$IP = getClientIP();
$geo_info = json_decode ( file_get_contents ( "http://ipinfo.io/$IP/json" ) );
require_once ($path = dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "resources/Browser.php-master/src/Browser.php");
$browser = new Browser ();
$from = "Selects <selects@saperstonestudios.com>";
$to = "Selects <selects@saperstonestudios.com>";
$subject = "Selects Have Been Made";

$html = "<html><body>";
$html .= "<p>This is an automatically generated message from Saperstone Studios</p>";
$text = "This is an automatically generated message from Saperstone Studios\n\n";
$html .= "<p><a href='mailto:$email'>$name</a> has made a selection from the <a href='" . $_SERVER ['HTTP_REFERER'] . "' target='_blank'>" . $album_info ['name'] . "</a> album</p>";
$text .= "$name has made a selection from the " . $album_info ['name'] . " album at " . $_SERVER ['HTTP_REFERER'] . ". Their email address is $email\n\n";
$html .= "<p><ul><li>" . implode ( "</li><li>", $selected ) . "</li></ul></p><br/>";
$text .= implode ( "\n", $selected ) . "\n\n";
$html .= "<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$comment</p>";
$text .= "\t\t$comment";
$html .= "</body></html>";

require_once "Mail.php";
require_once "Mail/mime.php";
$crlf = "\n";
$mime = new Mail_mime ( $crlf );
$mime->setTXTBody ( $text );
$mime->setHTMLBody ( $html );
$body = $mime->get ();
require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/email.php";

// send a separate one to the user
if ($email != "") {
    $to = "$name <$email>";
    $subject = "Thank You for Making Selects";

    $text = "Thank you for making your selects. We'll start working on your images, and reach back out to you shortly with access to your final images.";
    $html = "<html><body>$text</body></html>";

    require_once "Mail.php";
    require_once "Mail/mime.php";
    $crlf = "\n";
    $mime = new Mail_mime ( $crlf );
    $mime->setTXTBody ( $text );
    $mime->setHTMLBody ( $html );
    $body = $mime->get ();
    require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/email.php";
}

$conn->disconnect ();
exit ();