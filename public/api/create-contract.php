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

$type;
if (isset ( $_POST ['type'] ) && $_POST ['type'] != "") {
    $type = $sql->escapeString( $_POST ['type'] );
    //TODO - need check for valid type
} else {
    if (! isset ( $_POST ['type'] )) {
        echo "Contract type is required";
    } elseif ($_POST ['type'] == "") {
        echo "Contract type can not be blank";
    } else {
        echo "Some other contract type error occurred";
    }
    $sql->disconnect ();
    exit ();
}


$name;
if (isset ( $_POST ['name'] ) && $_POST ['name'] != "") {
    $name = $sql->escapeString( $_POST ['name'] );
} else {
    if (! isset ( $_POST ['name'] )) {
        echo "Contract name is required";
    } elseif ($_POST ['name'] == "") {
        echo "Contract name can not be blank";
    } else {
        echo "Some other contract name error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$session;
if (isset ( $_POST ['session'] ) && $_POST ['session'] != "") {
    $session = $sql->escapeString( $_POST ['session'] );
} else {
    if (! isset ( $_POST ['session'] )) {
        echo "Contract session is required";
    } elseif ($_POST ['session'] == "") {
        echo "Contract session can not be blank";
    } else {
        echo "Some other contract session error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$content;
if (isset ( $_POST ['content'] ) && $_POST ['content'] != "") {
    $content = $sql->escapeString( $_POST ['content'] );
} else {
    if (! isset ( $_POST ['content'] )) {
        echo "Contract content is required";
    } elseif ($_POST ['content'] == "") {
        echo "Contract content can not be blank";
    } else {
        echo "Some other contract content error occurred";
    }
    $sql->disconnect ();
    exit ();
}

$amount = $deposit = '0';
if (isset ( $_POST ['amount'] ) && $_POST ['amount'] != "") {
    $amount = floatval( str_replace( '$', '', $_POST ['amount'] ) );
}
if (isset ( $_POST ['deposit'] ) && $_POST ['deposit'] != "") {
    $deposit = floatval( str_replace( '$', '', $_POST ['deposit'] ) );
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
    $date = $sql->escapeString( $_POST ['date'] );
    $format = 'Y-m-d';
    $d = DateTime::createFromFormat($format, $date);
    if ( ! ($d && $d->format($format) === $date ) ) {
        echo "Contract date is not the correct format";
        $sql->disconnect ();
        exit ();
    }
    $date = "'" . $date . "'";
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

$last_id = $sql->executeStatement( "INSERT INTO `contracts` (`link`, `type`, `name`, `address`, `number`, `email`, `date`, `location`,
        `session`, `details`, `amount`, `deposit`, `invoice`, `content`) 
        VALUES ('$link','$type','$name',$address,$number,$email,$date,$location,'$session',$details,
        $amount,$deposit,$invoice,'$content');" );
$link = md5 ( $last_id . $type . $name . $session );
$sql->executeStatement( "UPDATE `contracts` SET `link` = '$link' WHERE `id` = $last_id;" );

if (isset ( $_POST ['lineItems'] ) && ! empty( $_POST ['lineItems'] ) ) {
    foreach ( $_POST ['lineItems'] as $lineItem ) {
        $amount = floatval( str_replace( '$', '', $lineItem ['amount'] ) );
        $item = $unit = 'NULL';
        if (isset ( $lineItem ['item'] ) && $lineItem ['item'] != "") {
            $item = "'" . $sql->escapeString( $lineItem ['item'] ) . "'";
        }
        if (isset ( $lineItem ['unit'] ) && $lineItem ['unit'] != "") {
            $unit = "'" . $sql->escapeString( $lineItem ['unit'] ) . "'";
        }
        $sql->executeStatement( "INSERT INTO `contract_line_items` (`contract`, `item`, `amount`, `unit`)
                VALUES ($last_id, $item, $amount, $unit);" );
    }
}
echo $last_id;
$sql->disconnect ();
exit ();
?>