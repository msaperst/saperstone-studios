<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new CurrentUser ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$username = "";
$firstName = "";
$lastName = "";
$email = "";
$role = "";
$active = "";

if (isset ($_POST ['id'])) {
    $id = ( int )$_POST ['id'];
} else {
    echo "ID is not provided";
    $conn->disconnect();
    exit ();
}
if (isset ($_POST ['username']) && $_POST ['username'] != "") {
    $username = $sql->escapeString($_POST ['username']);
} else {
    echo "Username is not provided";
    $conn->disconnect();
    exit ();
}
if (isset ($_POST ['email']) && filter_var($_POST ['email'], FILTER_VALIDATE_EMAIL)) {
    $email = $sql->escapeString($_POST ['email']);
} else {
    echo "Email is not provided";
    $conn->disconnect();
    exit ();
}

$sql = "SELECT * FROM users WHERE usr = '$username' AND id != '$id';";
$row = $sql->getRow($sql);
if ($row ['usr']) {
    echo "That user ID already exists";
    $conn->disconnect();
    exit ();
}

if (isset ($_POST ['firstName'])) {
    $firstName = $sql->escapeString($_POST ['firstName']);
}
if (isset ($_POST ['lastName'])) {
    $lastName = $sql->escapeString($_POST ['lastName']);
}
if (isset ($_POST ['role'])) {
    $role = $sql->escapeString($_POST ['role']);
}
if (isset ($_POST ['active'])) {
    $active = ( int )$_POST ['active'];
}

$sql = "UPDATE users SET usr='$username', firstName='$firstName', lastName='$lastName', email='$email', role='$role', active='$active' WHERE id='$id';";
mysqli_query($conn->db, $sql);

$conn->disconnect();
exit ();