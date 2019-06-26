<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
include_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
$conn = new Sql ();
$conn->connect ();

$user = new User ();

if (! $user->isAdmin ()) {
    header ( 'HTTP/1.0 401 Unauthorized' );
    if ($user->isLoggedIn ()) {
        echo "Sorry, you do you have appropriate rights to perform this action.";
    }
    $conn->disconnect ();
    exit ();
}

$post = 0;
if (isset ( $_POST ['post'] )) {
    $post = ( int ) $_POST ['post'];
} else {
    echo "No blog post provided";
    exit ();
}

$date;
if (isset ( $_POST ['date'] )) {
    $date = mysqli_real_escape_string ( $conn->db, $_POST ['date'] );
} else {
    echo "No publish date provided";
    exit ();
}

$time;
if (isset ( $_POST ['time'] )) {
    $time = mysqli_real_escape_string ( $conn->db, $_POST ['time'] );
} else {
    echo "No publish time provided";
    exit ();
}
$today = new DateTime ();
$scheduled = new DateTime ( "$date $time" );
$howLong = $scheduled->getTimestamp () - $today->getTimestamp ();
if ($howLong <= 0) {
    echo "This time is not in the future, please select a future time to schedule this post.";
    exit ();
}

$command = "UPDATE \`" . getenv('DB_DATABASE') . "\`.\`blog_details\` SET \`active\` = '1' WHERE \`id\` = '$post';";
$command = "mysql -h " . getenv('DB_HOST') . " -P " . getenv('DB_PORT') . " -u " . getenv('DB_USERNAME') . " -p" . getenv('DB_PASSWORD') . " -e \"$command\"";
system ( "nohup bash -c 'sleep $howLong; $command' > /dev/null 2>&1 &" );

$conn->disconnect ();
exit ();