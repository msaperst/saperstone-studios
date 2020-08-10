<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

if (isset ($_POST ['id']) && $_POST ['id'] != "") {
    $id = ( int )$_POST ['id'];
} else {
    echo "Id is not provided";
    $conn->disconnect();
    exit ();
}

if (isset ($_POST ['size']) && $_POST ['size'] != "") {
    $size = $sql->escapeString($_POST ['size']);
} else {
    echo "Size is not provided";
    $conn->disconnect();
    exit ();
}

if (isset ($_POST ['cost']) && $_POST ['cost'] != "") {
    $cost = floatval($_POST ['cost']);
} else {
    echo "Cost is not provided";
    $conn->disconnect();
    exit ();
}

if (isset ($_POST ['price']) && $_POST ['price'] != "") {
    $price = floatval($_POST ['price']);
} else {
    echo "Price is not provided";
    $conn->disconnect();
    exit ();
}

$sql = "UPDATE `products` SET `size` = '$size', `cost` = '$cost' , `price` = '$price' WHERE `products`.`id` = $id;";
mysqli_query($conn->db, $sql);

$conn->disconnect();
exit ();