<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceLoggedIn();

$firstName = "";
$lastName = "";
$curPass = "";
$email = "";

if (isset ($_POST ['email']) && filter_var($_POST ['email'], FILTER_VALIDATE_EMAIL)) {
    $email = $sql->escapeString($_POST ['email']);
} else {
    echo "Email is not provided";
    $conn->disconnect();
    exit ();
}
$row = $sql->getRow("SELECT email FROM users WHERE email='$email' AND id!={$user->getId()}");
if ($row ['email']) {
    echo "We already have another account on file for that email address. Try a different email address, or use the account associated with that email.";
    $conn->disconnect();
    exit ();
}

if (isset ($_POST ['firstName']) && $_POST ['firstName'] != "") {
    $firstName = $sql->escapeString($_POST ['firstName']);
} else {
    echo "First name is not provided";
    $conn->disconnect();
    exit ();
}

if (isset ($_POST ['lastName']) && $_POST ['lastName'] != "") {
    $lastName = $sql->escapeString($_POST ['lastName']);
} else {
    echo "Last name is not provided";
    $conn->disconnect();
    exit ();
}

$sql = "UPDATE users SET firstName='$firstName', lastName='$lastName', email='$email' WHERE id='" . $user->getId() . "';";
mysqli_query($conn->db, $sql);

if (isset ($_POST ['password']) && $_POST ['password'] != "" && isset ($_POST ['curPass']) && $_POST ['curPass'] == "") {
    echo "Please confirm old password to set new password";
    $conn->disconnect();
    exit ();
}

if (isset ($_POST ['password']) && $_POST ['password'] != "") {
    $curPass = md5($sql->escapeString($_POST ['curPass']));
    $row = $sql->getRow("SELECT usr FROM users WHERE id='" . $user->getId() . "' AND pass='$curPass'");
    if (!$row ['usr']) {
        echo "That password does not match what we have on record for you";
        $conn->disconnect();
        exit ();
    }
    $password = md5($sql->escapeString($_POST ['password']));
    $sql = "UPDATE users SET pass='$password' WHERE id='" . $user->getId() . "';";
    mysqli_query($conn->db, $sql);
}
mysqli_query($conn->db, "INSERT INTO `user_logs` VALUES ( {$user->getId ()}, CURRENT_TIMESTAMP, 'Updated User', NULL, NULL );");

$conn->disconnect();
exit ();