<?php
if (session_status () != PHP_SESSION_ACTIVE) {

    // Starting the session
    session_name ( 'login' );

    // Making the cookie live for 2 weeks
    session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );

    // Start our session
    if (session_status () == PHP_SESSION_NONE) {
        session_start ();
    }
}
?>