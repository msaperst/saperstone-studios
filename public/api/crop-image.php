<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
$sql = new Sql ();
$user = new User ($sql);

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "You do not have appropriate rights to perform this action";
    }
    $sql->disconnect ();
    exit ();
}

$image = $maxWidth = $top = $bottom = "";
if (isset ( $_POST ['image'] ) && $_POST ['image'] != "" && file_exists( $_POST['image'] ) ) {
    $image = $sql->escapeString( $_POST ['image'] );
} else {
    if (! isset ( $_POST ['image'] )) {
        echo "Image is required";
    } elseif ($_POST ['image'] == "") {
        echo "Image can not be blank";
    } elseif ( ! file_exists ( $_POST ['image'] ) ) {
        echo "Image does not exist";
    } else {
        echo "Some other image error occurred";
    }
    $sql->disconnect ();
    exit ();
}

if (isset ( $_POST ['max-width'] ) && $_POST ['max-width'] != "") {
    $maxWidth = ( int ) $_POST ['max-width'];
} else {
    if (! isset ( $_POST ['max-width'] )) {
        echo "Image max-width is required";
    } elseif ($_POST ['max-width'] == "") {
        echo "Image max-width can not be blank";
    } else {
        echo "Some other image max-width error occurred";
    }
    $sql->disconnect ();
    exit ();
}

if (isset ( $_POST ['top'] ) && $_POST ['top'] != "") {
    $top = ( int ) $_POST ['top'];
} else {
    if (! isset ( $_POST ['top'] )) {
        echo "Image top is required";
    } elseif ($_POST ['top'] == "") {
        echo "Image top can not be blank";
    } else {
        echo "Some other image top error occurred";
    }
    $sql->disconnect ();
    exit ();
}

if (isset ( $_POST ['bottom'] ) && $_POST ['bottom'] != "") {
    $bottom = ( int ) $_POST ['bottom'];
} else {
    if (! isset ( $_POST ['bottom'] )) {
        echo "Image bottom is required";
    } elseif ($_POST ['bottom'] == "") {
        echo "Image bottom can not be blank";
    } else {
        echo "Some other image bottom error occurred";
    }
    $sql->disconnect ();
    exit ();
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
    unlink( $image );
    $sql->disconnect ();
    exit ();
}

// crop our image
system ( "mogrify -crop ${maxWidth}x${height}+0+${top} \"$image\"" );

// rename the image
$filePath = dirname ( $image );
$fileName = basename ( $image );
rename ( $image, "$filePath/" . substr ( $fileName, 4 ) );

$sql->disconnect ();
exit ();
?>