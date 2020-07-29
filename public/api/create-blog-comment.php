<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();
$user = new User ($sql);

$post = "";
$name = "";
$email = "";
$message = "";

if (isset ( $_POST ['post'] ) && $_POST ['post'] != "") {
    $post = ( int ) $_POST ['post'];
} else {
    if (! isset ( $_POST ['post'] )) {
        echo "Blog id is required";
    } elseif ($_POST ['post'] == "") {
        echo "Blog id can not be blank";
    } else {
        echo "Some other blog id error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$blog_info = $sql->getRow( "SELECT * FROM blog_details WHERE id = $post;" );
if (! $blog_info ['id']) {
    echo "Blog id does not match any blogs";
    $sql->disconnect ();
    exit ();
}

if (isset ( $_POST ['name'] ) && $_POST ['name'] != "") {
    $name = $sql->escapeString( $_POST ['name'] );
}
if (isset ( $_POST ['email'] ) && $_POST ['email'] != "") {
    $email = $sql->escapeString( $_POST ['email'] );
}
if (isset ( $_POST ['message'] ) && $_POST ['message'] != "") {
    $message = $sql->escapeString( $_POST ['message'] );
} else {
    if (! isset ( $_POST ['message'] )) {
        echo "Message is required";
    } elseif ($_POST ['message'] == "") {
        echo "Message can not be blank";
    } else {
        echo "Some other message error occurred";
    }
    $sql->disconnect ();
    exit ();
}

if ($user->getId () != "") {
    $user = "'" . $user->getId () . "'";
} else {
    $user = 'NULL';
}

echo $sql->executeStatement ( "INSERT INTO blog_comments ( blog, user, name, date, ip, email, comment ) VALUES ($post, $user, '$name', CURRENT_TIMESTAMP, '" . getClientIP() . "', '$email', '$message' );" );
$sql->disconnect ();
exit ();
?>