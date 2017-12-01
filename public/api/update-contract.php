<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$user = new User ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

$id;
if (isset ( $_POST ['id'] )) {
    $id = ( int ) $_POST ['id'];
} else {
    echo "ID is not provided";
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
if (! $contract_info ['signature'] != "NULL") {
    echo "That contract has already been signed, it can't be updated";
    $conn->disconnect ();
    exit ();
}

$type;
if (isset ( $_POST ['type'] ) && $_POST ['type'] != "") {
    $type = mysqli_real_escape_string ( $conn->db, $_POST ['type'] );
} else {
    echo "Type is not provided";
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

$session;
if (isset ( $_POST ['session'] ) && $_POST ['session'] != "") {
    $session = mysqli_real_escape_string ( $conn->db, $_POST ['session'] );
} else {
    echo "Session is not provided";
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

$amount = $deposit = '0';
if (isset ( $_POST ['amount'] ) && $_POST ['amount'] != "") {
    $amount = doubleval ( $_POST ['amount'] );
}
if (isset ( $_POST ['deposit'] ) && $_POST ['deposit'] != "") {
    $deposit = doubleval ( $_POST ['deposit'] );
}

$address = $number = $email = $date = $location = $details = $invoice = 'NULL';
if (isset ( $_POST ['address'] ) && $_POST ['address'] != "") {
    $address = "'" . mysqli_real_escape_string ( $conn->db, $_POST ['address'] ) . "'";
}
if (isset ( $_POST ['number'] ) && $_POST ['number'] != "") {
    $number = "'" . mysqli_real_escape_string ( $conn->db, $_POST ['number'] ) . "'";
}
if (isset ( $_POST ['email'] ) && $_POST ['email'] != "") {
    $email = "'" . mysqli_real_escape_string ( $conn->db, $_POST ['email'] ) . "'";
}
if (isset ( $_POST ['date'] ) && $_POST ['date'] != "") {
    $date = "'" . mysqli_real_escape_string ( $conn->db, $_POST ['date'] ) . "'";
}
if (isset ( $_POST ['location'] ) && $_POST ['location'] != "") {
    $location = "'" . mysqli_real_escape_string ( $conn->db, $_POST ['location'] ) . "'";
}
if (isset ( $_POST ['details'] ) && $_POST ['details'] != "") {
    $details = "'" . mysqli_real_escape_string ( $conn->db, $_POST ['details'] ) . "'";
}
if (isset ( $_POST ['invoice'] ) && $_POST ['invoice'] != "") {
    $invoice = "'" . mysqli_real_escape_string ( $conn->db, $_POST ['invoice'] ) . "'";
}

$sql = "UPDATE `contracts` SET `type` = '$type', `name` = '$name', `address` = $address, `number` = $number, 
        `email` = $email, `date` = $date, `location` = $location, `session` = '$session', `details` = $details, 
        `amount` = $amount, `deposit` = $deposit, `invoice` = $invoice, `content` = '$content' WHERE `id` = $id;";
mysqli_query ( $conn->db, $sql );

$sql = "DELETE FROM `contract_line_items` WHERE `contract` = $id;";
mysqli_query ( $conn->db, $sql );
if (isset ( $_POST ['lineItems'] ) && $_POST ['lineItems'] != "") {
    $lineItems = $_POST ['lineItems'];
    foreach ( $lineItems as $lineItem ) {
        $amount = doubleval ( $lineItem ['amount'] );
        $item = $unit = 'NULL';
        if (isset ( $lineItem ['item'] ) && $lineItem ['item'] != "") {
            $item = "'" . mysqli_real_escape_string ( $conn->db, $lineItem ['item'] ) . "'";
        }
        if (isset ( $lineItem ['unit'] ) && $lineItem ['unit'] != "") {
            $unit = "'" . mysqli_real_escape_string ( $conn->db, $lineItem ['unit'] ) . "'";
        }
        $sql = "INSERT INTO `contract_line_items` (`contract`, `item`, `amount`, `unit`) 
                VALUES ($id, $item, $amount, $unit);";
        mysqli_query ( $conn->db, $sql );
    }
}

$conn->disconnect ();
exit ();