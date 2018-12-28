<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
$user = new User ();
$conn = new Sql ();
$conn->connect ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

$image = $maxWidth = $top = $bottom = "";
if (! isset ( $_POST ['image'] ) || $_POST ['image'] == "") {
    echo "Image to crop wasn't provided";
    exit ();
} else {
    $image = mysqli_real_escape_string ( $conn->db, $_POST ['image'] );
}
if (! file_exists ( $image )) {
    echo "Image to crop doesn't exist";
    exit ();
}
if (! isset ( $_POST ['max-width'] ) || $_POST ['max-width'] == "") {
    echo "Max-width of image crop wasn't provided";
    exit ();
} else {
    $maxWidth = ( int ) $_POST ['max-width'];
}
if (! isset ( $_POST ['top'] ) || $_POST ['top'] == "") {
    echo "Top of image crop wasn't provided";
    exit ();
} else {
    $top = ( int ) $_POST ['top'];
}
if (! isset ( $_POST ['bottom'] ) || $_POST ['bottom'] == "") {
    echo "Bottom of image crop wasn't provided";
    exit ();
} else {
    $bottom = ( int ) $_POST ['bottom'];
}
$height = $bottom - $top;

// ensure we have no 'red' leaking through
if ($top < 0) {
    $bottom = $bottom - $top;
    $top = 0;
}

// fix our image with it's width
system ( "mogrify -resize ${maxWidth}x \"$image\"" );
system ( "mogrify -density 72 \"$image\"" );

// verify that our image can fit in the specified crop
if (getimagesize ( $image ) [1] < ($height - 1)) {
    echo "Cropped image is smaller than the required image";
    exit ();
}

// crop our image
system ( "mogrify -crop ${maxWidth}x${height}+0+${top} \"$image\"" );

// rename the image
$filePath = dirname ( $image );
$fileName = basename ( $image );
rename ( $image, "$filePath/" . substr ( $fileName, 4 ) );

exit ();