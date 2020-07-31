<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();
$user = new User ($sql);

// only admin users
if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "You do not have appropriate rights to perform this action";
    }
    $sql->disconnect ();
    exit ();
}

$id = "";
if (isset ( $_POST ['post'] ) && $_POST ['post'] != "") {
    $id = ( int ) $_POST ['post'];
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

$blog_details = $sql->getRow( "SELECT * FROM blog_details WHERE id = $id;" );
if (! $blog_details ['id']) {
    echo "Blog id does not match any blogs";
    $sql->disconnect ();
    exit ();
}

// delete our files
$rows = $sql->getRows( "SELECT * FROM blog_images WHERE blog='$id';" );
foreach( $rows as $row ) {
    unlink( "../blog/" . $row['location'] );
}
//TODO - delete the folder if empty

// delete our database
$sql->executeStatement( "DELETE FROM blog_details WHERE id='$id';" );
$sql->executeStatement( "DELETE FROM blog_images WHERE blog='$id';" );
$sql->executeStatement( "DELETE FROM blog_tags WHERE blog='$id';" );
$sql->executeStatement( "DELETE FROM blog_texts WHERE blog='$id';" );

$sql->disconnect ();
exit ();