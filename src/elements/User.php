<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class User {

    private $sql;
    private $raw;
    private $id;
    private $usr;
    private $pass;
    private $firstName;
    private $lastName;
    private $email;
    private $role;
    private $hash;
    private $active;
    private $created;
    private $lastLogin;
    private $resetKey;

    function __construct($id) {
        if (!isset ($id)) {
            throw new Exception("User id is required");
        } elseif ($id == "") {
            throw new Exception("User id can not be blank");
        }
        $this->sql = new Sql();
        $id = (int)$id;
        $this->raw = $this->sql->getRow("SELECT * FROM users WHERE id = $id;");
        if (!$this->raw ['id']) {
            $this->sql->disconnect();
            throw new Exception("User id does not match any users");
        }
        $this->id = $this->raw['id'];
        $this->usr = $this->raw['usr'];
        $this->pass = $this->raw['pass'];
        $this->firstName = $this->raw['firstName'];
        $this->lastName = $this->raw['lastName'];
        $this->email = $this->raw['email'];
        $this->role = $this->raw['role'];
        $this->hash = $this->raw['hash'];
        $this->active = $this->raw['active'];
        $this->created = $this->raw['created'];
        $this->lastLogin = $this->raw['lastLogin'];
        $this->resetKey = $this->raw['resetKey'];
    }


    function getId() {
        return $this->id;
    }

    function getUsr() {
        return $this->usr;
    }

    function getHash() {
        return $this->hash;
    }

    function getRole() {
        return $this->role;
    }

    function getFirstName() {
        return $this->firstName;
    }

    function getLastName() {
        return $this->lastName;
    }

    function getEmail() {
        return $this->email;
    }

    function getDataArray() {
        return $this->raw;
    }

    function delete() {
        $user = new CurrentUser($this->sql);
        if (!$user->isAdmin()) {
            throw new Exception("User not authorized to delete user");
        }
        $this->sql->executeStatement("DELETE FROM users WHERE id='{$this->id}';");
    }
}