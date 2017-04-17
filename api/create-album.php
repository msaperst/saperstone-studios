<?php
require_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new User ();

// ensure we are logged in appropriately
if (! $user->isAdmin () && $user->getRole () != "uploader") {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

$name = "";
$description = "";
$date = "NULL";
// confirm we have an album name
if (isset ( $_POST ['name'] ) && $_POST ['name'] != "") {
    $name = mysqli_real_escape_string ( $conn->db, $_POST ['name'] );
} else {
    echo "Album name is required!";
    $conn->disconnect ();
    exit ();
}

// sanitize our inputs
if (isset ( $_POST ['description'] )) {
    $description = mysqli_real_escape_string ( $conn->db, $_POST ['description'] );
}
if (isset ( $_POST ['date'] ) && $_POST ['date'] != "") {
    $date = "'" . mysqli_real_escape_string ( $conn->db, $_POST ['date'] ) . "'";
}

// generate our location for the files
$location = preg_replace ( "/[^A-Za-z0-9]/", '', $name );
$location = $location . "_" . time ();
if (! mkdir ( "../albums/$location", 0755, true )) {
    $error = error_get_last ();
    echo $error ['message'] . "<br/>";
    echo "Unable to create album";
    $conn->disconnect ();
    exit ();
}

$sql = "INSERT INTO `albums` (`name`, `description`, `date`, `location`, `owner`) VALUES ('$name', '$description', $date, '$location', '" . $user->getId () . "');";
mysqli_query ( $conn->db, $sql );
$last_id = mysqli_insert_id ( $conn->db );

if ($user->getRole () == "uploader" && $last_id != 0) {
    $sql = "INSERT INTO `albums_for_users` (`user`, `album`) VALUES ('" . $user->getId () . "', '$last_id');";
    mysqli_query ( $conn->db, $sql );
    
    if ($user->isLoggedIn ()) {
        // update our user records table
        mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Created Album', NULL, $last_id );" );
    }
}
if ($last_id == 0) {
    rmdir ( "../albums/$location" );
}

echo $last_id;

$conn->disconnect ();
exit ();