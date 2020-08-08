<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$id = "";
if (isset ($_POST ['id']) && $_POST ['id'] != "") {
    $id = ( int )$_POST ['id'];
} else {
    if (!isset ($_POST ['id'])) {
        echo "Album id is required!";
    } elseif ($_POST ['id'] != "") {
        echo "Album id cannot be blank!";
    } else {
        echo "Some other Album id error occurred!";
    }
    $conn->disconnect();
    exit ();
}

$sql = "SELECT * FROM albums WHERE id = $id;";
$album_info = $sql->getRow($sql);
if (!$album_info ['id']) {
    echo "That ID doesn't match any albums";
    $conn->disconnect();
    exit ();
}
// only admin users and uploader users who own the album can make updates
if (!($user->isAdmin() || ($user->getRole() == "uploader" && $user->getId() == $album_info ['owner']))) {
    header('HTTP/1.0 401 Unauthorized');
    if ($user->isLoggedIn()) {
        echo "You do not have appropriate rights to perform this action";
    }
    $conn->disconnect();
    exit ();
}

$markup = "";
if (isset ($_POST ['markup'])) {
    $markup = $sql->escapeString($_POST ['markup']);
} else {
    echo "Markup is required!";
    $conn->disconnect();
    exit ();
}
if ($markup != "proof" && $markup != "watermark" && $markup != "none") {
    echo "Markup is not a valid option!";
    $conn->disconnect();
    exit ();
}

if (!$user->isAdmin()) {
    // update our user records table
    mysqli_query($conn->db, "INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Created Thumbs', NULL, $id );");
}

$sql = "SELECT * FROM albums WHERE id = $id;";
$result = mysqli_query($conn->db, $sql);
$album_info = mysqli_fetch_assoc($result);

system(dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "bin/make-thumbs.sh $id $markup " . $album_info ['location'] . " > /dev/null 2>&1 &");

$conn->disconnect();
exit ();