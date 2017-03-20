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

$id;
if (isset ( $_POST ['id'] ) && $_POST ['id'] != "") {
    $id = intval ( $_POST ['id'] );
} else {
    echo "Id is not provided";
    $conn->disconnect ();
    exit ();
}
$sql = "SELECT * FROM contracts WHERE id = $id;";
$contract_info = mysqli_fetch_assoc ( mysqli_query ( $conn->db, $sql ) );
if (! $contract_info ['id']) {
    echo "That ID doesn't match any contracts";
    $conn->disconnect ();
    exit ();
}

$name;
if (isset ( $_POST ['name'] ) && $_POST ['name'] != "") {
    $name = mysqli_real_escape_string ( $conn->db, $_POST ['name'] );
} else {
    echo "Name is not provided";
    $conn->disconnect ();
    exit ();
}

$address;
if (isset ( $_POST ['address'] ) && $_POST ['address'] != "") {
    $address = mysqli_real_escape_string ( $conn->db, $_POST ['address'] );
} else {
    echo "Address is not provided";
    $conn->disconnect ();
    exit ();
}

$number;
if (isset ( $_POST ['number'] ) && $_POST ['number'] != "") {
    $number = mysqli_real_escape_string ( $conn->db, $_POST ['number'] );
} else {
    echo "Number is not provided";
    $conn->disconnect ();
    exit ();
}

$email;
if (isset ( $_POST ['email'] ) && $_POST ['email'] != "") {
    $email = mysqli_real_escape_string ( $conn->db, $_POST ['email'] );
} else {
    echo "Email is not provided";
    $conn->disconnect ();
    exit ();
}

$signature;
if (isset ( $_POST ['signature'] ) && $_POST ['signature'] != "") {
    $signature = mysqli_real_escape_string ( $conn->db, $_POST ['signature'] );
} else {
    echo "Signature is not provided";
    $conn->disconnect ();
    exit ();
}

$initial;
if (isset ( $_POST ['initial'] ) && $_POST ['initial'] != "") {
    $initial = mysqli_real_escape_string ( $conn->db, $_POST ['initial'] );
} else {
    echo "Initial is not provided";
    $conn->disconnect ();
    exit ();
}

$content;
if (isset ( $_POST ['content'] ) && $_POST ['content'] != "") {
    $content = mysqli_real_escape_string ( $conn->db, $_POST ['content'] );
} else {
    echo "Content is not provided";
    $conn->disconnect ();
    exit ();
}

exit ();

$sql = "UPDATE `contracts` (`link`, `name`, `address`, `number`, `email`, `signature`, `initial`, `content`)
        VALUES ('','$name','$address','$number','$email','$signature','$initial','$content');";
mysqli_query ( $conn->db, $sql );

//TODO - create/save pdf
//TODO - email out pdf

$conn->disconnect ();
exit ();