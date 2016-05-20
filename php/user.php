<?php
function isLoggedIn() {
    require "sql.php";
    if (isset ( $_SESSION ) && isset ( $_SESSION ['hash'] )) {
        $row = mysqli_fetch_assoc ( mysqli_query ( $db, "SELECT usr FROM users WHERE hash='{$_SESSION['hash']}';" ) );
        if ($row ['usr']) {
            return true;
        }
    }
    return false;
}
function getUser() {
    require "sql.php";
    if (isset ( $_SESSION ) && isset ( $_SESSION ['hash'] )) {
        $row = mysqli_fetch_assoc ( mysqli_query ( $db, "SELECT usr FROM users WHERE hash='{$_SESSION['hash']}';" ) );
        if ($row ['usr']) {
            return $row ['usr'];
        }
    }
    return "";
}

function getRole() {
    require "sql.php";
    if (isset ( $_SESSION ) && isset ( $_SESSION ['hash'] )) {
        $row = mysqli_fetch_assoc ( mysqli_query ( $db, "SELECT role FROM users WHERE hash='{$_SESSION['hash']}';" ) );
        if ($row ['role']) {
            return $row ['role'];
        }
    }
    return "";
}
?>