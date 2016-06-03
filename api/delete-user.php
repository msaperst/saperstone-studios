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
    header ( 'HTTP/1.0 401 Unauthorized' );
    exit ();
}

if (isset ( $_POST ['id'] )) {
    $id = $_POST ['id'];
} else {
    echo "ID is not provided";
    exit ();
}

$sql = "DELETE FROM users WHERE id='$id';";
mysqli_query ( $db, $sql );
exit ();

?>