<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$systemUser = new CurrentUser ($sql);
$api = new Api ($sql, $systemUser);

$api->forceAdmin();

if (isset ($_POST ['id']) && $_POST ['id'] != "") {
    $id = intval($_POST ['id']);
} else {
    echo "Id is not provided";
    $conn->disconnect();
    exit ();
}

if (isset ($_POST ['name']) && $_POST ['name'] != "") {
    $name = $sql->escapeString($_POST ['name']);
} else {
    echo "Name is not provided";
    $conn->disconnect();
    exit ();
}

$sql = "UPDATE `product_types` SET `name` = '$name' WHERE `product_types`.`id` = $id;";
mysqli_query($conn->db, $sql);

$conn->disconnect();
exit ();