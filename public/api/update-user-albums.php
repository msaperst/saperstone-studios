<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

if (isset ($_POST ['user'])) {
    $user = (int)$_POST ['user'];
} else {
    echo "User is not provided";
    exit ();
}

$sql = new Sql ();
$sql = "DELETE FROM albums_for_users WHERE user = $user";
mysqli_query($conn->db, $sql);

if (isset ($_POST ['albums'])) {
    foreach ($_POST ['albums'] as $album) {
        $sql = "INSERT INTO albums_for_users ( `user`, `album` ) VALUES ( '$user', '$album' );";
        mysqli_query($conn->db, $sql);
    }
}

$conn->disconnect();
exit ();