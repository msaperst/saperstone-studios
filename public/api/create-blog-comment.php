<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$post = $api->retrievePostInt('post', 'Blog id');
if( is_array( $post ) ) {
    echo $post['error'];
    exit();
}

$blog_info = $sql->getRow( "SELECT * FROM blog_details WHERE id = $post;" );
if (! $blog_info ['id']) {
    echo "Blog id does not match any blogs";
    $sql->disconnect ();
    exit ();
}

$name = "";
if (isset ( $_POST ['name'] ) && $_POST ['name'] != "") {
    $name = $sql->escapeString( $_POST ['name'] );
}

$email = "";
if (isset ( $_POST ['email'] ) && $_POST ['email'] != "") {
    $email = $sql->escapeString( $_POST ['email'] );
}

$message = $api->retrievePostString('message', 'Message');
if( is_array( $message ) ) {
    echo $message['error'];
    exit();
}

if ($user->getId () != "") {
    $user = "'" . $user->getId () . "'";
} else {
    $user = 'NULL';
}

echo $sql->executeStatement ( "INSERT INTO blog_comments ( blog, user, name, date, ip, email, comment ) VALUES ($post, $user, '$name', CURRENT_TIMESTAMP, '" . $session->getClientIP() . "', '$email', '$message' );" );
$sql->disconnect ();
exit ();
?>