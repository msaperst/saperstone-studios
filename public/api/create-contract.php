<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$api->forceAdmin();

$type = $api->retrievePostString('type', 'Contract type');
if (is_array($type)) {
    echo $type['error'];
    exit();
}

$name = $api->retrievePostString('name', 'Contract name');
if (is_array($name)) {
    echo $name['error'];
    exit();
}

$session = $api->retrievePostString('session', 'Contract session');
if (is_array($session)) {
    echo $session['error'];
    exit();
}

$content = $api->retrievePostString('content', 'Contract content');
if (is_array($content)) {
    echo $content['error'];
    exit();
}

$amount = $deposit = '0';
if (isset ($_POST ['amount']) && $_POST ['amount'] != "") {
    $amount = floatval(str_replace('$', '', $_POST ['amount']));
}
if (isset ($_POST ['deposit']) && $_POST ['deposit'] != "") {
    $deposit = floatval(str_replace('$', '', $_POST ['deposit']));
}

$sql = new Sql ();
$address = $number = $email = $date = $location = $details = $invoice = 'NULL';
if (isset ($_POST ['address']) && $_POST ['address'] != "") {
    $address = "'" . $sql->escapeString($_POST ['address']) . "'";
}
if (isset ($_POST ['number']) && $_POST ['number'] != "") {
    $number = "'" . $sql->escapeString($_POST ['number']) . "'";
}
if (isset ($_POST ['email']) && $_POST ['email'] != "") {
    $email = "'" . $sql->escapeString($_POST ['email']) . "'";
}
if (isset ($_POST ['date']) && $_POST ['date'] != "") {
    $date = $sql->escapeString($_POST ['date']);
    $format = 'Y-m-d';
    $d = DateTime::createFromFormat($format, $date);
    if (!($d && $d->format($format) === $date)) {
        echo "Contract date is not the correct format";
        $sql->disconnect();
        exit ();
    }
    $date = "'" . $date . "'";
}
if (isset ($_POST ['location']) && $_POST ['location'] != "") {
    $location = "'" . $sql->escapeString($_POST ['location']) . "'";
}
if (isset ($_POST ['details']) && $_POST ['details'] != "") {
    $details = "'" . $sql->escapeString($_POST ['details']) . "'";
}
if (isset ($_POST ['invoice']) && $_POST ['invoice'] != "") {
    $invoice = "'" . $sql->escapeString($_POST ['invoice']) . "'";
}

$lastId = $sql->executeStatement("INSERT INTO `contracts` (`link`, `type`, `name`, `address`, `number`, `email`, `date`, `location`,
        `session`, `details`, `amount`, `deposit`, `invoice`, `content`) 
        VALUES ('','$type','$name',$address,$number,$email,$date,$location,'$session',$details,
        $amount,$deposit,$invoice,'$content');");
$link = md5($lastId . $type . $name . $session);
$sql->executeStatement("UPDATE `contracts` SET `link` = '$link' WHERE `id` = $lastId;");

if (isset ($_POST ['lineItems']) && !empty($_POST ['lineItems'])) {
    foreach ($_POST ['lineItems'] as $lineItem) {
        $amount = floatval(str_replace('$', '', $lineItem ['amount']));
        $item = $unit = 'NULL';
        if (isset ($lineItem ['item']) && $lineItem ['item'] != "") {
            $item = "'" . $sql->escapeString($lineItem ['item']) . "'";
        }
        if (isset ($lineItem ['unit']) && $lineItem ['unit'] != "") {
            $unit = "'" . $sql->escapeString($lineItem ['unit']) . "'";
        }
        $sql->executeStatement("INSERT INTO `contract_line_items` (`contract`, `item`, `amount`, `unit`)
                VALUES ($lastId, $item, $amount, $unit);");
    }
}
echo $lastId;
$sql->disconnect();
exit ();