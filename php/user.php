<?php
class user {
    var $sql;
    var $db;
    function user() {
        include_once "sql.php";
        $sql = new sql ();
        $sql->connect ();
        $this->db = $sql->db;
    }
    function isLoggedIn() {
        if (isset ( $_SESSION ) && isset ( $_SESSION ['hash'] )) {
            $row = mysqli_fetch_assoc ( mysqli_query ( $this->db, "SELECT usr FROM users WHERE hash='{$_SESSION['hash']}';" ) );
            if ($row ['usr']) {
                return true;
            }
        }
        return false;
    }
    function getId() {
        if (isset ( $_SESSION ) && isset ( $_SESSION ['hash'] )) {
            $row = mysqli_fetch_assoc ( mysqli_query ( $this->db, "SELECT id FROM users WHERE hash='{$_SESSION['hash']}';" ) );
            if ($row ['id']) {
                return $row ['id'];
            }
        }
        return "";
    }
    function getUser() {
        if (isset ( $_SESSION ) && isset ( $_SESSION ['hash'] )) {
            $row = mysqli_fetch_assoc ( mysqli_query ( $this->db, "SELECT usr FROM users WHERE hash='{$_SESSION['hash']}';" ) );
            if ($row ['usr']) {
                return $row ['usr'];
            }
        }
        return "";
    }
    function getRole() {
        if (isset ( $_SESSION ) && isset ( $_SESSION ['hash'] )) {
            $row = mysqli_fetch_assoc ( mysqli_query ( $this->db, "SELECT role FROM users WHERE hash='{$_SESSION['hash']}';" ) );
            if ($row ['role']) {
                return $row ['role'];
            }
        }
        return "";
    }
    function getName() {
        if (isset ( $_SESSION ) && isset ( $_SESSION ['hash'] )) {
            $row = mysqli_fetch_assoc ( mysqli_query ( $this->db, "SELECT firstName, lastName FROM users WHERE hash='{$_SESSION['hash']}';" ) );
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
        if (isset ( $_SESSION ) && isset ( $_SESSION ['hash'] )) {
            $row = mysqli_fetch_assoc ( mysqli_query ( $this->db, "SELECT email FROM users WHERE hash='{$_SESSION['hash']}';" ) );
            if ($row ['email']) {
                return $row ['email'];
            }
        }
        return "";
    }
}
?>