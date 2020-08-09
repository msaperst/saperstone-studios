<?php

require_once "autoloader.php";

class User {
    private $user_details = NULL;
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
            $this->user_details = $sql->getRow("SELECT * FROM users WHERE hash='{$hash}';");
        }
    }

    function isLoggedIn() {
        if ($this->user_details != NULL) {
            return true;
        }
        return false;
    }

    function getId() {
        if ($this->isLoggedIn() && $this->user_details ['id']) {
            return $this->user_details ['id'];
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
        if ($this->isLoggedIn() && $this->user_details ['usr']) {
            return $this->user_details ['usr'];
        }
        return "";
    }

    function getRole() {
        if ($this->isLoggedIn() && $this->user_details ['role']) {
            return $this->user_details ['role'];
        }
        return "";
    }

    function isAdmin() {
        return ($this->getRole() === "admin");
    }

    function getFirstName() {
        if ($this->isLoggedIn() && $this->user_details ['firstName']) {
            return $this->user_details ['firstName'];
        }
        return "";
    }

    function getLastName() {
        if ($this->isLoggedIn() && $this->user_details ['lastName']) {
            return $this->user_details ['lastName'];
        }
        return "";
    }

    function getName() {
        $name = "";
        if ($this->isLoggedIn()) {
            if ($this->user_details ['firstName']) {
                $name .= $this->user_details ['firstName'];
            }
            if ($this->user_details ['lastName']) {
                if ($name != "") {
                    $name .= " ";
                }
                $name .= $this->user_details ['lastName'];
            }
        }
        return $name;
    }

    function getEmail() {
        if ($this->isLoggedIn() && $this->user_details ['email']) {
            return $this->user_details ['email'];
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

?>