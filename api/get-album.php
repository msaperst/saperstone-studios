<?php
require_once "../php/sql.php";

if (isset ( $_GET ['id'] )) {
    $id = $_GET ['id'];
} else {
    echo "ID is not provided";
    exit ();
}

$sql = "SELECT albums.*, COUNT(album_images.album) AS 'images' FROM albums LEFT JOIN album_images ON albums.id = album_images.album WHERE albums.id = $id GROUP BY albums.id;";
$result = mysqli_query ( $db, $sql );
$r = mysqli_fetch_assoc ( $result );
$r ['date'] = substr ( $r ['date'], 0, 10 );
if ($r ['code'] == NULL) {
    $r ['code'] = "";
}
echo json_encode ( $r );
exit ();

?>