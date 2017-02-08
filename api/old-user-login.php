<?php
require_once "../php/sql.php";
$conn = new Sql ();
$conn->connect ();

session_name ( 'ssLogin' );
// Starting the session

session_set_cookie_params ( 2 * 7 * 24 * 60 * 60 );
// Making the cookie live for 2 weeks

session_start ();

$err = array ();
// Will hold our errors

if (! $_POST ['username'] || ! $_POST ['password']) {
    $err [] = 'All the fields must be filled in!';
}

if (! count ( $err )) {
    $_POST ['username'] = mysqli_real_escape_string ( $conn->db, $_POST ['username'] );
    $_POST ['password'] = mysqli_real_escape_string ( $conn->db, $_POST ['password'] );
    
    // Escaping all input data
    $row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, "SELECT hash,usr FROM users WHERE usr='{$_POST['username']}' AND pass='{$_POST ['password']}'" ) );
    
    if ($row ['usr']) {
        // If everything is OK login
        
        $_SESSION ['usr'] = $row ['usr'];
        $_SESSION ['hash'] = $row ['hash'];
        
        mysqli_query ( $conn->db, "UPDATE users SET lastLogin=CURRENT_TIMESTAMP WHERE hash='{$_SESSION['hash']}';" );
        // Update last login in DB
    } else {
        $row = mysqli_fetch_assoc ( mysqli_query ( $conn->db, "SELECT * FROM `old_users` WHERE directory='{$_POST['username']}' AND password='{$_POST ['password']}'" ) );
        if ($row ['id']) {
            echo $row['id'];
        } else {
            echo "Credentials do not match our records!";
        }
    }
}

$conn->disconnect ();
exit ();