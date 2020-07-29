<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();
$user = new User ($sql);

// ensure we are logged in appropriately
if (! $user->isAdmin () && $user->getRole () != "uploader") {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action";
    }
    $sql->disconnect ();
    exit ();
}

$name = "";
$description = "";
$date = "NULL";
// confirm we have an album name
if (isset ( $_POST ['name'] ) && $_POST ['name'] != "") {
    $name = $sql->escapeString( $_POST ['name'] );
} else {
    if (! isset ( $_POST ['name'] )) {
        echo "Album name is required";
    } elseif ($_POST ['name'] == "") {
        echo "Album name can not be blank";
    } else {
        echo "Some other album name error occurred";
    }
    $sql->disconnect ();
    exit ();
}

// sanitize our inputs
if (isset ( $_POST ['description'] )) {
    $description = $sql->escapeString( $_POST ['description'] );
}
if (isset ( $_POST ['date'] ) && $_POST ['date'] != "") {
    $date = "'" . $sql->escapeString( $_POST ['date'] ) . "'";
}

// generate our location for the files
$location = preg_replace ( "/[^A-Za-z0-9]/", '', $name );
$location = $location . "_" . time ();
if (! mkdir ( "../albums/$location", 0755, true )) {
    $error = error_get_last ();
    echo $error ['message'] . "<br/>";
    echo "Unable to create album";
    $sql->disconnect ();
    exit ();
}

$last_id = $sql->executeStatement( "INSERT INTO `albums` (`name`, `description`, `date`, `location`, `owner`) VALUES ('$name', '$description', $date, '$location', '" . $user->getId () . "');" );
if ($user->getRole () == "uploader" && $last_id != 0) {
    $sql->executeStatement( "INSERT INTO `albums_for_users` (`user`, `album`) VALUES ('" . $user->getId () . "', '$last_id');" );
    $sql->executeStatement( "INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Created Album', NULL, $last_id );" );
}
if ($last_id == 0) {
    rmdir ( "../albums/$location" );
}

echo $last_id;

$sql->disconnect ();
exit ();