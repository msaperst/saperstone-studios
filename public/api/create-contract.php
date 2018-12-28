<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$conn = new Sql ();
$conn->connect ();

$user = new User ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
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

$link = "";
$sql = "INSERT INTO `contracts` (`link`, `type`, `name`, `address`, `number`, `email`, `date`, `location`, 
        `session`, `details`, `amount`, `deposit`, `invoice`, `content`) 
        VALUES ('$link','$type','$name',$address,$number,$email,$date,$location,'$session',$details,
        $amount,$deposit,$invoice,'$content');";
mysqli_query ( $conn->db, $sql );
$last_id = mysqli_insert_id ( $conn->db );
$link = md5 ( $last_id . $type . $name . $session );
$sql = "UPDATE `contracts` SET `link` = '$link' WHERE `id` = $last_id;";
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
                VALUES ($last_id, $item, $amount, $unit);";
        mysqli_query ( $conn->db, $sql );
    }
}

$conn->disconnect ();
exit ();