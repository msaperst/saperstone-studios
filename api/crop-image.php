<?php
session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
$user = new user ();

if ($user->getRole () != "admin") {
    header ( 'HTTP/1.0 401 Unauthorized' );
    exit ();
}

$image = $maxWidth = $top = $bottom = "";
if (! isset ( $_POST ['image'] ) || $_POST ['image'] == "") {
    echo "Image to crop wasn't provided";
    exit ();
} else {
    $image = $_POST ['image'];
}
if (! file_exists ( $image )) {
    echo "Image to crop doesn't exist";
    exit ();
}
if (! isset ( $_POST ['max-width'] ) || $_POST ['max-width'] == "") {
    echo "Max-width of image crop wasn't provided";
    exit ();
} else {
    $maxWidth = $_POST ['max-width'];
}
if (! isset ( $_POST ['top'] ) || $_POST ['top'] == "") {
    echo "Top of image crop wasn't provided";
    exit ();
} else {
    $top = $_POST ['top'];
}
if (! isset ( $_POST ['bottom'] ) || $_POST ['bottom'] == "") {
    echo "Bottom of image crop wasn't provided";
    exit ();
} else {
    $bottom = $_POST ['bottom'];
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
if (getimagesize ( $image ) [1] < ($height-1)) {
    echo "Cropped image is smaller than the required image";
    exit ();
}

// crop our image
system ( "mogrify -crop ${maxWidth}x${height}+0+${top} \"$image\"" );

// add our watermark
// system ( "composite -gravity southwest -geometry 200x150+0+0 ../img/watermark.png \"$image\" \"$image\"" );

// rename the image
$filePath = dirname ( $image );
$fileName = basename ( $image );
rename ( $image, "$filePath/" . substr ( $fileName, 4 ) );

exit ();