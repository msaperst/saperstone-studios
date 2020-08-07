<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'session.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'user.php';
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'api.php';
$sql = new Sql ();
$user = new User ($sql);
$api = new Api ($sql, $user);

$api->forceAdmin();

$username = $api->retrievePostString('username', 'Username');
if( is_array( $username ) ) {
    echo $username['error'];
    exit();
}
$row = $sql->getRow( "SELECT * FROM users WHERE usr = '$username'" );
if ($row ['usr']) {
    echo "That username already exists in the system";
    $sql->disconnect ();
    exit ();
}

$email = $api->retrieveValidatedPost('email', 'Email', FILTER_VALIDATE_EMAIL );
if( is_array( $email ) ) {
    echo $email['error'];
    exit();
}
$row = $sql->getRow( "SELECT email FROM users WHERE email='$email'" );
if ($row ['email']) {
    echo "We already have an account on file for that email address";
    $sql->disconnect ();
    exit ();
}

$role = $api->retrievePostString('role', 'Role');
if( is_array( $role ) ) {
    echo $role['error'];
    exit();
}
$enums = $sql->getEnumValues( 'users', 'role' );
if (! in_array( $role, $enums ) ) {
    echo "Role is not valid";
    $sql->disconnect ();
    exit ();
}

$firstName = $lastName = "";
if (isset ( $_POST ['firstName'] )) {
    $firstName = $sql->escapeString( $_POST ['firstName'] );
}
if (isset ( $_POST ['lastName'] )) {
    $lastName = $sql->escapeString( $_POST ['lastName'] );
}

$active = 0;
if (isset ( $_POST ['active'] )) {
    $active = ( int ) $_POST ['active'];
}

$password = $user->generatePassword();

echo $sql->executeStatement ( "INSERT INTO users ( usr, pass, firstName, lastName, email, role, active, hash ) VALUES ('$username', '$password', '$firstName', '$lastName', '$email', '$role', '$active', '" . md5 ( $username . $role ) . "' );" );
$sql->disconnect ();

require_once "Mail.php";
require_once "Mail/mime.php";

$to = "$firstName $lastName <$email>";
$from = "noreply@saperstonestudios.com";
$subject = "New User Created at Saperstone Studios";
$text = "Someone has setup a new user for you at Saperstone Studios. ";
$text .= "You can login and access the site at https://saperstonestudios.com. ";
$text .= "Initial credentials have been setup for you as: \n";
$text .= "    Username: " . $username . "\n";
$text .= "    Password: " . $password . "\n";
$text .= "For security reasons, once logged in, we recommend you reset your password at ";
$text .= "https://saperstonestudios.com/user/profile.php";
$html = "<html><body>";
$html .= "Someone has setup a new user for you at Saperstone Studios. ";
$html .= "You can login and access the site at <a href='https://saperstonestudios.com'>saperstonestudios.com</a>. ";
$html .= "Initial credentials have been setup for you as: ";
$html .= "<p>&nbsp;&nbsp;&nbsp;&nbsp;Username: " . $username;
$html .= "<br/>&nbsp;&nbsp;&nbsp;&nbsp;Password: " . $password . "</p>";
$html .= "For security reasons, once logged in, we recommend you <a href='https://saperstonestudios.com/user/profile.php'>reset your password</a>.";
$crlf = "\n";
$mime = new Mail_mime ( $crlf );
$mime->setTXTBody ( $text );
$mime->setHTMLBody ( $html );
$body = $mime->get ();
require dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'email.php';
exit ();
?>