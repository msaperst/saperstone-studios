<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$conn = new Sql ();
$conn->connect ();

$user = new User ();

$post = "";
$name = "";
$email = "";
$message = "";

if (isset ( $_POST ['post'] ) && $_POST ['post'] != "") {
    $post = ( int ) $_POST ['post'];
} else {
    if (! isset ( $_POST ['post'] )) {
        echo "Post id is required!";
    } elseif ($_POST ['post'] == "") {
        echo "Post id cannot be blank!";
    } else {
        echo "Some other Post id error occurred!";
    }
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT * FROM blog_details WHERE id = $post;";
$blog_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $blog_info ['id']) {
    echo "That ID doesn't match any posts";
    $conn->disconnect ();
    exit ();
}

if (isset ( $_POST ['name'] ) && $_POST ['name'] != "") {
    $name = mysqli_real_escape_string ( $conn->db, $_POST ['name'] );
}
if (isset ( $_POST ['email'] ) && $_POST ['email'] != "") {
    $email = mysqli_real_escape_string ( $conn->db, $_POST ['email'] );
}
if (isset ( $_POST ['message'] ) && $_POST ['message'] != "") {
    $message = mysqli_real_escape_string ( $conn->db, $_POST ['message'] );
} else {
    echo "Message is required!";
    $conn->disconnect ();
    exit ();
}

if ($user->getId () != "") {
    $user = "'" . $user->getId () . "'";
} else {
    $user = 'NULL';
}

$sql = "INSERT INTO blog_comments ( blog, user, name, date, ip, email, comment ) VALUES ($post, $user, '$name', CURRENT_TIMESTAMP, '" . getClientIP . "', '$email', '$message' );";
mysqli_query ( $conn->db, $sql );
$last_id = mysqli_insert_id ( $conn->db );

echo $last_id;

$conn->disconnect ();
exit ();