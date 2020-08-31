<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class Album {

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

    function __construct() {
    }

    static function withId($id) {
        if (!isset ($id)) {
            throw new Exception("Album id is required");
        } elseif ($id == "") {
            throw new Exception("Album id can not be blank");
        }
        $album = new Album();
        $id = (int)$id;
        $sql = new Sql();
        $album->raw = $sql->getRow("SELECT * FROM albums WHERE id = $id;");
        if (!$album->raw ['id']) {
            $sql->disconnect();
            throw new Exception("Album id does not match any albums");
        }
        $album->id = $album->raw['id'];
        $album->name = $album->raw['name'];
        $album->description = $album->raw['description'];
        $album->date = $album->raw['date'];
        $album->lastAccessed = $album->raw['lastAccessed'];
        $album->location = $album->raw['location'];
        $album->code = $album->raw['code'];
        $album->owner = $album->raw['owner'];      //TODO - change this to a user class
        $album->images = $album->raw['images'];    //TODO - change this to an array of matching images
        $album->users = array_column($sql->getRows("SELECT user FROM albums_for_users WHERE album = {$album->id};"), 'user');
        $sql->disconnect();
        return $album;
    }

    static function withParams($params) {
        return self::setVals(new Album(), $params);
    }

    private static function setVals(Album $album, $params) {
        $sql = new Sql();
        //album name
        if (!isset ($params['name'])) {
            $sql->disconnect();
            throw new Exception("Album name is required");
        } elseif ($params['name'] == "") {
            $sql->disconnect();
            throw new Exception("Album name can not be blank");
        }
        $album->name = $sql->escapeString($params ['name']);
        //album description
        if (isset ($params ['description']) && $params ['description'] != "") {
            $album->description = $sql->escapeString($params ['description']);
        } else {
            $album->description = '';
        }
        // album date
        if (isset ($params ['date']) && $params ['date'] != "") {
            $date = $sql->escapeString($params ['date']);
            $format = 'Y-m-d';
            $d = DateTime::createFromFormat($format, $date);
            if (!($d && $d->format($format) === $date)) {
                $sql->disconnect();
                throw new Exception("Album date is not the correct format");
            }
            $album->date = "'" . $date . "'";
        } else {
            $album->date = 'NULL';
        }
        $sql->disconnect();
        return $album;
    }

    function getId() {
        return $this->id;
    }

    function getName() {
        return $this->name;
    }

    function getDescription() {
        return $this->description;
    }

    public function getOwner() {
        return $this->owner;
    }

    public function getLocation() {
        return $this->location;
    }

    /**
     * Only return basic information
     * name, description, date, code
     */
    function getDataBasic() {
        return array_diff_key($this->raw, ['id' => '', 'lastAccessed' => '', 'location' => '', 'owner' => '', 'images' => '']);
    }

    function getDataArray() {
        return $this->raw;
    }

    function canUserGetData() {
        $user = User::fromSystem();
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
        $user = User::fromSystem();
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

    function create() {
        $user = User::fromSystem();
        if (!$user->isAdmin() && $user->getRole() != "uploader") {
            throw new Exception("User not authorized to create album");
        }
        $sql = new Sql();
        // generate our location for the files
        $location = preg_replace("/[^A-Za-z0-9]/", '', $this->name);
        $location = $location . "_" . time();
        $this->location = $location;
        try {
            mkdir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'albums' . DIRECTORY_SEPARATOR . $location, 0755);
        } catch (Exception $e) {
            $sql->disconnect();
            throw new Exception($e->getMessage() . "<br/>Unable to create album");
        }
        $lastId = $sql->executeStatement("INSERT INTO `albums` (`name`, `description`, `date`, `location`, `owner`) VALUES ('$this->name', '$this->description', $this->date, '$this->location', {$user->getId()});");
        if ($user->getRole() == "uploader") {
            $sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES ({$user->getId()}, $lastId);");
            $sql->executeStatement("INSERT INTO `user_logs` VALUES ( {$user->getId()}, CURRENT_TIMESTAMP, 'Created Album', NULL, $lastId );");
        }
        $sql->disconnect();
        $this->id = $lastId;
        $album = self::withId($lastId);
        $this->raw = $album->getDataArray();
        return $lastId;
    }

    function update($params) {
        $user = User::fromSystem();
        if (!$this->canUserGetData()) {
            throw new Exception("User not authorized to update album");
        }
        self::setVals($this, $params);
        $sql = new Sql();
        $sql->executeStatement("UPDATE albums SET name='{$this->name}', description='{$this->description}', date={$this->date}, code=NULL WHERE id='{$this->getId()}';");
        $this->raw = $sql->getRow("SELECT * FROM albums WHERE id = {$this->getId()};");
        if (isset ($params['code']) && $params['code'] != "" && $user->isAdmin()) {
            $code = $sql->escapeString($params['code']);
            $codeExist = $sql->getRowCount("SELECT * FROM `albums` WHERE code = '$code';");
            if ($codeExist == 0) {
                $this->code = $code;
                $sql->executeStatement("UPDATE albums SET code='$code' WHERE id='{$this->getId()}';");
            } else {
                $sql->disconnect();
                throw new Exception("Album code already exists");
            }
        }
        $this->raw = $sql->getRow("SELECT * FROM albums WHERE id = {$this->getId()};");
        $sql->disconnect();
    }

    function delete() {
        if (!$this->canUserGetData()) {
            throw new Exception("User not authorized to delete album");
        }
        $sql = new Sql();
        $sql->executeStatement("DELETE FROM albums WHERE id='{$this->id}';");
        $sql->executeStatement("DELETE FROM album_images WHERE album='{$this->id}';");
        $sql->executeStatement("DELETE FROM albums_for_users WHERE album='{$this->id}';");
        $sql->disconnect();
        if ($this->location != "") {
            system("rm -rf " . escapeshellarg(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'albums' . DIRECTORY_SEPARATOR . $this->location));
        }
    }
}