<?php
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';
$api = new Api ();

$api->forceAdmin();

try {
    $blog = Blog::withId($_POST ['post']);
    $date = $api->retrievePostDateTime('date', 'Publish date', 'Y-m-d');
    $time = $api->retrievePostDateTime('time', 'Publish time', 'H:i');
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$today = new DateTime ();
$scheduled = new DateTime ("$date $time");
$howLong = $scheduled->getTimestamp() - $today->getTimestamp();
if ($howLong <= 0) {
    echo "This time is not in the future, please select a future time to schedule this post";
    exit ();
}

$command = "UPDATE \`" . getenv('DB_NAME') . "\`.\`blog_details\` SET \`active\` = '1' WHERE \`id\` = '{$blog->getId()}';";
$command = "mysql -h " . getenv('DB_HOST') . " -P " . getenv('DB_PORT') . " -u " . getenv('DB_USER') . " -p" . getenv('DB_PASS') . " -e \"$command\"";
system("nohup bash -c 'sleep $howLong; $command' > /dev/null 2>&1 &");
exit ();