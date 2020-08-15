<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new CurrentUser ($sql);
$api = new Api ($sql, $user);

// ensure we are logged in appropriately
if (!$user->isAdmin() && $user->getRole() != "uploader") {
    header('HTTP/1.0 401 Unauthorized');
    if ($user->isLoggedIn()) {
        echo "You do not have appropriate rights to perform this action";
    }
    $sql->disconnect();
    exit ();
}

$name = "";
$description = "";
$date = "NULL";
// confirm we have an album name
$name = $api->retrievePostString('name', 'Album name');
if (is_array($name)) {
    echo $name['error'];
    exit();
}

// sanitize our inputs
if (isset ($_POST ['description'])) {
    $description = $sql->escapeString($_POST ['description']);
}
if (isset ($_POST ['date']) && $_POST ['date'] != "") {
    $date = $sql->escapeString($_POST ['date']);
    $format = 'Y-m-d';
    $d = DateTime::createFromFormat($format, $date);
    if (!($d && $d->format($format) === $date)) {
        echo "Album date is not the correct format";
        $sql->disconnect();
        exit ();
    }
    $date = "'" . $date . "'";
}

// generate our location for the files
$location = preg_replace("/[^A-Za-z0-9]/", '', $name);
$location = $location . "_" . time();
if (!mkdir("../albums/$location", 0755, true)) {
    $error = error_get_last();
    echo $error ['message'] . "<br/>";
    echo "Unable to create album";
    $sql->disconnect();
    exit ();
}

$last_id = $sql->executeStatement("INSERT INTO `albums` (`name`, `description`, `date`, `location`, `owner`) VALUES ('$name', '$description', $date, '$location', '" . $user->getId() . "');");
if ($user->getRole() == "uploader") {
    $sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES ('" . $user->getId() . "', '$last_id');");
    $sql->executeStatement("INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Created Album', NULL, $last_id );");
}
echo $last_id;
$sql->disconnect();
exit ();