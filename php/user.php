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
function getUserId() {
    require "sql.php";
    if (isset ( $_SESSION ) && isset ( $_SESSION ['hash'] )) {
        $row = mysqli_fetch_assoc ( mysqli_query ( $db, "SELECT id FROM users WHERE hash='{$_SESSION['hash']}';" ) );
        if ($row ['id']) {
            return $row ['id'];
        }
    }
    return "";
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
function getName() {
    require "sql.php";
    if (isset ( $_SESSION ) && isset ( $_SESSION ['hash'] )) {
        $row = mysqli_fetch_assoc ( mysqli_query ( $db, "SELECT firstName, lastName FROM users WHERE hash='{$_SESSION['hash']}';" ) );
        $name = "";
        if ($row ['firstName']) {
            $name += $row ['firstName'];
        }
        if ($row ['lastName']) {
            $name += $row ['lastName'];
        }
        return $name;
    }
    return "";
}
function getEmail() {
    require "sql.php";
    if (isset ( $_SESSION ) && isset ( $_SESSION ['hash'] )) {
        $row = mysqli_fetch_assoc ( mysqli_query ( $db, "SELECT email FROM users WHERE hash='{$_SESSION['hash']}';" ) );
        if ($row ['email']) {
            return $row ['email'];
        }
    }
    return "";
}
?>