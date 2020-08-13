<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class Album {

    private $sql;
    private $raw;
    private $id;
    private $name;
    private $description;
    private $date;
    private $lastAccessed;
    private $location;
    private $code;
    private $owner;
    private $images = array();
    private $users = array();

    function __construct($id) {
        if (!isset ($id)) {
            throw new Exception("Album id is required");
        } elseif ($id == "") {
            throw new Exception("Album id can not be blank");
        }
        $this->sql = new Sql();
        $id = (int)$id;
        $this->raw = $this->sql->getRow("SELECT * FROM albums WHERE id = $id;");
        if (!$this->raw ['id']) {
            $this->sql->disconnect();
            throw new Exception("Album id does not match any albums");
        }
        $this->id = $this->raw['id'];
        $this->name = $this->raw['name'];
        $this->description = $this->raw['description'];
        $this->date = $this->raw['date'];
        $this->lastAccessed = $this->raw['lastAccessed'];
        $this->location = $this->raw['location'];
        $this->code = $this->raw['code'];
        $this->owner = $this->raw['owner'];      //TODO - change this to a user class
        $this->images = $this->raw['images'];    //TODO - change this to an array of matching images
        $this->users = array_column($this->sql->getRows("SELECT user FROM albums_for_users WHERE album = {$this->id};"), 'user');
    }

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    public function getOwner() {
        return $this->owner;
    }

    function getDataArray() {
        return $this->raw;
    }

    function canUserGetData() {
        $user = new User($this->sql);
        // only admin users and uploader users who own the album can get all data
        return ($user->isAdmin() || ($user->getRole() == "uploader" && $user->getId() == $this->owner));
    }

    function isSearchedFor() {
        if ($this->code == NULL) {
            // if not code, can't be searched for
            return false;
        } elseif (isset($_SESSION ['searched'][$this->id]) && $_SESSION ['searched'] [$this->id] == md5("album" . $this->code)) {
            // if the search is stored in your session, we're good
            return true;
        } elseif (isset($_COOKIE ['searched']) && isset(json_decode($_COOKIE ['searched'], true) [$this->id]) && json_decode($_COOKIE ['searched'], true) [$this->id] == md5("album" . $this->code)) {
            // if the search is stored in your cookies, we're good
            return true;
        } else {
            return false;
        }
    }

    function canUserAccess() {
        $user = new User($this->sql);
        if ($this->canUserGetData()) {
            // you can access your own stuff
            return true;
        } elseif ($this->isSearchedFor()) { // or it's stored in your cookies
            // you successfully searched for the album
            return true;
        } elseif ($user->isLoggedIn() && in_array($user->getId(), $this->users)) {
            // your user is authorized to view the album
            return true;
        } else {
            return false;
        }
    }

    function delete() {
        if (!$this->canUserGetData()) {
            return;
        }
        $this->sql->executeStatement("DELETE FROM albums WHERE id='{$this->id}';");
        $this->sql->executeStatement("DELETE FROM album_images WHERE album='{$this->id}';");
        $this->sql->executeStatement("DELETE FROM albums_for_users WHERE album='{$this->id}';");
        if ($this->location != "") {
            system("rm -rf " . escapeshellarg(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'albums' . DIRECTORY_SEPARATOR . $this->location));
        }
    }

}