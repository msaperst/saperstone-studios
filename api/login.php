<?php
require_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();

if (isset ( $_SESSION ) && isset ( $_SESSION ['hash'] ) && ! isset ( $_COOKIE ['ssRemember'] ) && ! isset ( $_SESSION ['rememberMe'] )) {
    // If you are logged in, but you don't have the tzRemember cookie (browser restart)
    // and you have not checked the rememberMe checkbox:
    
    session_unset ();
    session_destroy ();
    
    // Destroy the session
}

if ($_POST ['submit'] == 'Logout') {
    session_unset ();
    session_destroy ();
    $conn->disconnect ();
    exit ();
}

if ($_POST ['submit'] == 'Login') {
    // Checking whether the Login form has been submitted
    
    $err = array ();
    // Will hold our errors
    
    if (! $_POST ['username'] || ! $_POST ['password']) {
        $err [] = 'All the fields must be filled in!';
    }
    
    if (! count ( $err )) {
        $_POST ['username'] = mysqli_real_escape_string ( $conn->db, $_POST ['username'] );
        $_POST ['password'] = mysqli_real_escape_string ( $conn->db, $_POST ['password'] );
        $_POST ['rememberMe'] = ( int ) $_POST ['rememberMe'];
        
        // Escaping all input data
        $row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, "SELECT hash,usr,active FROM users WHERE usr='{$_POST['username']}' AND pass='{$_POST ['password']}'" ) );
        
        if ($row ['usr'] && $row ['active']) {
            // If everything is OK login
            
            $_SESSION ['usr'] = $row ['usr'];
            $_SESSION ['hash'] = $row ['hash'];
            $_SESSION ['rememberMe'] = $_POST ['rememberMe'];
            // Store some data in the session
            
            setcookie ( 'ssRemember', $_POST ['rememberMe'] );
            // We create the tzRemember cookie
            
            mysqli_query ( $conn->db, "UPDATE users SET lastLogin=CURRENT_TIMESTAMP WHERE hash='{$_SESSION['hash']}';" );
            // Update last login in DB
        } elseif ($row ['usr']) {
            $err [] = 'Sorry, you account has been deactivated. Please 
                    <a target="_blank" href="mailto:webmaster@saperstonestudios.com">contact our
                    webmaster</a> to get this resolved.';
        } else {
            $err [] = 'Credentials do not match our records!';
        }
    }
    
    if ($err) {
        // Save the error messages in the session
        echo implode ( '<br />', $err );
    }
    $conn->disconnect ();
    exit ();
}

$conn->disconnect ();
exit ();