<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$sql = new Sql ();

$user = new User ();

// only admin users can make updates
if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

$id = "";
if (isset ( $_POST ['gallery'] ) && $_POST ['gallery'] != "") {
    $id = ( int ) $_POST ['gallery'];
} else {
    if (! isset ( $_POST ['gallery'] )) {
        echo "Gallery ID is required!";
    } elseif ($_POST ['gallery'] != "") {
        echo "Gallery ID cannot be blank!";
    } else {
        echo "Some other Gallery ID error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM galleries WHERE id = '$id';";
$gallery_info = $sql->getRow( $sql );
if (! $gallery_info ['title']) {
    echo "The ID '$id' doesn't match any galleries";
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT MAX(sequence) as next FROM gallery_images WHERE gallery = '$id';";
$result = mysqli_query ( $conn->db, $sql );
$gallery_image_info = mysqli_fetch_assoc ( $result );
$next_seq = $gallery_image_info ['next'];
if (is_numeric ( $next_seq )) {
    $next_seq ++;
} else {
    $next_seq = 0;
}

$location = "";
while ( $gallery_info ['parent'] != NULL ) {
    $location = str_replace ( " ", "-", $gallery_info ['title'] ) . "/$location";
    $sql = "SELECT * FROM galleries WHERE id = '" . $gallery_info ['parent'] . "';";
    $gallery_info = $sql->getRow( $sql );
}
$location = str_replace ( " ", "-", $gallery_info ['title'] ) . "/$location";
if ( $location == str_replace ( " ", "-", $gallery_info ['title'] ) . "/" ) {
    $location = "img/main/" . $location;
}
$location = strtolower( $location );
$location = preg_replace( '/^commercial/', 'commercial/img', $location );
$location = preg_replace( '/^portrait/', 'portrait/img', $location );
$location = preg_replace( '/^wedding/', 'wedding/img', $location );
$output_dir = "../$location";
if (! is_dir ( $output_dir )) {
    mkdir ( $output_dir, 0755, true );
}

if (isset ( $_FILES ["myfile"] )) {
    $ret = array ();
    
    $error = $_FILES ["myfile"] ["error"];
    // You need to handle both cases
    // If Any browser does not support serializing of multiple files using FormData()
    // single file
    if (! is_array ( $_FILES ["myfile"] ["name"] )) {
        $fileName = $_FILES ["myfile"] ["name"];
        $status = move_uploaded_file ( $_FILES ["myfile"] ["tmp_name"], $output_dir . $fileName );
        if (! $status) {
            echo json_encode ( "Some issue occurred saving the file" . print_r ( $_FILES ["myfile"] ) );
            exit ( 1 );
        }
        $ret [] = $fileName;
        // Multiple files, file[]
    } else {
        $fileCount = count ( $_FILES ["myfile"] ["name"] );
        for($i = 0; $i < $fileCount; $i ++) {
            $fileName = $_FILES ["myfile"] ["name"] [$i];
            move_uploaded_file ( $_FILES ["myfile"] ["tmp_name"] [$i], $output_dir . $fileName );
            $ret [] = $fileName;
        }
    }
    
    // add our uploaded files to
    $response = array ();
    foreach ( $ret as $fileName ) {
        $size = getimagesize ( $output_dir . $fileName );
        if ($size [0] < 1140) {
            echo json_encode ( "This image doesn't meet the minimum width requirements of 900px<br/>The image is " . $size [0] . " x " . $size [1] );
            unlink ( $output_dir . $fileName );
            exit ( 1 );
        } elseif ($size [1] < 760) {
            echo json_encode ( "This image doesn't meet the minimum height requirements of 600px<br/>The image is " . $size [0] . " x " . $size [1] );
            unlink ( $output_dir . $fileName );
            exit ( 1 );
        } else {
            system ( "mogrify -resize 1140x760 \"$output_dir$fileName\"" );
            system ( "mogrify -density 72 \"$output_dir$fileName\"" );
            $size = getimagesize ( $output_dir . $fileName );
        }
        $response [$next_seq] = $fileName;
        $sql = "INSERT INTO `gallery_images` (`gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`) VALUES ('$id', '$fileName', '$next_seq', '', '/$location$fileName', '" . $size [0] . "', '" . $size [1] . "');";
        mysqli_query ( $conn->db, $sql );
        $next_seq ++;
    }
    
    echo json_encode ( $response );
}

$conn->disconnect ();
exit ();