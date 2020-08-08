<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$sql = "SELECT * FROM users;";
$result = mysqli_query($conn->db, $sql);
while ($r = mysqli_fetch_assoc($result)) {
    $response [] = $r;
}
echo "{\"data\":" . json_encode($response) . "}";

$conn->disconnect();
exit ();