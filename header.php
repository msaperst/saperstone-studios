<?php
if (session_status () != PHP_SESSION_ACTIVE) {
    session_name ( 'ssLogin' );
    // Starting the session
    
    session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
    // Making the cookie live for 2 weeks
    
    session_start ();
    // Start our session
}

$nav = "main";
// Define our default menu

?>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description"
    content="Photography and Retouch for Virginia, DC, and Maryland by Saperstone Studios">
<meta name="author" content="Saperstone Studios">

<link rel="apple-touch-icon" sizes="57x57"
    href="/img/favicon/apple-icon-57x57.png?v=2">
<link rel="apple-touch-icon" sizes="60x60"
    href="/img/favicon/apple-icon-60x60.png?v=2">
<link rel="apple-touch-icon" sizes="72x72"
    href="/img/favicon/apple-icon-72x72.png?v=2">
<link rel="apple-touch-icon" sizes="76x76"
    href="/img/favicon/apple-icon-76x76.png?v=2">
<link rel="apple-touch-icon" sizes="114x114"
    href="/img/favicon/apple-icon-114x114.png?v=2">
<link rel="apple-touch-icon" sizes="120x120"
    href="/img/favicon/apple-icon-120x120.png?v=2">
<link rel="apple-touch-icon" sizes="144x144"
    href="/img/favicon/apple-icon-144x144.png?v=2">
<link rel="apple-touch-icon" sizes="152x152"
    href="/img/favicon/apple-icon-152x152.png?v=2">
<link rel="apple-touch-icon" sizes="180x180"
    href="/img/favicon/apple-icon-180x180.png?v=2">
<link rel="icon" type="image/png" sizes="192x192"
    href="/img/favicon/android-icon-192x192.png?v=2">
<link rel="icon" type="image/png" sizes="32x32"
    href="/img/favicon/favicon-32x32.png?v=2">
<link rel="icon" type="image/png" sizes="96x96"
    href="/img/favicon/favicon-96x96.png?v=2">
<link rel="icon" type="image/png" sizes="16x16"
    href="/img/favicon/favicon-16x16.png?v=2">
<link rel="manifest" href="/img/favicon/manifest.json">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage"
    content="/img/favicon/ms-icon-144x144.png?v=2">
<meta name="theme-color" content="#ffffff">

<title>Photography and Retouch for Virginia, DC, and Maryland by
    Saperstone Studios</title>
<link rel="alternate" type="application/rss+xml" href="/blog.rss"
    title="RSS feed for Saperstone Studios Blogs">

<!-- Bootstrap Core CSS -->
<link
    href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7"
    crossorigin="anonymous">
<link
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css"
    rel="stylesheet">

<!-- Custom CSS -->
<link href="/css/modern-business.css" rel="stylesheet">
<link href="/css/saperstone-studios.css" rel="stylesheet">

<!-- Custom Fonts -->
<link href="/plugins/font-awesome-4.7.0/css/font-awesome.min.css"
    rel="stylesheet" type="text/css">

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

<?php
include_once "php/user.php";
$user = new User ();
?>