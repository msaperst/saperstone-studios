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

$storage_dir = "";
// setup our preview image
if (isset ( $_POST ['preview'] ['img'] ) && $_POST ['preview'] ['img'] != "") {
    $previewImage = mysqli_real_escape_string ( $conn->db, $_POST ['preview'] ['img'] );
    $storage_dir = dirname ( $blog_details ['preview'] );
    copy ( "$previewImage", "$storage_dir/preview_image-$id.jpg" );
    system ( "mogrify -resize 360x \"$storage_dir/preview_image-$id.jpg\"" );
    system ( "mogrify -density 72 \"$storage_dir/preview_image-$id.jpg\"" );
    $sql = "UPDATE `blog_details` SET `preview` = '$storage_dir/preview_image-$id.jpg' WHERE `id` = $id;";
    mysqli_query ( $conn->db, $sql );
}

// if we're changing the post activation
if (isset ( $_POST ['active'] )) {
    $active = ( int ) $_POST ['active'];
    $sql = "UPDATE `blog_details` SET `active` = '$active' WHERE `id` = $id;";
    mysqli_query ( $conn->db, $sql );
}

// if we're updating the content
if (isset ( $_POST ['content'] )) {
    // delete any old content
    $sql = "DELETE FROM blog_texts WHERE blog='$id';";
    mysqli_query ( $conn->db, $sql );
    $sql = "DELETE FROM blog_images WHERE blog='$id';";
    mysqli_query ( $conn->db, $sql );
    // add the new content
    foreach ( $_POST ['content'] as $content ) {
        if ($content ['type'] == "text") {
            $text = mysqli_real_escape_string ( $conn->db, $content ['text'] );
            $group = ( int ) $content ['group'];
            $sql = "INSERT INTO `blog_texts` ( `blog`, `contentGroup`, `text` ) VALUES ('$id', '$group', '$text');";
            mysqli_query ( $conn->db, $sql );
        } elseif ($content ['type'] == "images") {
            $group = ( int ) $content ['group'];
            foreach ( $content ['imgs'] as $img ) {
                $location = mysqli_real_escape_string ( $conn->db, $img ['location'] );
                $top = ( int ) $img ['top'];
                $left = ( int ) $img ['left'];
                $width = ( int ) $img ['width'];
                $height = ( int ) $img ['height'];
                
                rename ( "$location", "$storage_dir/" . basename ( $location ) );
                system ( "mogrify -resize ${width}x \"$storage_dir/" . basename ( $location ) . "\"" );
                system ( "mogrify -density 72 \"$storage_dir/" . basename ( $location ) . "\"" );
                
                $sql = "INSERT INTO `blog_images` ( `blog`, `contentGroup`, `location`, `top`, `left`, `width`, `height` ) VALUES ('$id', '$group', '$storage_dir/" . basename ( $location ) . "', '$top', '$left', '$width', '$height');";
                mysqli_query ( $conn->db, $sql );
            }
        } else {
            echo "You provided some bad content";
            exit ();
        }
    }
}

$conn->disconnect ();
exit ();