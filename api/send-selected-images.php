<?php
require_once "../php/sql.php";
$conn = new sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new user ();

if (! $user->isLoggedIn ()) {
    $user = $_SERVER ['REMOTE_ADDR'];
} else {
    $user = $user->getId ();
}

if (isset ( $_POST ['what'] )) {
    $what = $_POST ['what'];
} else {
    $response ['err'] = "Need to provide what you desire to download";
    echo json_encode ( $response );
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['album'] )) {
    $album = mysqli_real_escape_string ( $conn->db, $_POST ['album'] );
} else {
    echo "No album was provided. Please refresh this page and resubmit this request.";
    $conn->disconnect ();
    exit ();
}
$sql = "SELECT * FROM `albums` WHERE id = '$album';";
$album_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $album_info ['name']) { // if the album doesn't exist, throw a 404 error
    echo "The provided album does not exist. Please refresh this page and resubmit this request.";
    $conn->disconnect ();
    exit ();
}

if ($what == "favorites") {
    $sql = "SELECT album_images.* FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.sequence WHERE favorites.user = '$user' AND favorites.album = '$album';";
    $result = mysqli_query ( $conn->db, $sql );
    $desired = array ();
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $selected [] = $r['title'];
    }
} else {
    $sql = "SELECT * FROM album_images WHERE album = '$album' AND sequence = '$what';";
    $result = mysqli_query ( $conn->db, $sql );
    $desired = array ();
    while ( $r = mysqli_fetch_assoc ( $result ) ) {
        $selected [] = $r['title'];
    }
}

// send email
$user = new user ();
$IP = $_SERVER ['REMOTE_ADDR'];
$geo_info = json_decode ( file_get_contents ( "http://ipinfo.io/$IP/json" ) );
require_once ($path = '../plugins/Browser.php-master/lib/Browser.php');
$browser = new Browser ();
$from = "Selects <selects@saperstonestudios.com>";
$to = "Selects <selects@saperstonestudios.com>";
if( isset( $_POST['email'] ) ) {
    $to .= ", ".$_POST['name']." <".$_POST['email'].">";
}
$subject = "Selects Have Been Made";

$html = "<html><body>";
$html .= "<p>This is an automatically generated message from Saperstone Studios</p>";
$text = "This is an automatically generated message from Saperstone Studios\n\n";
$html .= "<p>A selection has been made from the <a href='" . $_SERVER ['HTTP_REFERER'] . "' target='_blank'>" . $album_info ['name'] . "</a> album</p>";
$text .= "A selection has been made from the " . $album_info ['name'] . " album at " . $_SERVER ['HTTP_REFERER'] . "\n\n";
$html .= "<p><ul><li>" . implode ( "</li><li>", $selected ) . "</li></ul></p><br/>";
$text .= implode ( "\n", $selected ) . "\n\n";
if( isset( $_POST['comment']) ) {
    $html .= "<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$_POST['comment']."</p>";
    $text .= "\t\t".$_POST['comment'];
}
$html .= "</body></html>";

require_once "Mail.php";
require_once "Mail/mime.php";
$crlf = "\n";
$mime = new Mail_mime ( $crlf );
$mime->setTXTBody ( $text );
$mime->setHTMLBody ( $html );
$body = $mime->get ();
require ('../php/email.php');

$conn->disconnect ();
exit ();