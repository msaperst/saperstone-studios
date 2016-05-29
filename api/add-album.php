<?php
require_once "../php/sql.php";

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

require_once "../php/user.php";

// ensure we are logged in appropriately
if (getRole () != "admin") {
    header ( "HTTP/1.0 403 Forbidden" );
    exit ();
}

// confirm we have an album name
if (isset ( $_POST ['name'] ) && $_POST ['name'] != "") {
    $name = $_POST ['name'];
} else {
    echo "Album name is required!";
    exit ();
}

// sanitize our inputs
if (isset ( $_POST ['name'] )) {
    $name = mysqli_real_escape_string ( $db, $_POST ['name'] );
}
if (isset ( $_POST ['description'] )) {
    $description = mysqli_real_escape_string ( $db, $_POST ['description'] );
}
if (isset ( $_POST ['date'] )) {
    $date = mysqli_real_escape_string ( $db, $_POST ['date'] );
}

// generate our location for the files
$location = preg_replace ( "/[^A-Za-z0-9]/", '', $name );
$location = $location . "_" . time ();
mkdir ( "../albums/$location" );

$sql = "INSERT INTO `albums` (`name`, `description`, `date`, `location`) VALUES ('$name', '$description', '$date', '$location');";
mysqli_query ( $db, $sql );
$last_id = mysqli_insert_id ( $db );

echo $last_id;

exit ();