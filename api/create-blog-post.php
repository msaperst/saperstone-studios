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

$previewImage = "";
if (isset ( $_POST ['preview'] ['img'] ) && $_POST ['preview'] ['img'] != "") {
    $previewImage = mysqli_real_escape_string ( $conn->db, $_POST ['preview'] ['img'] );
} else {
    echo "No preview image provided";
    exit ();
}

$previewOffset = 0;
if (isset ( $_POST ['preview'] ['offset'] )) {
    $previewOffset = ( int ) $_POST ['preview'] ['offset'];
}

// quick check of our content
foreach ( $_POST ['content'] as $content ) {
    if ($content ['type'] != "text" && $content ['type'] != "images") {
        echo "You provided some bad content";
        exit ();
    }
}

// move and resize our preview image
$storage_dir = "../blog/" . str_replace ( "-", "/", $date );
if (! is_dir ( $storage_dir )) {
    mkdir ( $storage_dir, 0755, true );
}
copy ( "$previewImage", "$storage_dir/preview_image.jpg" );
system ( "mogrify -resize 360x \"$storage_dir/preview_image.jpg\"" );
system ( "mogrify -density 72 \"$storage_dir/preview_image.jpg\"" );

// write our initial blog information
$sql = "INSERT INTO `blog_details` ( `title`, `date`, `preview`, `offset` ) VALUES ('$title', '$date', '$storage_dir/preview_image.jpg', '$previewOffset' );";
mysqli_query ( $conn->db, $sql );
$blog_id = mysqli_insert_id ( $conn->db );

// update our preview image with the blog post id
rename ( "$storage_dir/preview_image.jpg", "$storage_dir/preview_image-$blog_id.jpg" );
$sql = "UPDATE `blog_details` SET `preview` = '$storage_dir/preview_image-$blog_id.jpg' WHERE `id` = $blog_id;";
mysqli_query ( $conn->db, $sql );

// enter our tag information
if (isset ( $_POST ['tags'] )) {
    foreach ( $_POST ['tags'] as $tag ) {
        $tag = mysqli_real_escape_string ( $conn->db, $tag );
        $sql = "INSERT INTO `blog_tags` ( `blog`, `tag` ) VALUES ('$blog_id', '$tag');";
        mysqli_query ( $conn->db, $sql );
    }
}

// get down all of our content
foreach ( $_POST ['content'] as $content ) {
    if ($content ['type'] == "text") {
        $text = mysqli_real_escape_string ( $conn->db, $content ['text'] );
        $group = ( int ) $content ['group'];
        $sql = "INSERT INTO `blog_texts` ( `blog`, `contentGroup`, `text` ) VALUES ('$blog_id', '$group', '$text');";
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
            
            $sql = "INSERT INTO `blog_images` ( `blog`, `contentGroup`, `location`, `top`, `left`, `width`, `height` ) VALUES ('$blog_id', '$group', '$storage_dir/" . basename ( $location ) . "', '$top', '$left', '$width', '$height');";
            mysqli_query ( $conn->db, $sql );
        }
    } else {
        echo "You provided some bad content";
        exit ();
    }
}

echo $blog_id;

$conn->disconnect ();
exit ();