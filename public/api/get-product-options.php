<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$systemUser = User::fromSystem();
$api = new Api ();

$type = $api->retrieveGetInt('type', 'Product type');
if (is_array($type)) {
    echo $type['error'];
    exit();
}
$sql = new Sql();
$product_info = $sql->getRow("SELECT * FROM product_types WHERE id = $type;");
if (!$product_info ['id']) {
    echo "Product type does not match any products";
    $sql->disconnect();
    exit ();
}

$options = array_column($sql->getRows("SELECT opt FROM product_options WHERE product_type = '$type';"), 'opt');
echo json_encode($options);
$sql->disconnect();
exit ();