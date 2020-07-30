<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
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

$title = "";
if (isset ( $_POST ['title'] ) && $_POST ['title'] != "") {
    $title = $sql->escapeString( $_POST ['title'] );
} else {
    if (! isset ( $_POST ['title'] )) {
        echo "Blog title is required";
    } elseif ($_POST ['title'] == "") {
        echo "Blog title can not be blank";
    } else {
        echo "Some other blog title error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$date = "";
if (isset ( $_POST ['date'] ) && $_POST ['date'] != "") {
    $date = $sql->escapeString( $_POST ['date'] );
    $format = 'Y-m-d';
    $d = DateTime::createFromFormat($format, $date);
    if ( ! ($d && $d->format($format) === $date ) ) {
        echo "Blog date is not the correct format";
        $sql->disconnect ();
        exit ();
    }
} else {
    if (! isset ( $_POST ['date'] )) {
        echo "Blog date is required";
    } elseif ($_POST ['date'] == "") {
        echo "Blog date can not be blank";
    } else {
        echo "Some other blog date error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$previewImage = "";
if (isset ( $_POST ['preview'] ['img'] ) && $_POST ['preview'] ['img'] != "") {
    $previewImage = $sql->escapeString( $_POST ['preview'] ['img'] );
} else {
    if (! isset ( $_POST ['preview'] ['img'] )) {
        echo "Blog preview image is required";
    } elseif ($_POST ['preview'] ['img'] == "") {
        echo "Blog preview image can not be blank";
    } else {
        echo "Some other blog preview image error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$previewOffset = 0;
if (isset ( $_POST ['preview'] ['offset'] )) {
    $previewOffset = ( int ) $_POST ['preview'] ['offset'];
}

// quick check of our content
if (isset ( $_POST ['content'] ) && ! empty( $_POST ['content'] ) ) {
    foreach ( $_POST ['content'] as $content ) {
        if ($content ['type'] != "text" && $content ['type'] != "images") {
            echo "Unexpected content present, please consult the webmaster";
            $sql->disconnect ();
            exit ();
        }
    }
} else {
    if (! isset ( $_POST ['content'] ) ) {
        echo "Blog content is required";
    } elseif ( empty ( $_POST ['content'] ) ) {
        echo "Blog content can not be empty";
    } else {
        echo "Some other blog content error occurred";
    }
    $sql->disconnect ();
    exit ();
}

// move and resize our preview image
$storage_dir = "../blog/posts/" . str_replace ( "-", "/", $date );
if (! is_dir ( $storage_dir )) {
    mkdir ( $storage_dir, 0755, true );
}
copy ( "$previewImage", "$storage_dir/preview_image.jpg" );
system ( "mogrify -resize 360x \"$storage_dir/preview_image.jpg\"" );
system ( "mogrify -density 72 \"$storage_dir/preview_image.jpg\"" );

// write our initial blog information
$blog_id = $sql->executeStatement( "INSERT INTO `blog_details` ( `title`, `date`, `preview`, `offset` ) VALUES ('$title', '$date', '$storage_dir/preview_image.jpg', '$previewOffset' );" );
// update our preview image with the blog post id
rename ( "$storage_dir/preview_image.jpg", "$storage_dir/preview_image-$blog_id.jpg" );
$sql->executeStatement( "UPDATE `blog_details` SET `preview` = '$storage_dir/preview_image-$blog_id.jpg' WHERE `id` = $blog_id;" );

// enter our tag information
if (isset ( $_POST ['tags'] )) {
    foreach ( $_POST ['tags'] as $tag ) {
        $tag = ( int ) $tag;
        $sql->executeStatement( "INSERT INTO `blog_tags` ( `blog`, `tag` ) VALUES ('$blog_id', '$tag');" );
    }
}

// get down all of our content
foreach ( $_POST ['content'] as $content ) {
    if ($content ['type'] == "text") {
        $text = $sql->escapeString( $content ['text'] );
        $group = ( int ) $content ['group'];
        $sql->executeStatement( "INSERT INTO `blog_texts` ( `blog`, `contentGroup`, `text` ) VALUES ('$blog_id', '$group', '$text');" );
    } elseif ($content ['type'] == "images") {
        $group = ( int ) $content ['group'];
        foreach ( $content ['imgs'] as $img ) {
            $location = $sql->escapeString( $img ['location'] );
            $top = ( int ) $img ['top'];
            $left = ( int ) $img ['left'];
            $width = ( int ) $img ['width'];
            $height = ( int ) $img ['height'];
            
            rename ( "$location", "$storage_dir/" . basename ( $location ) );
            system ( "mogrify -resize ${width}x \"$storage_dir/" . basename ( $location ) . "\"" );
            system ( "mogrify -density 72 \"$storage_dir/" . basename ( $location ) . "\"" );
            
            $sql->executeStatement( "INSERT INTO `blog_images` ( `blog`, `contentGroup`, `location`, `top`, `left`, `width`, `height` ) VALUES ('$blog_id', '$group', '$storage_dir/" . basename ( $location ) . "', '$top', '$left', '$width', '$height');" );
        }
    } else {
        echo "You provided some bad content";
        $sql->disconnect ();
        exit ();
    }
}

echo $blog_id;
$sql->disconnect ();
exit ();