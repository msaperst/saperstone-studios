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
if (getRole () != "admin" && getRole () != "uploader") {
    header ( 'HTTP/1.0 401 Unauthorized' );
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
if (! mkdir ( "../albums/$location", 0755, true )) {
    $error = error_get_last ();
    echo $error ['message'] . "<br/>";
    echo "Unable to create album";
    exit ();
}

$sql = "INSERT INTO `albums` (`name`, `description`, `date`, `location`, `owner`) VALUES ('$name', '$description', '$date', '$location', '" . getUserId () . "');";
mysqli_query ( $db, $sql );
$last_id = mysqli_insert_id ( $db );

if( getRole () == "uploader" ) {
    $sql = "INSERT INTO `albums_for_users` (`user`, `album`) VALUES ('" .getUserId(). "', '$last_id');";
    mysqli_query ( $db, $sql );
}

echo $last_id;

exit ();