<?php

require "../php/sql.php";

if( isset ( $_POST ['email'] ) && filter_var($_POST ['email'], FILTER_VALIDATE_EMAIL) ) {
    $resetCode = generatePassword( 8 );
    mysqli_query ( $db, "UPDATE users SET resetKey='$resetCode' WHERE email='{$_POST ['email']}';" );
    //TODO - send email!!! This will contain the code, and also will need to contain the users username
    exit();
} else {
    echo "Enter a valid email address!";
    exit();
}

function getRandomBytes($nbBytes = 32)
{
    $bytes = openssl_random_pseudo_bytes($nbBytes, $strong);
    if (false !== $bytes && true === $strong) {
        return $bytes;
    }
    else {
        throw new \Exception("Unable to generate secure token from OpenSSL.");
    }
}
function generatePassword($length){
    return substr(preg_replace("/[^a-zA-Z0-9]/", "", base64_encode(getRandomBytes($length+1))),0,$length);
}
?>