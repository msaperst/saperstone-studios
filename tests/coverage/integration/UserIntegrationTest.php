<?php

namespace coverage\integration;

use Exception;
use PHPUnit\Framework\TestCase;
use Sql;
use User;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UserIntegrationTest extends TestCase {
    private $sql;

    public function setUp() {
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `users` (`id`, `usr`, `pass`, `firstName`, `lastName`, `email`, `role`, `hash`, `active`, `created`, `lastLogin`, `resetKey`) VALUES (899, 'test', '" . md5('user') . "', 'test', 'user', 'test@example.com', 'downloader', '12345', '0', '2020-01-01 10:10:10', '2020-01-01 20:10:10', '123')");
    }

    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM `users` WHERE `users`.`id` = 899;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
        if( isset($_COOKIE['hash'])) {
            unset($_COOKIE['hash']);
        }
        if( isset($_SESSION['hash'])) {
            unset($_SESSION['hash']);
        }
        if( isset($_SESSION['usr'])) {
            unset($_SESSION['usr']);
        }
    }

    public function testNullUserId() {
        try {
            User::withId(NULL);
        } catch (Exception $e) {
            $this->assertEquals("User id is required", $e->getMessage());
        }
    }

    public function testBlankUserId() {
        try {
            User::withId("");
        } catch (Exception $e) {
            $this->assertEquals("User id can not be blank", $e->getMessage());
        }
    }

    public function testLetterUserId() {
        try {
            User::withId("a");
        } catch (Exception $e) {
            $this->assertEquals("User id does not match any users", $e->getMessage());
        }
    }

    public function testBadUserId() {
        try {
            User::withId(8999);
        } catch (Exception $e) {
            $this->assertEquals("User id does not match any users", $e->getMessage());
        }
    }

    public function testBadStringUserId() {
        try {
            User::withId("8999");
        } catch (Exception $e) {
            $this->assertEquals("User id does not match any users", $e->getMessage());
        }
    }

    public function testFromResetNoMatch() {
        try {
            User::fromReset(NULL, '123');
        } catch (Exception $e) {
            $this->assertEquals('User id is required', $e->getMessage());
        }
    }

    public function testFromResetMatch() {
        $user = User::fromReset('test@example.com', '123');
        $this->assertEquals(899, $user->getId());
    }

    public function testFromLoginNoMatch() {
        try {
            User::fromLogin('hey', '123');
        } catch (Exception $e) {
            $this->assertEquals('User id is required', $e->getMessage());
        }
    }

    public function testFromLoginMatch() {
        $user = User::fromLogin('test', 'user');
        $this->assertEquals(899, $user->getId());
    }

    public function testFromEmailNoMatch() {
        try {
            User::fromEmail('random@email.com');
        } catch (Exception $e) {
            $this->assertEquals('User id is required', $e->getMessage());
        }
    }

    public function testFromEmailMatch() {
        $user = User::fromEmail('msaperst@gmail.com');
        $this->assertEquals(1, $user->getId());
    }

    public function testGetId() {
        $user = User::withId('899');
        $this->assertEquals(899, $user->getId());
    }

    public function testGetUsr() {
        $user = User::withId('899');
        $this->assertEquals('test', $user->getUsername());
    }

    public function testGetHash() {
        $user = User::withId('899');
        $this->assertEquals('12345', $user->getHash());
    }

    public function testGetActiveFalse() {
        $user = User::withId('899');
        $this->assertFalse($user->isActive());
    }

    public function testGetActiveTrue() {
        $user = User::withId('1');
        $this->assertTrue($user->isActive());
    }

    public function testGetRole() {
        $user = User::withId('899');
        $this->assertEquals('downloader', $user->getRole());
    }

    public function testGetFirstName() {
        $user = User::withId('899');
        $this->assertEquals('test', $user->getFirstName());
    }

    public function testGetLastName() {
        $user = User::withId('899');
        $this->assertEquals('user', $user->getLastName());
    }

    public function testGetEmail() {
        $user = User::withId('899');
        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function testBasicData() {
        $user = User::withId(899);
        $userInfo = $user->getDataBasic();
        $this->assertEquals(8, sizeof($userInfo));
        $this->assertEquals(899, $userInfo['id']);
        $this->assertEquals('test', $userInfo['usr']);
        $this->assertEquals('test', $userInfo['firstName']);
        $this->assertEquals('user', $userInfo['lastName']);
        $this->assertEquals('test@example.com', $userInfo['email']);
        $this->assertEquals('downloader', $userInfo['role']);
        $this->assertEquals(0, $userInfo['active']);
        $this->assertEquals('123', $userInfo['resetKey']);
    }

    public function testAllData() {
        $user = User::withId(899);
        $userInfo = $user->getDataArray();
        $this->assertEquals(12, sizeof($userInfo));
        $this->assertEquals(899, $userInfo['id']);
        $this->assertEquals('test', $userInfo['usr']);
        $this->assertEquals(md5('user'), $userInfo['pass']);
        $this->assertEquals('test', $userInfo['firstName']);
        $this->assertEquals('user', $userInfo['lastName']);
        $this->assertEquals('test@example.com', $userInfo['email']);
        $this->assertEquals('downloader', $userInfo['role']);
        $this->assertEquals('12345', $userInfo['hash']);
        $this->assertEquals(0, $userInfo['active']);
        $this->assertEquals('2020-01-01 10:10:10', $userInfo['created']);
        $this->assertEquals('2020-01-01 20:10:10', $userInfo['lastLogin']);
        $this->assertEquals('123', $userInfo['resetKey']);
    }

    public function testDeleteNoAccess() {
        $user = User::withId(899);
        try {
            $user->delete();
        } catch (Exception $e) {
            $this->assertEquals("User not authorized to delete user", $e->getMessage());
        }
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `users` WHERE `users`.`id` = 899;"));
    }

    public function testDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $user = User::withId(899);
        $user->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `users` WHERE `users`.`id` = 899;"));
    }


    public function testNoUser() {
        $user = User::fromSystem();
        $this->assertFalse($user->isLoggedIn());
        $this->assertEquals('', $user->getId());
        $this->assertEquals('', $user->getIdentifier());
        $this->assertEquals('', $user->getUsername());
        $this->assertEquals('downloader', $user->getRole());
        $this->assertFalse($user->isAdmin());
        $this->assertEquals('', $user->getFirstName());
        $this->assertEquals('', $user->getLastName());
        $this->assertEquals('', $user->getName());
        $this->assertEquals('', $user->getEmail());
    }

    public function testBadSessionUser() {
        $_SESSION ['hash'] = "1234567890abcdef1234567890abcdef";
        try {
            User::fromSystem();
        } catch (Exception $e) {
            $this->assertEquals('Invalid user token provided', $e->getMessage());
        } finally {
            unset($_SESSION ['hash']);
        }
    }

    public function testBadCookieUser() {
        $_COOKIE ['hash'] = "1234567890abcdef1234567890abcdef";
        try {
            User::fromSystem();
        } catch (Exception $e) {
            $this->assertEquals('Invalid user token provided', $e->getMessage());
        } finally {
            unset($_COOKIE ['hash']);
        }
    }

    public function testAdminUser() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $user = User::fromSystem();
        unset($_SESSION ['hash']);
        $this->assertTrue($user->isLoggedIn());
        $this->assertEquals(1, $user->getId());
        $this->assertEquals(1, $user->getIdentifier());
        $this->assertEquals('msaperst', $user->getUsername());
        $this->assertEquals('admin', $user->getRole());
        $this->assertTrue($user->isAdmin());
        $this->assertEquals('Max', $user->getFirstName());
        $this->assertEquals('Saperstone', $user->getLastName());
        $this->assertEquals('Max Saperstone', $user->getName());
        $this->assertEquals('msaperst@gmail.com', $user->getEmail());
    }

    public function testDownloadUser() {
        $_COOKIE ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $user = User::fromSystem();
        unset($_COOKIE ['hash']);
        $this->assertTrue($user->isLoggedIn());
        $this->assertEquals(3, $user->getId());
        $this->assertEquals(3, $user->getIdentifier());
        $this->assertEquals('downloader', $user->getUsername());
        $this->assertEquals('downloader', $user->getRole());
        $this->assertFalse($user->isAdmin());
        $this->assertEquals('Download', $user->getFirstName());
        $this->assertEquals('User', $user->getLastName());
        $this->assertEquals('Download User', $user->getName());
        $this->assertEquals('email@example.org', $user->getEmail());
    }

    public function testUploadUser() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $user = User::fromSystem();
        unset($_SESSION ['hash']);
        $this->assertTrue($user->isLoggedIn());
        $this->assertEquals(4, $user->getId());
        $this->assertEquals(4, $user->getIdentifier());
        $this->assertEquals('uploader', $user->getUsername());
        $this->assertEquals('uploader', $user->getRole());
        $this->assertFalse($user->isAdmin());
        $this->assertEquals('Upload', $user->getFirstName());
        $this->assertEquals('User', $user->getLastName());
        $this->assertEquals('Upload User', $user->getName());
        $this->assertEquals('uploader@example.org', $user->getEmail());
    }

    public function testGeneratePassword() {
        $user = User::fromSystem();
        $this->assertEquals(20, strlen($user->generatePassword()));
        $this->assertEquals(1, preg_match("/^([a-zA-Z0-9]{20})$/", $user->generatePassword()));
    }

    public function testNewUserNoUsername() {
        try {
            User::withParams(array());
        } catch (Exception $e) {
            $this->assertEquals('Username is required', $e->getMessage());
        }
    }

    public function testNewUserBlankUsername() {
        $params = [
            'username' => ''
        ];
        try {
            User::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Username can not be blank', $e->getMessage());
        }
    }

    public function testNewUserUsernameToShort() {
        $params = [
            'username' => '123'
        ];
        try {
            User::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Username is not valid: it must be at least 5 characters, and contain only letters numbers and underscores', $e->getMessage());
        }
    }

    public function testNewUserUsernameBadChars() {
        $params = [
            'username' => '123$5K{;'
        ];
        try {
            User::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Username is not valid: it must be at least 5 characters, and contain only letters numbers and underscores', $e->getMessage());
        }
    }

    public function testNewUserUsernameDuplicate() {
        $params = [
            'username' => 'msaperst'
        ];
        try {
            User::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('That username already exists in the system', $e->getMessage());
        }
    }

    public function testNewUserNoEmail() {
        $params = [
            'username' => 'testUser'
        ];
        try {
            User::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Email is required', $e->getMessage());
        }
    }

    public function testNewUserBlankEmail() {
        $params = [
            'username' => 'testUser',
            'email' => ''
        ];
        try {
            User::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Email can not be blank', $e->getMessage());
        }
    }

    public function testNewUserInvalidEmail() {
        $params = [
            'username' => 'testUser',
            'email' => 'max@max'
        ];
        try {
            User::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Email is not valid', $e->getMessage());
        }
    }

    public function testNewUserDuplicateEmail() {
        $params = [
            'username' => 'testUser',
            'email' => 'msaperst@gmail.com'
        ];
        try {
            User::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('That email already exists in the system: try logging in with it', $e->getMessage());
        }
    }

    public function testNewUserNoPassword() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
        ];
        try {
            User::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Password is required', $e->getMessage());
        }
    }

    public function testNewUserBlankPassword() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => ''
        ];
        try {
            User::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Password can not be blank', $e->getMessage());
        }
    }

    public function testNewUserAdminPasswordNotNeeded() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $user = User::withParams($params);
        unset($_SESSION['hash']);
        $this->assertEquals(20, strlen($user->getPassword()));
    }

    public function testNewUserBasics() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $user = User::withParams($params);
        unset($_SESSION['hash']);
        $this->assertEquals('12345', $user->getPassword());
    }

    public function testNewUserDownloaderDefaultRole() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345'
        ];
        $user = User::withParams($params);
        $this->assertEquals('downloader', $user->getRole());
    }

    public function testNewUserOnlyAdminCanSetAdmin() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345',
            'role' => 'admin'
        ];
        $user = User::withParams($params);
        $this->assertEquals('downloader', $user->getRole());
    }

    public function testNewUserAdminCanSetAdmin() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'role' => 'admin'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $user = User::withParams($params);
        unset($_SESSION['hash']);
        $this->assertEquals('admin', $user->getRole());
    }

    public function testNewUserAdminCantSetBadRole() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'role' => 'administrator'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            User::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Role is not valid', $e->getMessage());
        } finally {
            unset($_SESSION['hash']);
        }
    }

    public function testNewUserActiveDefault() {
        try {
            $params = [
                'username' => 'testUser',
                'email' => 'test@example.org',
                'password' => '12345'
            ];
            $user = User::withParams($params);
            $id = $user->create();
            $this->assertEquals(1, $user->getDataArray()['active']);
        } finally {
            $this->sql->executeStatement("DELETE FROM `users` WHERE `id` = $id;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
        }
    }

    public function testNewUserOnlyAdminCanSetInactive() {
        try {
            $params = [
                'username' => 'testUser',
                'email' => 'test@example.org',
                'password' => '12345',
                'active' => '0'
            ];
            $user = User::withParams($params);
            $id = $user->create();
            $this->assertEquals(1, $user->getDataArray()['active']);
        } finally {
            $this->sql->executeStatement("DELETE FROM `users` WHERE `id` = $id;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
        }
    }

    public function testNewUserAdminCanSetInactive() {
        try {
            $params = [
                'username' => 'testUser',
                'email' => 'test@example.org',
                'password' => '12345',
                'active' => '0'
            ];
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $user = User::withParams($params);
            unset($_SESSION['hash']);
            $id = $user->create();
            $this->assertEquals(0, $user->getDataArray()['active']);
        } finally {
            $this->sql->executeStatement("DELETE FROM `users` WHERE `id` = $id;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
        }
    }

    public function testNewUserDefaultNoName() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345',
        ];
        $user = User::withParams($params);
        $this->assertEquals('', $user->getName());
    }

    public function testNewUserFirstName() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345',
            'firstName' => 'Max'
        ];
        $user = User::withParams($params);
        $this->assertEquals('Max', $user->getName());
    }

    public function testNewUserLastName() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345',
            'lastName' => 'Saperstone'
        ];
        $user = User::withParams($params);
        $this->assertEquals('Saperstone', $user->getName());
    }

    public function testNewUserName() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345',
            'firstName' => 'Max',
            'lastName' => 'Saperstone'
        ];
        $user = User::withParams($params);
        $this->assertEquals('Max Saperstone', $user->getName());
    }

    public function testNewUserHash() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345'
        ];
        $user = User::withParams($params);
        $this->assertEquals(md5('testUser12345'), $user->getHash());
    }

    public function testNewUserFromAdmin() {
        sleep( 1 );
        date_default_timezone_set("America/New_York");
        try {
            $params = [
                'username' => 'testUser',
                'email' => 'test@example.org',
                'password' => '12345'
            ];
            $_COOKIE ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $user = User::withParams($params);
            $id = $user->create();
            $userLogs = $this->sql->getRow("SELECT * FROM `user_logs` WHERE user = $id ORDER BY time DESC");
            $this->assertEquals($id, $userLogs['user']);
            $this->assertEquals('Created', $userLogs['action']);
            $this->assertNull($userLogs['what']);
            $this->assertNull($userLogs['album']);
            $this->assertEquals($id, $user->getId());
            $userDetails = $user->getDataArray();
            $this->assertEquals($id, $userDetails['id']);
            $this->assertEquals('testUser', $userDetails['usr']);
            $this->assertEquals(md5('12345'), $userDetails['pass']);
            $this->assertEquals('', $userDetails['firstName']);
            $this->assertEquals('', $userDetails['lastName']);
            $this->assertEquals('test@example.org', $userDetails['email']);
            $this->assertEquals('downloader', $userDetails['role']);
            $this->assertEquals(md5('testUser12345'), $userDetails['hash']);
            $this->assertEquals(1, $userDetails['active']);
            $this->assertStringStartsWith(date("Y-m-d H:i"), $userDetails['created']);
            $this->assertNull($userDetails['lastLogin']);
            $this->assertNull($userDetails['resetKey']);
        } finally {
            unset($_COOKIE['hash']);
            $this->sql->executeStatement("DELETE FROM `users` WHERE `id` = $id;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
        }
    }

    public function testNewUser() {
        sleep( 1 );
        date_default_timezone_set("America/New_York");
        try {
            $params = [
                'username' => 'testUser',
                'email' => 'test@example.org',
                'password' => '12345'
            ];
            $user = User::withParams($params);
            $id = $user->create();
            $userLogs = $this->sql->getRow("SELECT * FROM `user_logs` WHERE user = $id ORDER BY time DESC");
            $this->assertEquals($id, $userLogs['user']);
            $this->assertEquals('Registered', $userLogs['action']);
            $this->assertNull($userLogs['what']);
            $this->assertNull($userLogs['album']);
            $this->assertEquals($id, $user->getId());
            $userDetails = $user->getDataArray();
            $this->assertEquals($id, $userDetails['id']);
            $this->assertEquals('testUser', $userDetails['usr']);
            $this->assertEquals(md5('12345'), $userDetails['pass']);
            $this->assertEquals('', $userDetails['firstName']);
            $this->assertEquals('', $userDetails['lastName']);
            $this->assertEquals('test@example.org', $userDetails['email']);
            $this->assertEquals('downloader', $userDetails['role']);
            $this->assertEquals(md5('testUser12345'), $userDetails['hash']);
            $this->assertEquals(1, $userDetails['active']);
            $this->assertStringStartsWith(date("Y-m-d H:i"), $userDetails['created']);
            $this->assertNull($userDetails['lastLogin']);
            $this->assertNull($userDetails['resetKey']);
        } finally {
            $this->sql->executeStatement("DELETE FROM `users` WHERE `id` = $id;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
        }
    }

    public function testInactiveUser() {
        $user = User::withId(899);
        $user->login(false);
        $userInfo = $user->getDataArray();
        $this->assertStringStartsNotWith(date('Y-m-d'), $userInfo['lastLogin']);
        //TODO - check no session
        //TODO - check no cookies
    }

    public function testActiveUser() {
        date_default_timezone_set("America/New_York");
        $user = User::withId(4);
        $user->login(false);
        $userInfo = $user->getDataArray();
        $this->assertStringStartsWith(date('Y-m-d H:i'), $userInfo['lastLogin']);
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 4 ORDER BY time DESC LIMIT 1;");
        $this->assertEquals('Logged In', $log['action']);
        //TODO - check session
        //TODO - check no cookies
    }

    public function testRememberMeNoCookies() {
        date_default_timezone_set("America/New_York");
        $user = User::withId(4);
        $user->login(true);
        $userInfo = $user->getDataArray();
        $this->assertStringStartsWith(date('Y-m-d H:i'), $userInfo['lastLogin']);
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 4 ORDER BY time DESC LIMIT 1;");
        $this->assertEquals('Logged In', $log['action']);
        //TODO - check session
        //TODO - check no cookies
    }

    public function testRememberMeNoArray() {
        try {
            $_COOKIE['CookiePreferences'] = '';
            date_default_timezone_set("America/New_York");
            $user = User::withId(4);
            $user->login(true);
            $userInfo = $user->getDataArray();
            $this->assertStringStartsWith(date('Y-m-d H:i'), $userInfo['lastLogin']);
            $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 4 ORDER BY time DESC LIMIT 1;");
            $this->assertEquals('Logged In', $log['action']);
            //TODO - check session
            //TODO - check no cookies
        } finally {
            unset($_COOKIE['CookiePreferences']);
        }
    }

    public function testRememberMeNoPreferences() {
        try {
            $_COOKIE['CookiePreferences'] = '["analytics"]';
            date_default_timezone_set("America/New_York");
            $user = User::withId(4);
            $user->login(true);
            $userInfo = $user->getDataArray();
            $this->assertStringStartsWith(date('Y-m-d H:i'), $userInfo['lastLogin']);
            $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 4 ORDER BY time DESC LIMIT 1;");
            $this->assertEquals('Logged In', $log['action']);
            //TODO - check session
            //TODO - check no cookies
        } finally {
            unset($_COOKIE['CookiePreferences']);
        }
    }

    public function testRememberMe() {
        try {
            $_COOKIE['CookiePreferences'] = '["preferences", "analytics"]';
            date_default_timezone_set("America/New_York");
            $user = User::withId(4);
            $user->login(true);
            $userInfo = $user->getDataArray();
            $this->assertStringStartsWith(date('Y-m-d H:i'), $userInfo['lastLogin']);
            $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 4 ORDER BY time DESC LIMIT 1;");
            $this->assertEquals('Logged In', $log['action']);
            //TODO - check session
            //TODO - check cookies
        } finally {
            unset($_COOKIE['CookiePreferences']);
        }
    }

    public function testSetResetCode() {
        try {
            $user = User::withId(4);
            $code = $user->setResetCode();
            $this->assertEquals($code, $this->sql->getRow("SELECT * FROM users WHERE id = 4;")['resetKey']);
        } finally {
            $this->sql->executeStatement("UPDATE users SET resetKey=NULL WHERE id=4;");
        }
    }

    public function testBadUserUpdateUser() {
        try {
            $_COOKIE ['hash'] = "5510b5e6fffd897c234cafe499f76146";
            $user = User::withId(4);
            $user->update(array());
        } catch (Exception $e) {
            $this->assertEquals('User not authorized to update user', $e->getMessage());
        } finally {
            unset( $_COOKIE ['hash']);
        }
    }

    public function testUpdateUserNoEmail() {
        try {
            $_COOKIE ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $user = User::withId(4);
            $user->update(array());
        } catch (Exception $e) {
            $this->assertEquals('Email is required', $e->getMessage());
        } finally {
            unset( $_COOKIE ['hash']);
        }
    }

    public function testUpdateUserBlankEmail() {
        $params = [
            'email' => ''
        ];
        try {
            $_COOKIE ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $user = User::withId(4);
            $user->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Email can not be blank', $e->getMessage());
        } finally {
            unset( $_COOKIE ['hash']);
        }
    }

    public function testUpdateUserDuplicateEmail() {
        $params = [
            'email' => 'msaperst@gmail.com'
        ];
        try {
            $_COOKIE ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $user = User::withId(4);
            $user->update($params);
        } catch (Exception $e) {
            $this->assertEquals('That email already exists in the system: try logging in with it', $e->getMessage());
        } finally {
            unset( $_COOKIE ['hash']);
        }
    }

    public function testUpdateUserBadRole() {
        $params = [
            'email' => 'unique@gmail.com',
            'role' => 'foo'
        ];
        try {
            $_COOKIE ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $user = User::withId(4);
            $user->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Role is not valid', $e->getMessage());
        } finally {
            unset( $_COOKIE ['hash']);
        }
    }

    public function testUpdateUserSameEmail() {
        sleep( 1 );
        $params = [
            'email' => 'uploader@example.org'
        ];
        try {
            $_COOKIE ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $user = User::withId(4);
            $user->update($params);
            $userDetails = $user->getDataArray();
            $this->assertEquals(4, $userDetails['id']);
            $this->assertEquals('uploader', $userDetails['usr']);
            $this->assertEquals(md5('password'), $userDetails['pass']);
            $this->assertEquals('Upload', $userDetails['firstName']);
            $this->assertEquals('User', $userDetails['lastName']);
            $this->assertEquals('uploader@example.org', $userDetails['email']);
            $this->assertEquals('uploader', $userDetails['role']);
            $this->assertEquals('c90788c0e409eac6a95f6c6360d8dbf7', $userDetails['hash']);
            $this->assertEquals(1, $userDetails['active']);
            $this->assertNull($userDetails['resetKey']);
            $userLogs = $this->sql->getRow("SELECT * FROM `user_logs` WHERE user = 4 ORDER BY time DESC");
            $this->assertEquals(4, $userLogs['user']);
            $this->assertEquals('Updated User', $userLogs['action']);
            $this->assertNull($userLogs['what']);
            $this->assertNull($userLogs['album']);
        } finally {
            unset( $_COOKIE ['hash']);
        }
    }

    public function testUpdateUserAllBasicValuesNonAdmin() {
        sleep( 1 );
        $params = [
            'email' => 'upload@example.org',
            'active' => 0,
            'firstName' => 'u',
            'lastName' => 't',
            'role' => 'admin'
        ];
        try {
            $_COOKIE ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
            $user = User::withId(4);
            $user->update($params);
            $userDetails = $user->getDataArray();
            $this->assertEquals(4, $userDetails['id']);
            $this->assertEquals('uploader', $userDetails['usr']);
            $this->assertEquals(md5('password'), $userDetails['pass']);
            $this->assertEquals('u', $userDetails['firstName']);
            $this->assertEquals('t', $userDetails['lastName']);
            $this->assertEquals('upload@example.org', $userDetails['email']);
            $this->assertEquals('uploader', $userDetails['role']);
            $this->assertEquals('c90788c0e409eac6a95f6c6360d8dbf7', $userDetails['hash']);
            $this->assertEquals(1, $userDetails['active']);
            $this->assertNull($userDetails['resetKey']);
            $userLogs = $this->sql->getRow("SELECT * FROM `user_logs` WHERE user = 4 ORDER BY time DESC");
            $this->assertEquals(4, $userLogs['user']);
            $this->assertEquals('Updated User', $userLogs['action']);
            $this->assertNull($userLogs['what']);
            $this->assertNull($userLogs['album']);
        } finally {
            $this->sql->executeStatement("UPDATE users SET pass = '5f4dcc3b5aa765d61d8327deb882cf99', firstName = 'Upload', lastName = 'User', email = 'uploader@example.org', role = 'uploader', hash = 'c90788c0e409eac6a95f6c6360d8dbf7', active = 1 WHERE id = 4;");
            unset( $_COOKIE ['hash']);
        }
    }

    public function testUpdateUserAllBasicValuesAdmin() {
        sleep( 1 );
        $params = [
            'email' => 'upload@example.org',
            'active' => 0,
            'firstName' => 'u',
            'lastName' => 't',
            'role' => 'admin'
        ];
        try {
            $_COOKIE ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $user = User::withId(4);
            $user->update($params);
            $userDetails = $user->getDataArray();
            $this->assertEquals(4, $userDetails['id']);
            $this->assertEquals('uploader', $userDetails['usr']);
            $this->assertEquals(md5('password'), $userDetails['pass']);
            $this->assertEquals('u', $userDetails['firstName']);
            $this->assertEquals('t', $userDetails['lastName']);
            $this->assertEquals('upload@example.org', $userDetails['email']);
            $this->assertEquals('admin', $userDetails['role']);
            $this->assertEquals('c90788c0e409eac6a95f6c6360d8dbf7', $userDetails['hash']);
            $this->assertEquals(0, $userDetails['active']);
            $this->assertNull($userDetails['resetKey']);
            $userLogs = $this->sql->getRow("SELECT * FROM `user_logs` WHERE user = 4 ORDER BY time DESC");
            $this->assertEquals(4, $userLogs['user']);
            $this->assertEquals('Updated User', $userLogs['action']);
            $this->assertNull($userLogs['what']);
            $this->assertNull($userLogs['album']);
        } finally {
            $this->sql->executeStatement("UPDATE users SET pass = '5f4dcc3b5aa765d61d8327deb882cf99', firstName = 'Upload', lastName = 'User', email = 'uploader@example.org', role = 'uploader', hash = 'c90788c0e409eac6a95f6c6360d8dbf7', active = 1 WHERE id = 4;");
            unset( $_COOKIE ['hash']);
        }
    }

    public function testUpdateUserPasswordNoCurrent() {
        $params = [
            'email' => 'unique@gmail.com',
            'password' => 'newpassword'
        ];
        try {
            $_COOKIE ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
            $user = User::withId(4);
            $user->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Current password is required', $e->getMessage());
        } finally {
            unset( $_COOKIE ['hash']);
        }
    }

    public function testUpdateUserPasswordBlankCurrent() {
        $params = [
            'email' => 'unique@gmail.com',
            'password' => 'newpassword',
            'curPass' => ''
        ];
        try {
            $_COOKIE ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
            $user = User::withId(4);
            $user->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Current password can not be blank', $e->getMessage());
        } finally {
            unset( $_COOKIE ['hash']);
        }
    }

    public function testUpdateUserPasswordDoesNotMatch() {
        $params = [
            'email' => 'unique@gmail.com',
            'password' => 'newpassword',
            'curPass' => 'badPassword'
        ];
        try {
            $_COOKIE ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
            $user = User::withId(4);
            $user->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Current password does not match our records', $e->getMessage());
        } finally {
            unset( $_COOKIE ['hash']);
        }
    }

    public function testUpdateUserPasswordDoesMatch() {
        sleep( 1 );
        $params = [
            'email' => 'uploader@example.org',
            'password' => 'newpassword',
            'curPass' => 'password'
        ];
        try {
            $_COOKIE ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
            $user = User::withId(4);
            $user->update($params);
            $userDetails = $user->getDataArray();
            $this->assertEquals(4, $userDetails['id']);
            $this->assertEquals('uploader', $userDetails['usr']);
            $this->assertEquals(md5('newpassword'), $userDetails['pass']);
            $this->assertEquals('Upload', $userDetails['firstName']);
            $this->assertEquals('User', $userDetails['lastName']);
            $this->assertEquals('uploader@example.org', $userDetails['email']);
            $this->assertEquals('uploader', $userDetails['role']);
            $this->assertEquals('c90788c0e409eac6a95f6c6360d8dbf7', $userDetails['hash']);
            $this->assertEquals(1, $userDetails['active']);
            $this->assertNull($userDetails['resetKey']);
            $userLogs = $this->sql->getRow("SELECT * FROM `user_logs` WHERE user = 4 ORDER BY time DESC");
            $this->assertEquals(4, $userLogs['user']);
            $this->assertEquals('Updated User', $userLogs['action']);
            $this->assertNull($userLogs['what']);
            $this->assertNull($userLogs['album']);
        } finally {
            $this->sql->executeStatement("UPDATE users SET pass = '5f4dcc3b5aa765d61d8327deb882cf99', firstName = 'Upload', lastName = 'User', email = 'uploader@example.org', role = 'uploader', hash = 'c90788c0e409eac6a95f6c6360d8dbf7', active = 1 WHERE id = 4;");
            unset( $_COOKIE ['hash']);
        }
    }

    public function testUpdateUserPassword() {
        sleep( 1 );
        $params = [
            'email' => 'uploader@example.org',
            'password' => 'newpassword',
        ];
        try {
            $_COOKIE ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $user = User::withId(4);
            $user->update($params);
            $userDetails = $user->getDataArray();
            $this->assertEquals(4, $userDetails['id']);
            $this->assertEquals('uploader', $userDetails['usr']);
            $this->assertEquals(md5('newpassword'), $userDetails['pass']);
            $this->assertEquals('Upload', $userDetails['firstName']);
            $this->assertEquals('User', $userDetails['lastName']);
            $this->assertEquals('uploader@example.org', $userDetails['email']);
            $this->assertEquals('uploader', $userDetails['role']);
            $this->assertEquals('c90788c0e409eac6a95f6c6360d8dbf7', $userDetails['hash']);
            $this->assertEquals(1, $userDetails['active']);
            $this->assertNull($userDetails['resetKey']);
            $userLogs = $this->sql->getRow("SELECT * FROM `user_logs` WHERE user = 4 ORDER BY time DESC");
            $this->assertEquals(4, $userLogs['user']);
            $this->assertEquals('Updated User', $userLogs['action']);
            $this->assertNull($userLogs['what']);
            $this->assertNull($userLogs['album']);
        } finally {
            $this->sql->executeStatement("UPDATE users SET pass = '5f4dcc3b5aa765d61d8327deb882cf99', firstName = 'Upload', lastName = 'User', email = 'uploader@example.org', role = 'uploader', hash = 'c90788c0e409eac6a95f6c6360d8dbf7', active = 1 WHERE id = 4;");
            unset( $_COOKIE ['hash']);
        }
    }
}