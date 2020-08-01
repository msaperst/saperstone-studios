<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/api.php";
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$response = [ ];
$start = 0;
$howMany = 999999999999999999;

if (isset ( $_GET ['start'] )) {
    $start = ( int ) $_GET ['start'];
}
if (isset ( $_GET ['howMany'] )) {
    $howMany = ( int ) $_GET ['howMany'];
}
if (isset ( $_GET ['searchTerm'] )) {
    $search = $sql->escapeString( $_GET ['searchTerm'] );
} else {
    exit ();
}

$sql = "SELECT * FROM (SELECT id AS blog FROM `blog_details` WHERE ( `title` LIKE '%$search%' OR `safe_title` LIKE '%$search%' ) AND `active` UNION ALL SELECT blog FROM `blog_texts` WHERE `text` LIKE '%$search%') AS x GROUP BY `blog` DESC LIMIT $start,$howMany;";
$result = mysqli_query ( $conn->db, $sql );
while ( $r = mysqli_fetch_assoc ( $result ) ) {
    $sql = "SELECT * FROM `blog_details` WHERE `id` = '" . $r ['blog'] . "';";
    $response [] = $sql->getRow( $sql );
}
echo json_encode ( $response );

$conn->disconnect ();
exit ();
