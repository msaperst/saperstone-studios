<?php
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/session.php";
$conn = new Sql ();
$conn->connect ();

if (isset ( $_SESSION ) && isset ( $_SESSION ['hash'] ) && ! isset ( $_COOKIE ['hash'] ) && ! isset ( $_COOKIE ['usr'] )) {
    // If you are logged in, but you don't have the tzRemember cookie (browser restart)
    // and you have not checked the rememberMe checkbox:
    
    session_unset ();
    session_destroy ();
    
    // Destroy the session
}

if ($_POST ['submit'] == 'Logout') {
    // note the logout
    $row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, "SELECT * FROM users WHERE hash='{$_SESSION['hash']}'" ) );
    mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( {$row ['id']}, CURRENT_TIMESTAMP, 'Logged Out', NULL, NULL );" );

    // remove any stored login
    unset($_COOKIE['hash']);
    unset($_COOKIE['usr']);
    setcookie('hash', null, -1, '/');
    setcookie('usr', null, -1, '/');

    // destroy the session
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
        $row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, "SELECT * FROM users WHERE usr='{$_POST['username']}' AND pass='{$_POST ['password']}'" ) );
        
        if ($row ['usr'] && $row ['active']) {
            // If everything is OK login

            $_SESSION ['usr'] = $row ['usr'];
            $_SESSION ['hash'] = $row ['hash'];
            // Store some data in the session

            if( $_POST['rememberMe'] ) {
                // remember the user if prompted
                $_COOKIE['hash'] = $row ['hash'];
                $_COOKIE ['usr'] = $row ['usr'];
                setcookie ( 'hash', $row ['hash'], time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
                setcookie ( 'usr', $row ['usr'], time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
            }

            mysqli_query ( $conn->db, "UPDATE `users` SET lastLogin=CURRENT_TIMESTAMP WHERE hash='{$_SESSION['hash']}';" );
            mysqli_query ( $conn->db, "INSERT INTO `user_logs` VALUES ( {$row ['id']}, CURRENT_TIMESTAMP, 'Logged In', NULL, NULL );" );
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