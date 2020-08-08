<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$id = $api->retrievePostInt('post', 'Blog id');
if( is_array( $id ) ) {
    echo $id['error'];
    exit();
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