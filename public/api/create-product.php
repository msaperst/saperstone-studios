<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$category = $api->retrievePostString('category', 'Product category');
if( is_array( $category ) ) {
    echo $category['error'];
    exit();
}
$enums = $sql->getEnumValues( 'product_types', 'category' );
if (! in_array( $category, $enums ) ) {
    echo "Product category is not valid";
    $sql->disconnect ();
    exit ();
}

$name = $api->retrievePostString('name', 'Product name');
if( is_array( $name ) ) {
    echo $name['error'];
    exit();
}

echo $sql->executeStatement( "INSERT INTO `product_types` (`id`, `category`, `name`) VALUES (NULL, '$category', '$name');" );
$sql->disconnect ();
exit ();
?>