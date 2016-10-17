<?php
require_once "../php/sql.php";
$conn = new sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new user ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    $conn->disconnect ();
    exit ();
}

$id = "";
if (isset ( $_POST ['post'] ) && $_POST ['post'] != "") {
    $id = ( int ) $_POST ['post'];
} else {
    if (! isset ( $_POST ['post'] )) {
        echo "Post id is required!";
    } elseif ($_POST ['post'] != "") {
        echo "Post id cannot be blank!";
    } else {
        echo "Some other Post id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM blog_details WHERE id = $id;";
$blog_details = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $blog_details ['id']) {
    echo "That ID doesn't match any posts";
    $conn->disconnect ();
    exit ();
}

$title = "";
if (isset ( $_POST ['title'] ) && $_POST ['title'] != "") {
    $title = mysqli_real_escape_string ( $conn->db, $_POST ['title'] );
} else {
    echo "No title was provided";
    exit ();
}

$date = "";
if (isset ( $_POST ['date'] ) && $_POST ['date'] != "") {
    $date = mysqli_real_escape_string ( $conn->db, $_POST ['date'] );
} else {
    echo "No date was provided";
    exit ();
}

$previewOffset = 0;
if (isset ( $_POST ['preview'] ['offset'] )) {
    $previewOffset = ( int ) $_POST ['preview'] ['offset'];
}

// set our gaurenteed blog information
$sql = "UPDATE `blog_details` SET `title` = '$title', `date` = '$date', `offset` = '$previewOffset' WHERE `id` = $id;";
mysqli_query ( $conn->db, $sql );

// delete any old tags
$sql = "DELETE FROM blog_tags WHERE blog='$id';";
mysqli_query ( $conn->db, $sql );
if (isset ( $_POST ['tags'] )) {
    foreach ( $_POST ['tags'] as $tag ) {
        $tag = mysqli_real_escape_string ( $conn->db, $tag );
        $sql = "INSERT INTO `blog_tags` ( `blog`, `tag` ) VALUES ('$id', '$tag');";
        mysqli_query ( $conn->db, $sql );
    }
}

// setup our preview image
if (isset ( $_POST ['preview'] ['img'] ) && $_POST ['preview'] ['img'] != "") {
    $previewImage = mysqli_real_escape_string ( $conn->db, $_POST ['preview'] ['img'] );
    $location = dirname( $blog_details ['preview'] );
    copy ( "$previewImage", "$location/preview_image-$id.jpg" );
    system ( "mogrify -resize 360x \"$location/preview_image-$id.jpg\"" );
    system ( "mogrify -density 72 \"$location/preview_image-$id.jpg\"" );
    $sql = "UPDATE `blog_details` SET `preview` = '$location/preview_image-$id.jpg' WHERE `id` = $id;";
    mysqli_query ( $conn->db, $sql );
}

//if we're changing the post activation
if (isset ( $_POST ['active'] )) {
    $active = ( int ) $_POST ['active'];
    $sql = "UPDATE `blog_details` SET `active` = '$active' WHERE `id` = $id;";
    mysqli_query ( $conn->db, $sql );
}

$conn->disconnect ();
exit ();