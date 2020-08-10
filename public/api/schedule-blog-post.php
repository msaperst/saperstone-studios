<?php
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$session = new Session();
$session->initialize();
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$post = 0;
if (isset ($_POST ['post'])) {
    $post = ( int )$_POST ['post'];
} else {
    echo "No blog post provided";
    exit ();
}

$date;
if (isset ($_POST ['date'])) {
    $date = $sql->escapeString($_POST ['date']);
} else {
    echo "No publish date provided";
    exit ();
}

$time;
if (isset ($_POST ['time'])) {
    $time = $sql->escapeString($_POST ['time']);
} else {
    echo "No publish time provided";
    exit ();
}
$today = new DateTime ();
$scheduled = new DateTime ("$date $time");
$howLong = $scheduled->getTimestamp() - $today->getTimestamp();
if ($howLong <= 0) {
    echo "This time is not in the future, please select a future time to schedule this post.";
    exit ();
}

$command = "UPDATE \`" . getenv('DB_NAME') . "\`.\`blog_details\` SET \`active\` = '1' WHERE \`id\` = '$post';";
$command = "mysql -h " . getenv('DB_HOST') . " -P " . getenv('DB_PORT') . " -u " . getenv('DB_USER') . " -p" . getenv('DB_PASS') . " -e \"$command\"";
system("nohup bash -c 'sleep $howLong; $command' > /dev/null 2>&1 &");

$conn->disconnect();
exit ();