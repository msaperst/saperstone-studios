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

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

$gallery = "";
if (isset ( $_POST ['gallery'] ) && $_POST ['gallery'] != "") {
    $gallery = ( int ) $_POST ['gallery'];
} else {
    if (! isset ( $_POST ['gallery'] )) {
        echo "Gallery id is required!";
    } elseif ($_POST ['gallery'] != "") {
        echo "Gallery id cannot be blank!";
    } else {
        echo "Some other Gallery id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM galleries WHERE id = $gallery;";
$gallery_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $gallery_info ['id']) {
    echo "That ID doesn't match any gallerys";
    $conn->disconnect ();
    exit ();
}

$image = "";
if (isset ( $_POST ['image'] ) && $_POST ['image'] != "") {
    $image = ( int ) $_POST ['image'];
} else {
    if (! isset ( $_POST ['image'] )) {
        echo "Image id is required!";
    } elseif ($_POST ['image'] != "") {
        echo "Image id cannot be blank!";
    } else {
        echo "Some other Image id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

// delete our image from mysql table
$sql = "SELECT location FROM gallery_images WHERE id='$image';";
$row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
$sql = "DELETE FROM gallery_images WHERE id='$image';";
mysqli_query ( $conn->db, $sql );

// need to re-sequence images in mysql table
$sql = "SET @seq:=-1;";
mysqli_query ( $conn->db, $sql );
$sql = "UPDATE gallery_images SET sequence=(@seq:=@seq+1) WHERE gallery='$gallery' ORDER BY `sequence`;";
mysqli_query ( $conn->db, $sql );

// delete our image from the file system
if ($row ['location'] != "") {
    system ( "rm -f " . escapeshellarg ( "../" . $row ['location'] ) );
    $parts = explode ( "/", $row ['location'] );
    $full = array_splice ( $parts, count ( $parts ) - 1, 0, "full" );
    system ( "rm -f " . escapeshellarg ( "../" . implode ( "/", $parts ) ) );
}

$conn->disconnect ();
exit ();