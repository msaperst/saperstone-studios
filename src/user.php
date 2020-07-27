<?php
class User {
    private $user_details = NULL;
    private $sql;
    function __construct($sql) {
        $this->sql = $sql;
        $hash = NULL;
        if( isset ( $_COOKIE ) && isset ( $_COOKIE['hash'] )) {
            $hash = $_COOKIE['hash'];
        }
        if( isset ( $_SESSION ) && isset ( $_SESSION ['hash'] )) {
            $hash = $_SESSION ['hash'];
        }
        if ($hash != NULL ) {
            $this->user_details = $sql->getRow( "SELECT * FROM users WHERE hash='{$hash}';" );
        }
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

    function forceAdmin() {
        if (! $this->isAdmin ()) {
            require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/errors.php";
            throw401();
        }
    }

    function forceLogIn() {
        if (! $this->isLoggedIn ()) {
            require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/errors.php";
            throw401();
        }
    }
}
?>