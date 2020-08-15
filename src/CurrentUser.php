<?php

require_once "autoloader.php";

class CurrentUser {
    private $user = NULL;
    private $sql;
    private $session;

    function __construct($sql) {
        $this->session = new Session();
        $this->sql = $sql;
        $hash = NULL;
        if (isset ($_COOKIE) && isset ($_COOKIE['hash'])) {
            $hash = $_COOKIE['hash'];
        }
        if (isset ($_SESSION) && isset ($_SESSION ['hash'])) {
            $hash = $_SESSION ['hash'];
        }
        if ($hash != NULL) {
            try{
                $this->user = new User($sql->getRow("SELECT * FROM users WHERE hash='{$hash}';")['id']);
            } catch ( Exception $e) {
                $this->user =NULL;
            }
        }
    }

    function isLoggedIn() {
        if ($this->user != NULL) {
            return true;
        }
        return false;
    }

    function getId() {
        if ($this->user != NULL) {
            return $this->user->getId();
        }
        return "";
    }

    function getIdentifier() {
        if (!$this->isLoggedIn()) {
            return $this->session->getClientIP();
        } else {
            return $this->getId();
        }
    }

    function getUser() {
        if ($this->user != NULL) {
            return $this->user->getUsr();
        }
        return "";
    }

    function getRole() {
        if ($this->user != NULL) {
            return $this->user->getRole();
        }
        return "";
    }

    function isAdmin() {
        return ($this->getRole() === "admin");
    }

    function getFirstName() {
        if ($this->user != NULL) {
            return $this->user->getFirstName();
        }
        return "";
    }

    function getLastName() {
        if ($this->user != NULL) {
            return $this->user->getLastName();
        }
        return "";
    }

    function getName() {
        $name = $this->getFirstName();
        if( $this->getLastName() != "" && $name != "") {
            $name .= " ";
        }
        $name .= $this->getLastName();
        return $name;
    }

    function getEmail() {
        if ($this->user != NULL) {
            return $this->user->getEmail();
        }
        return "";
    }

    function generatePassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 20; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    function forceAdmin() {
        if (!$this->isAdmin()) {
            $errors = new Errors();
            $errors->throw401();
        }
    }

    function forceLogIn() {
        if (!$this->isLoggedIn()) {
            $errors = new Errors();
            $errors->throw401();
        }
    }
}