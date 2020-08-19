<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

try {
    $blog = new Blog($_POST['post']);
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$sql = new Sql ();
$name = "";
if (isset ($_POST ['name']) && $_POST ['name'] != "") {
    $name = $sql->escapeString($_POST ['name']);
}

$email = "";
if (isset ($_POST ['email']) && $_POST ['email'] != "") {
    $email = $sql->escapeString($_POST ['email']);
}

try {
    $message = $api->retrievePostString('message', 'Message');
} catch (Exception $e) {
    echo $e->getMessage();
    $sql->disconnect();
    exit();
}

if ($systemUser->getId() != "") {
    $user = "'" . $systemUser->getId() . "'";
} else {
    $user = 'NULL';
}

echo $sql->executeStatement("INSERT INTO blog_comments ( blog, user, name, date, ip, email, comment ) VALUES ({$blog->getId()}, $user, '$name', CURRENT_TIMESTAMP, '" . $session->getClientIP() . "', '$email', '$message' );");
$sql->disconnect();
exit ();