<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$id;
if (isset ( $_POST ['id'] )) {
    $id = ( int ) $_POST ['id'];
} else {
    echo "ID is not provided";
    $conn->disconnect ();
    exit ();
}
$sql = "SELECT * FROM contracts WHERE id = $id;";
$contract_info = $sql->getRow( $sql );
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
    $type = $sql->escapeString( $_POST ['type'] );
} else {
    echo "Type is not provided";
    $conn->disconnect ();
    exit ();
}

$name;
if (isset ( $_POST ['name'] ) && $_POST ['name'] != "") {
    $name = $sql->escapeString( $_POST ['name'] );
} else {
    echo "Name is not provided";
    $conn->disconnect ();
    exit ();
}

$session;
if (isset ( $_POST ['session'] ) && $_POST ['session'] != "") {
    $session = $sql->escapeString( $_POST ['session'] );
} else {
    echo "Session is not provided";
    $conn->disconnect ();
    exit ();
}

$content;
if (isset ( $_POST ['content'] ) && $_POST ['content'] != "") {
    $content = $sql->escapeString( $_POST ['content'] );
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
    $address = "'" . $sql->escapeString( $_POST ['address'] ) . "'";
}
if (isset ( $_POST ['number'] ) && $_POST ['number'] != "") {
    $number = "'" . $sql->escapeString( $_POST ['number'] ) . "'";
}
if (isset ( $_POST ['email'] ) && $_POST ['email'] != "") {
    $email = "'" . $sql->escapeString( $_POST ['email'] ) . "'";
}
if (isset ( $_POST ['date'] ) && $_POST ['date'] != "") {
    $date = "'" . $sql->escapeString( $_POST ['date'] ) . "'";
}
if (isset ( $_POST ['location'] ) && $_POST ['location'] != "") {
    $location = "'" . $sql->escapeString( $_POST ['location'] ) . "'";
}
if (isset ( $_POST ['details'] ) && $_POST ['details'] != "") {
    $details = "'" . $sql->escapeString( $_POST ['details'] ) . "'";
}
if (isset ( $_POST ['invoice'] ) && $_POST ['invoice'] != "") {
    $invoice = "'" . $sql->escapeString( $_POST ['invoice'] ) . "'";
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
            $item = "'" . $sql->escapeString( $lineItem ['item'] ) . "'";
        }
        if (isset ( $lineItem ['unit'] ) && $lineItem ['unit'] != "") {
            $unit = "'" . $sql->escapeString( $lineItem ['unit'] ) . "'";
        }
        $sql = "INSERT INTO `contract_line_items` (`contract`, `item`, `amount`, `unit`) 
                VALUES ($id, $item, $amount, $unit);";
        mysqli_query ( $conn->db, $sql );
    }
}

$conn->disconnect ();
exit ();