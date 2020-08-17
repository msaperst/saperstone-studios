<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$userId = $systemUser->getIdentifier();

$sql = new Sql ();
if (isset ($_POST ['what'])) {
    $what = $sql->escapeString($_POST ['what']);
} else {
    $response ['err'] = "Need to provide what you desire to download";
    echo json_encode($response);
    $conn->disconnect();
    exit ();
}

if (isset ($_POST ['album'])) {
    $album = $sql->escapeString($_POST ['album']);
} else {
    echo "No album was provided. Please refresh this page and resubmit this request.";
    $conn->disconnect();
    exit ();
}
$sql = "SELECT * FROM `albums` WHERE id = '$album';";
$album_info = $sql->getRow($sql);
// if the album doesn't exist, throw a 404 error
if (!$album_info ['name']) {
    echo "The provided album does not exist. Please refresh this page and resubmit this request.";
    $conn->disconnect();
    exit ();
}

$selected = array();
if ($what == "favorites") {
    $sql = "SELECT album_images.* FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.id WHERE favorites.user = '$systemUser' AND favorites.album = '$album';";
    $result = mysqli_query($conn->db, $sql);
    $desired = array();
    while ($r = mysqli_fetch_assoc($result)) {
        $selected [] = $r ['title'];
    }
} else {
    $sql = "SELECT * FROM album_images WHERE album = '$album' AND sequence = '$what';";
    $result = mysqli_query($conn->db, $sql);
    $desired = array();
    while ($r = mysqli_fetch_assoc($result)) {
        $selected [] = $r ['title'];
    }
}

$name = "";
if (isset ($_POST ['name'])) {
    $name = $sql->escapeString($_POST ['name']);
}
$emailA = "";
if (isset ($_POST ['emailA'])) {
    $emailA = $sql->escapeString($_POST ['emailA']);
}
$comment = "";
if (isset ($_POST ['comment'])) {
    $comment = $sql->escapeString($_POST ['comment']);
}
$sql->disconnect();

// send email
$systemUser = User::fromSystem();
$from = "Selects <selects@saperstonestudios.com>";
$to = "Selects <selects@saperstonestudios.com>";
$subject = "Selects Have Been Made";
$email = new Email($to, $from, $subject);

$html = "<html><body>";
$html .= "<p>This is an automatically generated message from Saperstone Studios</p>";
$text = "This is an automatically generated message from Saperstone Studios\n\n";
$html .= "<p><a href='mailto:$emailA'>$name</a> has made a selection from the <a href='" . $session->getBaseURL() . "/user/album.php?album=" . $album_info ['id'] . "' target='_blank'>" . $album_info ['name'] . "</a> album</p>";
$text .= "$name has made a selection from the " . $album_info ['name'] . " album at " . $session->getBaseURL() . "/user/album.php?album=" . $album_info ['id'] . ". Their email address is $emailA\n\n";
$html .= "<p><ul><li>" . implode("</li><li>", $selected) . "</li></ul></p><br/>";
$text .= implode("\n", $selected) . "\n\n";
$html .= "<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$comment</p>";
$text .= "\t\t$comment";
$html .= "</body></html>";

$email->setHtml($html);
$email->setText($text);
$email->sendEmail();

// send a separate one to the user
if ($emailA != "") {
    $to = "$name <$emailA>";
    $subject = "Thank You for Making Selects";
    $email = new Email($to, $from, $subject);

    $text = "Thank you for making your selects. We'll start working on your images, and reach back out to you shortly with access to your final images.";
    $html = "<html><body>$text</body></html>";

    $email->setHtml($html);
    $email->setText($text);
    $email->sendEmail();
}

exit ();