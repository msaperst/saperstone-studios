<?php
class User {
    private $user_details = NULL;
    function __construct() {
        include_once "sql.php";
        $sql = new Sql ();
        $sql->connect ();
        $hash = NULL;
        if( isset ( $_COOKIE ) && isset ( $_COOKIE['hash'] )) {
            $hash = $_COOKIE['hash'];
        }
        if( isset ( $_SESSION ) && isset ( $_SESSION ['hash'] )) {
            $hash = $_SESSION ['hash'];
        }
        if ($hash != NULL ) {
            $this->user_details = mysqli_fetch_assoc ( mysqli_query ( $sql->db, "SELECT * FROM users WHERE hash='{$hash}';" ) );
        }
        $sql->disconnect ();
    }
    function isLoggedIn() {
        if ($this->user_details != NULL) {
            return true;
        }
        return false;
    }
    function getId() {
        if ($this->user_details ['id']) {
            return $this->user_details ['id'];
        }
        return "";
    }
    function getUser() {
        if ($this->user_details ['usr']) {
            return $this->user_details ['usr'];
        }
        return "";
    }
    function getRole() {
        if ($this->user_details ['role']) {
            return $this->user_details ['role'];
        }
        return "";
    }
    function isAdmin() {
        return ($this->getRole () === "admin");
    }
    function getFirstName() {
        if ($this->user_details ['firstName']) {
            return $this->user_details ['firstName'];
        }
        return "";
    }
    function getLastName() {
        if ($this->user_details ['lastName']) {
            return $this->user_details ['lastName'];
        }
        return "";
    }
    function getName() {
        $name = "";
        if ($this->user_details ['firstName']) {
            $name .= $this->user_details ['firstName'];
        }
        if ($this->user_details ['lastName']) {
            $name .= " " . $this->user_details ['lastName'];
        }
        return $name;
    }
    function getEmail() {
        if ($this->user_details ['email']) {
            return $this->user_details ['email'];
        }
        return "";
    }
}
?>