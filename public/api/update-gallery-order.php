<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$systemUser = new CurrentUser ($sql);
$api = new Api ($sql, $systemUser);

$api->forceAdmin();

$id = "";
if (isset ($_POST ['id']) && $_POST ['id'] != "") {
    $id = ( int )$_POST ['id'];
} else {
    if (!isset ($_POST ['id'])) {
        echo "Gallery ID is required!";
    } elseif ($_POST ['id'] != "") {
        echo "Gallery ID cannot be blank!";
    } else {
        echo "Some other Gallery ID error occurred!";
    }
    $conn->disconnect();
    exit ();
}

$sql = "SELECT * FROM galleries WHERE id = $id;";
$gallery_info = $sql->getRow($sql);
if (!$gallery_info ['id']) {
    echo "That ID doesn't match any galleries";
    $conn->disconnect();
    exit ();
}

$imgs;

if (isset ($_POST ['imgs']) && is_array($_POST ['imgs'])) {
    $imgs = $_POST ['imgs'];
} else {
    echo "The images you passed in are in an invalid format";
    $conn->disconnect();
    exit ();
}

for ($x = 0; $x < sizeof($imgs); $x++) {
    $img = $imgs [$x];
    $sql = "UPDATE gallery_images SET sequence=" . $x . " WHERE id='" . ( int )$img ['id'] . "';";
    mysqli_query($conn->db, $sql);
}

$conn->disconnect();
exit ();