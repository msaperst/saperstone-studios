<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new CurrentUser ($sql);
$api = new Api ($sql, $user);

$start = 0;
$howMany = 999999999999999999;

if (isset ($_GET ['start'])) {
    $start = ( int )$_GET ['start'];
}
if (isset ($_GET ['howMany'])) {
    $howMany = ( int )$_GET ['howMany'];
}

$whereClause = "WHERE blog_details.active = 1";
if ($user->isAdmin() && isset ($_GET ['a']) && $_GET ['a']) {
    $whereClause = "";
}

if (isset ($_GET ['tag'])) {
    $query = "SELECT DISTINCT blog FROM blog_tags AS a1";
    $where = " WHERE ";
    for ($i = 1; $i <= sizeof($_GET['tag']); $i++) {
        if ($i != 1) {
            $query .= " JOIN blog_tags AS a$i USING (blog) ";
        }
        $where .= "a$i.tag = " . $_GET['tag'][$i - 1] . " AND ";
    }
    $where = substr($where, 0, -4);
    $blogs = $sql->getRows($query . $where);

    if ($whereClause == "") {
        $whereClause = "WHERE (";
    } else {
        $whereClause .= " AND (";
    }
    foreach ($blogs as $blog) {
        $whereClause .= "id = " . (int)$blog['blog'] . " OR ";
    }
    $whereClause = substr($whereClause, 0, -3);
    $whereClause .= ")";
    $response = $sql->getRows("SELECT DISTINCT * FROM `blog_details` $whereClause ORDER BY `date` DESC LIMIT $start,$howMany;");
} else {
    $response = $sql->getRows("SELECT * FROM `blog_details` $whereClause ORDER BY `date` DESC LIMIT $start,$howMany;");
}

echo "{\"data\":" . json_encode($response) . "}";
$sql->disconnect();
exit ();