<?php
require_once "../php/sql.php";
$conn = new sql ();
$conn->connect ();

if (isset ( $_GET ['id'] )) {
    $id = $_GET ['id'];
} else {
    echo "ID is not provided";
    $conn->disconnect ();
    exit ();
}

$sql = "SELECT galleries.*, COUNT(gallery_images.gallery) AS 'images' FROM galleries LEFT JOIN gallery_images ON galleries.id = gallery_images.gallery WHERE galleries.id = $id GROUP BY galleries.id;";
$result = mysqli_query ( $conn->db, $sql );
echo json_encode ( mysqli_fetch_assoc ( $result ) );

$conn->disconnect ();
exit ();