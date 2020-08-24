<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class User {

    private $session;
    private $raw;
    private $id;
    private $username;
    private $md5Pass;
    private $password;
    private $firstName = '';
    private $lastName = '';
    private $email;
    private $role;
    private $hash;
    private $active;
    private $created;
    private $lastLogin;
    private $resetKey;
    private $isLoggedIn = false;

    function __construct() {
        $this->session = new Session();
    }

    static function fromSystem() {
        $user = new User();
        $hash = NULL;
        if (isset ($_COOKIE) && isset ($_COOKIE['hash'])) {
            $hash = $_COOKIE['hash'];
        }
        if (isset ($_SESSION) && isset ($_SESSION ['hash'])) {
            $hash = $_SESSION ['hash'];
        }
        if ($hash != NULL) {
            try {
                $sql = new Sql();
                $user = User::withId($sql->getRow("SELECT * FROM users WHERE hash='{$hash}';")['id']);
                $sql->disconnect();
                $user->isLoggedIn = true;
            } catch (Exception $e) {
                throw new Exception("Invalid user token provided");
            }
        }
        return $user;
    }

    static function withId($id) {
        if (!isset ($id)) {
            throw new Exception("User id is required");
        } elseif ($id == "") {
            throw new Exception("User id can not be blank");
        }
        $user = new User();
        $id = (int)$id;
        $sql = new Sql();
        $user->raw = $sql->getRow("SELECT * FROM users WHERE id = $id;");
        $sql->disconnect();
        if (!$user->raw ['id']) {
            throw new Exception("User id does not match any users");
        }
        $user->id = $user->raw['id'];
        $user->username = $user->raw['usr'];
        $user->md5Pass = $user->raw['pass'];
        $user->firstName = $user->raw['firstName'];
        $user->lastName = $user->raw['lastName'];
        $user->email = $user->raw['email'];
        $user->role = $user->raw['role'];
        $user->hash = $user->raw['hash'];
        $user->active = $user->raw['active'];
        $user->created = $user->raw['created'];
        $user->lastLogin = $user->raw['lastLogin'];
        $user->resetKey = $user->raw['resetKey'];
        return $user;
    }

    static function withParams($params) {
        $systemUser = User::fromSystem();
        $user = new User();
        $sql = new Sql();
        //verify username properly provided
        if (!isset ($params['username'])) {
            $sql->disconnect();
            throw new Exception("Username is required");
        } elseif ($params['username'] == "") {
            $sql->disconnect();
            throw new Exception("Username can not be blank");
        } elseif (!preg_match('/^[\w]{5,}$/', $params ['username'])) {
            $sql->disconnect();
            throw new Exception("Username is not valid: it must be at least 5 characters, and contain only letters numbers and underscores");
        } elseif ($sql->getRowCount("SELECT * FROM users WHERE usr = '" . $sql->escapeString($params ['username']) . "'") > 0) {
            $sql->disconnect();
            throw new Exception ("That username already exists in the system");
        }
        $user->username = $sql->escapeString($params ['username']);
        //verify email properly provided
        if (!isset ($params['email'])) {
            $sql->disconnect();
            throw new Exception("Email is required");
        } elseif ($params['email'] == "") {
            $sql->disconnect();
            throw new Exception("Email can not be blank");
        } elseif (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
            $sql->disconnect();
            throw new Exception("Email is not valid");
        } elseif ($sql->getRowCount("SELECT * FROM users WHERE email = '" . $sql->escapeString($params ['email']) . "'") > 0) {
            $sql->disconnect();
            throw new Exception ("That email already exists in the system: try logging in with it");
        }
        $user->email = $sql->escapeString($params['email']);
        //setting role - admin users can provide different role
        if ($systemUser->isAdmin() && isset($params['role']) && $params['role'] != "") {
            $enums = $sql->getEnumValues('users', 'role');
            if (!in_array($params['role'], $enums)) {
                $sql->disconnect();
                throw new Exception("Role is not valid");
            }
            $user->role = $sql->escapeString($params['role']);
        } else {
            $user->role = 'downloader';
        }
        //set the password - admins are not expected to provide one, but could
        if (isset($params['password']) && $params['password'] != "") {
            $user->password = $sql->escapeString($params['password']);
        } elseif ($systemUser->isAdmin()) {
            $user->password = self::generatePassword();
        } else {
            if (!isset($params['password'])) {
                $sql->disconnect();
                throw new Exception("Password is required");
            } else {
                $sql->disconnect();
                throw new Exception("Password can not be blank");
            }
        }
        $user->md5Pass = md5($user->password);
        //sets if the user as active - only an admin can make a user inactive
        if ($systemUser->isAdmin() && isset($params['active'])) {
            $user->active = ( int )$params['active'];
        } else {
            $user->active = '1';
        }
        //optional values
        if (isset ($params ['firstName'])) {
            $user->firstName = $sql->escapeString($params ['firstName']);
        }
        if (isset ($params ['lastName'])) {
            $user->lastName = $sql->escapeString($params ['lastName']);
        }
        // some common values
        $sql->disconnect();
        $user->hash = md5($user->username . $user->password);
        return $user;
    }

    static function fromReset($email, $code) {
        $sql = new Sql();
        $row = $sql->getRow("SELECT * FROM users WHERE email='$email' AND resetKey='$code';");
        $sql->disconnect();
        return User::withId($row['id']);
    }

    static function fromLogin($username, $password) {
        $sql = new Sql();
        $row = $sql->getRow("SELECT * FROM users WHERE usr='$username' AND pass='" . md5($password) . "';");
        $sql->disconnect();
        return User::withId($row['id']);
    }

    static function fromEmail($email) {
        $sql = new Sql();
        $row = $sql->getRow("SELECT * FROM users WHERE email='$email';");
        $sql->disconnect();
        return User::withId($row['id']);
    }

    function isLoggedIn() {
        return $this->isLoggedIn;
    }

    function getId() {
        return $this->id;
    }

    function getIdentifier() {
        if (!$this->isLoggedIn()) {
            return $this->session->getClientIP();
        } else {
            return $this->getId();
        }
    }

    function getUsername() {
        return $this->username;
    }

    function getPassword() {
        return $this->password;
    }

    function getHash() {
        return $this->hash;
    }

    function isActive() {
        return boolval($this->active);
    }

    function getRole() {
        return $this->role;
    }

    function isAdmin() {
        return ($this->getRole() === "admin");
    }

    function getFirstName() {
        return $this->firstName;
    }

    function getLastName() {
        return $this->lastName;
    }

    function getName() {
        $name = $this->getFirstName();
        if ($this->getLastName() != "" && $name != "") {
            $name .= " ";
        }
        $name .= $this->getLastName();
        return $name;
    }

    function getEmail() {
        return $this->email;
    }

    /**
     * Only return basic information
     * id, usr, firstName, lastName, email, resetKey, active, role
     */
    function getDataBasic() {
        return array_diff_key($this->raw, ['pass'=>'', 'hash'=>'', 'created'=>'', 'lastLogin'=>'']);
    }

    function getDataArray() {
        return $this->raw;
    }

    static function generatePassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 20; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    function create() {
        $sql = new Sql();
        $lastId = $sql->executeStatement("INSERT INTO `users` (`usr`, `pass`, `firstName`, `lastName`, `email`, `role`, `active`, `hash`) VALUES ('{$this->username}', '{$this->md5Pass}', '{$this->firstName}', '{$this->lastName}', '{$this->email}', '{$this->role}', '{$this->active}', '{$this->hash}');");
        $systemUser = User::fromSystem();
        if (!$systemUser->isAdmin()) {
            $message = 'Registered';
        } else {
            $message = 'Created';
        }
        $sql->executeStatement("INSERT INTO `user_logs` VALUES ( $lastId, CURRENT_TIMESTAMP, '$message', NULL, NULL );");
        $sql->disconnect();
        $this->id = $lastId;
        $user = self::withId($lastId);
        $this->created = $user->created;
        $this->raw = $user->getDataArray();
        return $lastId;
    }

    function delete() {
        $systemUser = User::fromSystem();
        if (!$systemUser->isAdmin()) {
            throw new Exception("User not authorized to delete user");
        }
        $sql = new Sql();
        $sql->executeStatement("DELETE FROM users WHERE id='{$this->id}';");
        $sql->disconnect();
    }

    function login($rememberMe) {
        if (!$this->isActive()) {
            return;
        }
        $session = new Session();
        $session->initialize();
        $_SESSION ['usr'] = $this->username;
        $_SESSION ['hash'] = $this->hash;

        if (isset($_COOKIE['CookiePreferences'])) {
            $preferences = json_decode($_COOKIE['CookiePreferences']);
            if ($rememberMe && is_array($preferences) && in_array("preferences", $preferences)) {
                // remember the user if prompted
                if (!headers_sent()) {
                    setcookie('hash', $this->hash, time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
                    setcookie('usr', $this->username, time() + 10 * 52 * 7 * 24 * 60 * 60, '/');
                }
            }
        }
        $sql = new Sql();
        $sql->executeStatement("UPDATE `users` SET lastLogin=CURRENT_TIMESTAMP WHERE id={$this->id};");
        $user = self::withId($this->id);
        $this->lastLogin = $user->lastLogin;
        $this->raw = $user->getDataArray();
        $sql->executeStatement("INSERT INTO `user_logs` VALUES ( {$this->id}, CURRENT_TIMESTAMP, 'Logged In', NULL, NULL );");
        $sql->disconnect();
    }

    function setResetCode() {
        $sql = new Sql();
        $resetCode = Strings::randomString(8);
        $sql->executeStatement("UPDATE users SET resetKey='$resetCode' WHERE id={$this->id};");
        $sql->disconnect();
        return $resetCode;
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