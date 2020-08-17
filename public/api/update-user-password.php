<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

$err = array();

if (isset ($_POST ['id'])) {
    $id = ( int )$_POST ['id'];
} else {
    $err [] = "ID is not provided";
}

$sql = new Sql ();
if (isset ($_POST ['password']) && $_POST ['password'] != "") {
    $_POST ['password'] = $sql->escapeString($_POST ['password']);
} else {
    $err [] = "All the fields must be filled in!";
}
if (isset ($_POST ['passwordConfirm']) && $_POST ['passwordConfirm'] != "") {
    $_POST ['passwordConfirm'] = $sql->escapeString($_POST ['passwordConfirm']);
} else {
    $err [] = "All the fields must be filled in!";
}

if ($_POST ['password'] != $_POST ['passwordConfirm']) {
    $err [] = "Password and Confirmation do not match!";
}

if ($_POST ['password'] == "" || $_POST ['passwordConfirm'] == "") {
    $err [] = "Password cannot be blank";
}
$err = array_unique($err);

if (count($err) > 0) {
    echo implode('<br />', $err);
    $conn->disconnect();
    exit ();
}

mysqli_query($conn->db, "UPDATE users SET pass='" . md5($_POST ['password']) . "' WHERE id='$id';");

$conn->disconnect();
exit ();