<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();
$user = new User ($sql);

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "You do not have appropriate rights to perform this action";
    }
    $sql->disconnect ();
    exit ();
}

$tag = "";

if (isset ( $_POST ['tag'] ) && $_POST ['tag'] != "") {
    $tag = $sql->escapeString( $_POST ['tag'] );
} else {
    if (! isset ( $_POST ['tag'] )) {
        echo "Blog tag is required";
    } elseif ($_POST ['tag'] == "") {
        echo "Blog tag can not be blank";
    } else {
        echo "Some other blog tag error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$row = $sql->getRow( "SELECT * FROM `tags` WHERE `tag` = '$tag';" );
if ($row ['id']) {
    echo "Blog tag already exists";
    $sql->disconnect ();
    exit ();
}

$last_id = $sql->executeStatement( "INSERT INTO tags ( tag ) VALUES ('$tag');" );
echo $last_id;
$sql->disconnect ();
exit ();