<?php
require_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();
// Start our session

include_once "../php/user.php";
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

$command = "UPDATE \`" . $conn->params ['db.database'] . "\`.\`blog_details\` SET \`active\` = '1' WHERE \`id\` = '$post';";
$command = "mysql -h " . $conn->params ['db.host'] . " -u " . $conn->params ['db.username'] . " -p" . $conn->params ['db.password'] . " -e \"$command\"";
system ( "nohup bash -c 'sleep $howLong; $command' > /dev/null 2>&1 &" );

$conn->disconnect ();
exit ();