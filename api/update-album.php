<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/user.php";

if (getRole () != "admin") {
    header ( "HTTP/1.0 403 Forbidden" );
    exit ();
}

$name = "";
$description = "";
$date = "";

if( isset( $_POST['id'] ) ) {
    $id = $_POST ['id'];
} else {
    echo "ID is not provided";
    exit();
}
if( isset( $_POST['name'] ) ) {
    $name = mysqli_real_escape_string ( $db, $_POST ['name'] );
}
if( isset( $_POST['description'] ) ) {
    $description = mysqli_real_escape_string ( $db, $_POST ['description'] );
}
if( isset( $_POST['date'] ) ) {
    $date = mysqli_real_escape_string ( $db, $_POST ['date'] );
}

$sql = "UPDATE albums SET name='$name', description='$description', date='$date' WHERE id='$id';";
echo $sql;
mysqli_query ( $db, $sql );
exit ();

?>