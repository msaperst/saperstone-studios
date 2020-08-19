<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$what = $api->retrievePostString('what', 'What to download');
if (is_array($what)) {
    echo json_encode($what);
    exit();
}

try {
    $album = new Album($_POST['album']);
} catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage()));
    exit();
}

// check for album access
$sql = new Sql();
$isAlbumDownloadable = $sql->getRowCount("SELECT * FROM `download_rights` WHERE user = '0' AND album = '{$album->getId()}';");
if (!$systemUser->isLoggedIn() && !$isAlbumDownloadable) {
    header('HTTP/1.0 401 Unauthorized');
    $sql->disconnect();
    exit ();
}

$userId = $systemUser->getIdentifier();

//TODO - might need to ensure that the user has access to the album
// determine what the user can download
$downloadable = array();
foreach ($sql->getRows("SELECT * FROM `download_rights` WHERE `user` = '" . $systemUser->getId() . "' OR `user` = '0';") as $r) {
    if ($r ['album'] == "*" || ($r ['album'] == $album->getId() && $r ['image'] == "*")) {
        $downloadable = array_merge($downloadable, $sql->getRows("SELECT * FROM album_images WHERE album = {$album->getId()};"));
    } elseif ($r ['album'] == $album->getId()) {
        $downloadable = array_merge($downloadable, $sql->getRows("SELECT * FROM album_images WHERE album = {$album->getId()} AND id = " . $r ['image'] . ";"));
    }
}
$downloadable = array_unique($downloadable, SORT_REGULAR);

// determine what the user wants to download
$desired = array();
if ($what == "all") {
    $desired = $sql->getRows("SELECT album_images.* FROM album_images WHERE album = '{$album->getId()}';");
} elseif ($what == "favorites") {
    $desired = $sql->getRows("SELECT album_images.* FROM favorites LEFT JOIN album_images ON favorites.album = album_images.album AND favorites.image = album_images.id WHERE favorites.user = '$userId' AND favorites.album = '{$album->getId()}';");
} else {
    $desired = $sql->getRows("SELECT * FROM album_images WHERE album = '{$album->getId()}' AND sequence = '" . (int)$what . "';");
}

// determine what we will download
$available = array();
if ($systemUser->isAdmin()) {    // if we're an admin, we can download all files
    $available = $desired;
} else {    // check to see which files we want to download, we can download
    foreach ($desired as $file) {
        $result = doesArrayContainFile($downloadable, $file);
        if ($result) {
            $available [] = $file;
        }
    }
}

if (empty ($available)) {
    $response ['error'] = "There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.";
    echo json_encode($response);
    $sql->disconnect();
    exit ();
}

// for each available file, zip it, and then download it
$images = "";
$image_array = array();
foreach ($available as $image) {
    $file = $image ['location'];
    if (file_exists(dirname(".." . $file) . "/full/" . basename($file))) {
        $images .= dirname(".." . $file) . "/full/" . basename($file) . " ";
        $image_array [] = basename($file);
    } elseif (file_exists(".." . $file)) {
        $images .= ".." . $file . " ";
        $image_array [] = basename($file);
    }
}
if ($images == "") {
    $response ['error'] = "No files exist for you to download. Please <a class='gen' target='_blank' href='mailto:admin@saperstonestudios.com'>contact our System Administrators</a>.";
    echo json_encode($response);
    $sql->disconnect();
    exit ();
}
if (!is_dir("../tmp/")) {
    mkdir("../tmp/");
}
$myFile = "../tmp/{$album->getName()} " . date("Y-m-d H-i-s") . ".zip";
$command = `zip -j "$myFile" $images`;
$response ['file'] = $myFile;
echo json_encode($response);

// update our user records table
$sql->executeStatement("INSERT INTO `user_logs` VALUES ( {$systemUser->getId()}, CURRENT_TIMESTAMP, 'Downloaded', '" . implode("\n", $image_array) . "', {$album->getId()} );");
$sql->disconnect();

// send email
// TODO - NEED TO CHANGE BACK, JUST GOOD FOR TESTING!!!
$email = new Email("Contact <msaperst+sstest@gmail.com>", "Actions <actions@saperstonestudios.com>", "Someone Downloaded Something");

$html = "<html><body>";
$html .= "<p>This is an automatically generated message from Saperstone Studios</p>";
$text = "This is an automatically generated message from Saperstone Studios\n\n";
$html .= "<p>Downloads have been made from the <a href='" . $session->getBaseURL() . "/user/album.php?album={$album->getId()}' target='_blank'>{$album->getName()}</a> album</p>";
$text .= "Downloads have been made from the {$album->getName()} album at " . $session->getBaseURL() . "/user/album.php?album={$album->getId()}\n\n";
$html .= "<p><ul><li>" . implode("</li><li>", $image_array) . "</li></ul></p><br/>";
$text .= implode("\n", $image_array) . "\n\n";
$html .= "<p><strong>Name</strong>: " . $systemUser->getName() . "<br/>";
$text .= "Name: " . $systemUser->getName() . "\n";
$html .= "<strong>Email</strong>: <a href='mailto:" . $systemUser->getEmail() . "'>" . $systemUser->getEmail() . "</a><br/>";
$text .= "Email: " . $systemUser->getEmail() . "\n";
$html .= $email->getUserInfoHtml();
$text .= $email->getUserInfoText();
$html .= "</body></html>";

$email->setHtml($html);
$email->setText($text);
try {
    $email->sendEmail();
} catch (Exception $e) {
    //apparently do nothing...
}
exit();

// our function to see if an array of files contains the expected file
function doesArrayContainFile($array, $file) {
    foreach ($array as $element) {
        $match = true;
        foreach ($element as $key => $value) {
            if ($element [$key] != $file [$key]) {
                $match = false;
            }
        }
        if ($match) {
            return true;
        }
    }
    return false;
}