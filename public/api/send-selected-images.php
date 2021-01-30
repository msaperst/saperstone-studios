<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

try {
    $album = Album::withId($_POST ['album']);
} catch (Exception $e) {
    echo json_encode(array('err' => $e->getMessage()));
    exit();
}

if (!$album->canUserAccess()) {
    header('HTTP/1.0 403 Unauthorized');
    exit ();
}

try {
    $what = $api->retrievePostString('what', 'What to select');
} catch (Exception $e) {
    echo json_encode(array('err' => $e->getMessage()));
    exit();
}

$sql = new Sql();
if ($what == "favorites") {
    $selected = array_column($sql->getRows("SELECT album_images.title FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.id WHERE favorites.user = '{$systemUser->getIdentifier()}' AND favorites.album = '{$album->getId()}';"), 'title');
    if (empty($selected)) {
        echo json_encode(array('err' => "You have not selected any favorites"));
        $sql->disconnect();
        exit();
    }
} else {
    try {
        $image = new Image($album, $what);
    } catch (Exception $e) {
        echo json_encode(array('err' => $e->getMessage()));
        $sql->disconnect();
        exit();
    }
    $selected = array($image->getTitle());
}

$name = $link = "Someone";
if (isset ($_POST ['name'])) {
    $name = $sql->escapeString($_POST ['name']);
}
$emailA = "";
if (isset ($_POST ['email'])) {
    $emailA = $sql->escapeString($_POST ['email']);
    $link = "<a href='mailto:$emailA'>$name</a>";
}
$comment = "";
if (isset ($_POST ['comment'])) {
    $comment = $sql->escapeString($_POST ['comment']);
}
$sql->disconnect();

// send email
$systemUser = User::fromSystem();
$from = "Selects <selects@saperstonestudios.com>";
$to = "Selects <" . getenv('EMAIL_SELECTS') . ">";
$subject = "Selects Have Been Made";
$email = new Email($to, $from, $subject);

$html = "<html><body>";
$html .= "<p>This is an automatically generated message from Saperstone Studios</p>";
$text = "This is an automatically generated message from Saperstone Studios\n\n";
$html .= "<p>$link has made a selection from the <a href='" . $session->getBaseURL() . "/user/album.php?album={$album->getId()}' target='_blank'>{$album->getName()}</a> album</p>";
$text .= "$name has made a selection from the {$album->getName()} album at " . $session->getBaseURL() . "/user/album.php?album={$album->getId()}.";
if ($emailA != "") {
    $text .= " Their email address is $emailA";
}
$text .= "\n\n";
$html .= "<p><ul><li>" . implode("</li><li>", $selected) . "</li></ul></p><br/>";
$text .= implode("\n", $selected) . "\n\n";
$html .= "<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$comment</p>";
$text .= "\t\t$comment";
$html .= "</body></html>";

$email->setHtml($html);
$email->setText($text);
try {
    $email->sendEmail();
} catch (Exception $e) {
    //apparently do nothing
}

// send a separate one to the user
if ($emailA != "") {
    $to = "$name <$emailA>";
    $subject = "Thank You for Making Selects";
    $email = new Email($to, $from, $subject);

    $text = "Thank you for making your selects. We'll start working on your images, and reach back out to you shortly with access to your final images.";
    $html = "<html><body>$text</body></html>";

    $email->setHtml($html);
    $email->setText($text);
    try {
        $email->sendEmail();
    } catch (Exception $e) {
        //apparently do nothing
    }
}

exit ();