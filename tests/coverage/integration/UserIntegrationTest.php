<?php

namespace coverage\integration;

use BadUserException;
use CustomAsserts;
use PHPUnit\Framework\TestCase;
use Sql;
use SqlException;
use User;
use UserException;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UserIntegrationTest extends TestCase {
    private Sql $sql;

    private string $sessionHash;

    private string $cookieHash;

    private string $cookiePreferences;

    private string $usr;

    private int $id;

    /**
     * @throws SqlException
     */
    public function setUp(): void {
        if (isset($_SESSION ['hash'])) {
            $this->sessionHash = $_SESSION ['hash'];
        }
        if (isset($_COOKIE ['hash'])) {
            $this->cookieHash = $_COOKIE ['hash'];
        }
        if (isset($_SESSION ['usr'])) {
            $this->usr = $_SESSION ['usr'];
        }
        if (isset($_COOKIE['CookiePreferences'])) {
            $this->cookiePreferences = $_COOKIE['CookiePreferences'];
        }
        unset($this->id);

        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `users` (`id`, `usr`, `pass`, `firstName`, `lastName`, `email`, `role`, `hash`, `active`, `created`, `lastLogin`, `resetKey`) VALUES (899, 'test', '" . md5('user') . "', 'test', 'user', 'test@example.com', 'downloader', '12345', '0', '2020-01-01 10:10:10', '2020-01-01 20:10:10', '123')");
    }

    /**
     * @throws SqlException
     */
    public function tearDown(): void {
        if (isset($this->sessionHash)) {
            $_SESSION ['hash'] = $this->sessionHash;
        } else {
            unset($_SESSION ['hash']);
        }
        if (isset($this->cookieHash)) {
            $_COOKIE ['hash'] = $this->cookieHash;
        } else {
            unset($_COOKIE ['hash']);
        }
        if (isset($this->usr)) {
            $_SESSION ['usr'] = $this->usr;
        } else {
            unset($_SESSION ['usr']);
        }
        if (isset($this->cookiePreferences)) {
            $_COOKIE ['cookiePreferences'] = $this->cookiePreferences;
        } else {
            unset($_COOKIE['cookiePreferences']);
        }
        if (isset($this->id)) {
            $this->sql->executeStatement("DELETE FROM `users` WHERE `id` = $this->id;");
            $this->sql->executeStatement("DELETE FROM `user_logs` WHERE `user` = $this->id;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
            unset($this->id);
        }
        $this->sql->executeStatement("DELETE FROM `users` WHERE `users`.`id` = 899;");
        $this->sql->executeStatement("DELETE FROM `user_logs` WHERE `user` = 4");
        $this->sql->executeStatement("DELETE FROM `users` WHERE `users`.`usr` = 'testUser';");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");

        $this->sql->executeStatement("UPDATE users SET pass = '5f4dcc3b5aa765d61d8327deb882cf99', firstName = 'Upload', lastName = 'User', email = 'uploader@example.org', role = 'uploader', hash = 'c90788c0e409eac6a95f6c6360d8dbf7', active = 1 WHERE id = 4;");
        $this->sql->executeStatement("UPDATE users SET resetKey=NULL WHERE id=4;");
        $this->sql->disconnect();
    }

    public function testNullUserId() {
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('User id is required');
        User::withId(NULL);
    }

    public function testBlankUserId() {
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('User id can not be blank');
        User::withId("");
    }

    // TODO - need to redo this test
//    public function testLetterUserId() {
//        $this->expectException(BadUserException::class);
//        $this->expectExceptionMessage('User id does not match any users');
//        User::withId("r");
//    }

    public function testBadUserId() {
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('User id does not match any users');
        User::withId(8999);
    }

    public function testBadStringUserId() {
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('User id does not match any users');
        User::withId("8999");
    }

    public function testFromResetNoMatch() {
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Credentials do not match our records');
        User::fromReset(NULL, '123');
    }

    /**
     * @throws BadUserException
     */
    public function testFromResetMatch() {
        $user = User::fromReset('test@example.com', '123');
        $this->assertEquals(899, $user->getId());
    }

    public function testFromLoginNoMatch() {
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Credentials do not match our records');
        User::fromLogin('hey', '123');
    }

    /**
     * @throws BadUserException
     */
    public function testFromLoginMatch() {
        $user = User::fromLogin('test', 'user');
        $this->assertEquals(899, $user->getId());
    }

    public function testFromEmailNoMatch() {
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Credentials do not match our records');
        User::fromEmail('random@email.com');
    }

    /**
     * @throws BadUserException
     */
    public function testFromEmailMatch() {
        $user = User::fromEmail('msaperst@gmail.com');
        $this->assertEquals(1, $user->getId());
    }

    /**
     * @throws BadUserException
     */
    public function testGetId() {
        $user = User::withId('899');
        $this->assertEquals(899, $user->getId());
    }

    /**
     * @throws BadUserException
     */
    public function testGetUsr() {
        $user = User::withId('899');
        $this->assertEquals('test', $user->getUsername());
    }

    /**
     * @throws BadUserException
     */
    public function testGetHash() {
        $user = User::withId('899');
        $this->assertEquals('12345', $user->getHash());
    }

    /**
     * @throws BadUserException
     */
    public function testGetActiveFalse() {
        $user = User::withId('899');
        $this->assertFalse($user->isActive());
    }

    /**
     * @throws BadUserException
     */
    public function testGetActiveTrue() {
        $user = User::withId('1');
        $this->assertTrue($user->isActive());
    }

    /**
     * @throws BadUserException
     */
    public function testGetRole() {
        $user = User::withId('899');
        $this->assertEquals('downloader', $user->getRole());
    }

    /**
     * @throws BadUserException
     */
    public function testGetFirstName() {
        $user = User::withId('899');
        $this->assertEquals('test', $user->getFirstName());
    }

    /**
     * @throws BadUserException
     */
    public function testGetLastName() {
        $user = User::withId('899');
        $this->assertEquals('user', $user->getLastName());
    }

    /**
     * @throws BadUserException
     */
    public function testGetEmail() {
        $user = User::withId('899');
        $this->assertEquals('test@example.com', $user->getEmail());
    }

    /**
     * @throws BadUserException
     */
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

    /**
     * @throws BadUserException
     */
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

    /**
     * @throws SqlException
     * @throws BadUserException
     */
    public function testUpdatePasswordNoAccess() {
        $user = User::withId(899);
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User not authorized to update user');
        $user->updatePassword(NULL);
    }

    /**
     * @throws SqlException
     * @throws UserException
     */
    public function testUpdatePasswordNoPassword() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Password is required');
        $user = User::withId(899);
        $user->updatePassword(NULL);
    }

    /**
     * @throws SqlException
     * @throws UserException
     */
    public function testUpdatePasswordBlankPassword() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Password can not be blank');
        $user = User::withId(899);
        $user->updatePassword(['password' => '']);
    }

    /**
     * @throws BadUserException
     * @throws SqlException
     */
    public function testDeleteNoAccess() {
        $user = User::withId(899);
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User not authorized to delete user');
        $user->delete();
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `users` WHERE `users`.`id` = 899;"));
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     * @throws UserException
     */
    public function testDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $user = User::withId(899);
        $user->delete();
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `users` WHERE `users`.`id` = 899;"));
    }

    /**
     * @throws BadUserException
     */
    public function testNoUser() {
        $user = User::fromSystem();
        $this->assertFalse($user->isLoggedIn());
        $this->assertEquals('', $user->getId());
        $this->assertEquals('', $user->getIdentifier());
        $this->assertEquals('', $user->getUsername());
        $this->assertEquals('', $user->getRole());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isUploader());
        $this->assertEquals('', $user->getFirstName());
        $this->assertEquals('', $user->getLastName());
        $this->assertEquals('', $user->getName());
        $this->assertEquals('', $user->getEmail());
    }

    public function testBadSessionUser() {
        $_SESSION ['hash'] = "1234567890abcdef1234567890abcdef";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Invalid user token provided');
        User::fromSystem();
    }

    public function testBadCookieUser() {
        $_COOKIE ['hash'] = "1234567890abcdef1234567890abcdef";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Invalid user token provided');
        User::fromSystem();
    }

    /**
     * @throws BadUserException
     */
    public function testAdminUser() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $user = User::fromSystem();
        $this->assertTrue($user->isLoggedIn());
        $this->assertEquals(1, $user->getId());
        $this->assertEquals(1, $user->getIdentifier());
        $this->assertEquals('msaperst', $user->getUsername());
        $this->assertEquals('admin', $user->getRole());
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isUploader());
        $this->assertEquals('Max', $user->getFirstName());
        $this->assertEquals('Saperstone', $user->getLastName());
        $this->assertEquals('Max Saperstone', $user->getName());
        $this->assertEquals('msaperst@gmail.com', $user->getEmail());
    }

    /**
     * @throws BadUserException
     */
    public function testDownloadUser() {
        $_COOKIE ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $user = User::fromSystem();
        $this->assertTrue($user->isLoggedIn());
        $this->assertEquals(3, $user->getId());
        $this->assertEquals(3, $user->getIdentifier());
        $this->assertEquals('downloader', $user->getUsername());
        $this->assertEquals('downloader', $user->getRole());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isUploader());
        $this->assertEquals('Download', $user->getFirstName());
        $this->assertEquals('User', $user->getLastName());
        $this->assertEquals('Download User', $user->getName());
        $this->assertEquals('email@example.org', $user->getEmail());
    }

    /**
     * @throws BadUserException
     */
    public function testUploadUser() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $user = User::fromSystem();
        $this->assertTrue($user->isLoggedIn());
        $this->assertEquals(4, $user->getId());
        $this->assertEquals(4, $user->getIdentifier());
        $this->assertEquals('uploader', $user->getUsername());
        $this->assertEquals('uploader', $user->getRole());
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isUploader());
        $this->assertEquals('Upload', $user->getFirstName());
        $this->assertEquals('User', $user->getLastName());
        $this->assertEquals('Upload User', $user->getName());
        $this->assertEquals('uploader@example.org', $user->getEmail());
    }

    /**
     * @throws BadUserException
     */
    public function testGeneratePassword() {
        $user = User::fromSystem();
        $this->assertEquals(20, strlen($user->generatePassword()));
        $this->assertEquals(1, preg_match("/^([a-zA-Z0-9]{20})$/", $user->generatePassword()));
    }

    public function testNewUserNoUsername() {
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Username is required');
        User::withParams(array());
    }

    public function testNewUserBlankUsername() {
        $params = [
            'username' => ''
        ];
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Username can not be blank');
        User::withParams($params);
    }

    public function testNewUserUsernameToShort() {
        $params = [
            'username' => '123'
        ];
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Username is not valid: it must be at least 5 characters, and contain only letters numbers and underscores');
        User::withParams($params);
    }

    public function testNewUserUsernameBadChars() {
        $params = [
            'username' => '123$5K{;'
        ];
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Username is not valid: it must be at least 5 characters, and contain only letters numbers and underscores');
        User::withParams($params);
    }

    public function testNewUserUsernameDuplicate() {
        $params = [
            'username' => 'msaperst'
        ];
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('That username already exists in the system');
        User::withParams($params);
    }

    public function testNewUserNoEmail() {
        $params = [
            'username' => 'testUser'
        ];
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Email is required');
        User::withParams($params);
    }

    public function testNewUserBlankEmail() {
        $params = [
            'username' => 'testUser',
            'email' => ''
        ];
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Email can not be blank');
        User::withParams($params);
    }

    public function testNewUserInvalidEmail() {
        $params = [
            'username' => 'testUser',
            'email' => 'max@max'
        ];
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Email is not valid');
        User::withParams($params);
    }

    public function testNewUserDuplicateEmail() {
        $params = [
            'username' => 'testUser',
            'email' => 'msaperst@gmail.com'
        ];
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('That email already exists in the system: try logging in with it');
        User::withParams($params);
    }

    public function testNewUserNoPassword() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
        ];
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Password is required');
        User::withParams($params);
    }

    public function testNewUserBlankPassword() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => ''
        ];
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Password can not be blank');
        User::withParams($params);
    }

    /**
     * @throws BadUserException
     */
    public function testNewUserAdminPasswordNotNeeded() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $user = User::withParams($params);
        $this->assertEquals(20, strlen($user->getPassword()));
    }

    /**
     * @throws BadUserException
     */
    public function testNewUserBasics() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $user = User::withParams($params);
        $this->assertEquals('12345', $user->getPassword());
    }

    /**
     * @throws BadUserException
     */
    public function testNewUserDownloaderDefaultRole() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345'
        ];
        $user = User::withParams($params);
        $this->assertEquals('downloader', $user->getRole());
    }

    /**
     * @throws BadUserException
     */
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

    /**
     * @throws BadUserException
     */
    public function testNewUserAdminCanSetAdmin() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'role' => 'admin'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $user = User::withParams($params);
        $this->assertEquals('admin', $user->getRole());
    }

    public function testNewUserAdminCantSetBadRole() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'role' => 'administrator'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Role is not valid');
        User::withParams($params);
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     */
    public function testNewUserActiveDefault() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345'
        ];
        $user = User::withParams($params);
        $this->id = $user->create();
        $this->assertEquals(1, $user->getDataArray()['active']);
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     */
    public function testNewUserOnlyAdminCanSetInactive() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345',
            'active' => '0'
        ];
        $user = User::withParams($params);
        $this->id = $user->create();
        $this->assertEquals(1, $user->getDataArray()['active']);
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     */
    public function testNewUserAdminCanSetInactive() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345',
            'active' => '0'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $user = User::withParams($params);
        $this->id = $user->create();
        $this->assertEquals(0, $user->getDataArray()['active']);
    }

    /**
     * @throws BadUserException
     */
    public function testNewUserDefaultNoName() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345',
        ];
        $user = User::withParams($params);
        $this->assertEquals('', $user->getName());
    }

    /**
     * @throws BadUserException
     */
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

    /**
     * @throws BadUserException
     */
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

    /**
     * @throws BadUserException
     */
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

    /**
     * @throws BadUserException
     */
    public function testNewUserHash() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345'
        ];
        $user = User::withParams($params);
        $this->assertEquals(md5('testUser12345'), $user->getHash());
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     */
    public function testNewUserFromAdmin() {
        date_default_timezone_set("America/New_York");
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345'
        ];
        $_COOKIE ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $user = User::withParams($params);
        $this->id = $user->create();
        $userLogs = $this->sql->getRow("SELECT * FROM `user_logs` WHERE user = $this->id ORDER BY time DESC");
        $this->assertEquals($this->id, $userLogs['user']);
        $this->assertEquals('Created', $userLogs['action']);
        $this->assertNull($userLogs['what']);
        $this->assertNull($userLogs['album']);
        $this->assertEquals($this->id, $user->getId());
        $userDetails = $user->getDataArray();
        $this->assertEquals($this->id, $userDetails['id']);
        $this->assertEquals('testUser', $userDetails['usr']);
        $this->assertEquals(md5('12345'), $userDetails['pass']);
        $this->assertEquals('', $userDetails['firstName']);
        $this->assertEquals('', $userDetails['lastName']);
        $this->assertEquals('test@example.org', $userDetails['email']);
        $this->assertEquals('downloader', $userDetails['role']);
        $this->assertEquals(md5('testUser12345'), $userDetails['hash']);
        $this->assertEquals(1, $userDetails['active']);
        CustomAsserts::timeWithin(2, $userDetails['created']);
        $this->assertNull($userDetails['lastLogin']);
        $this->assertNull($userDetails['resetKey']);
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     */
    public function testNewUser() {
        date_default_timezone_set("America/New_York");
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345'
        ];
        $user = User::withParams($params);
        $this->id = $user->create();
        $userLogs = $this->sql->getRow("SELECT * FROM `user_logs` WHERE user = $this->id ORDER BY time DESC");
        $this->assertEquals($this->id, $userLogs['user']);
        $this->assertEquals('Registered', $userLogs['action']);
        $this->assertNull($userLogs['what']);
        $this->assertNull($userLogs['album']);
        $this->assertEquals($this->id, $user->getId());
        $userDetails = $user->getDataArray();
        $this->assertEquals($this->id, $userDetails['id']);
        $this->assertEquals('testUser', $userDetails['usr']);
        $this->assertEquals(md5('12345'), $userDetails['pass']);
        $this->assertEquals('', $userDetails['firstName']);
        $this->assertEquals('', $userDetails['lastName']);
        $this->assertEquals('test@example.org', $userDetails['email']);
        $this->assertEquals('downloader', $userDetails['role']);
        $this->assertEquals(md5('testUser12345'), $userDetails['hash']);
        $this->assertEquals(1, $userDetails['active']);
        CustomAsserts::timeWithin(2, $userDetails['created']);
        $this->assertNull($userDetails['lastLogin']);
        $this->assertNull($userDetails['resetKey']);
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     */
    public function testInactiveUser() {
        $user = User::withId(899);
        $user->login(false);
        $userInfo = $user->getDataArray();
        $this->assertStringStartsNotWith(date('Y-m-d'), $userInfo['lastLogin']);
        //TODO - check no session
        //TODO - check no cookies
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     */
    public function testActiveUser() {
        date_default_timezone_set("America/New_York");
        $user = User::withId(4);
        $user->login(false);
        $userInfo = $user->getDataArray();
        CustomAsserts::timeWithin(2, $userInfo['lastLogin']);
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 4 ORDER BY time DESC LIMIT 1;");
        $this->assertEquals('Logged In', $log['action']);
        //TODO - check session
        //TODO - check no cookies
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     */
    public function testRememberMeNoCookies() {
        date_default_timezone_set("America/New_York");
        $user = User::withId(4);
        $user->login(true);
        $userInfo = $user->getDataArray();
        CustomAsserts::timeWithin(2, $userInfo['lastLogin']);
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 4 ORDER BY time DESC LIMIT 1;");
        $this->assertEquals('Logged In', $log['action']);
        //TODO - check session
        //TODO - check no cookies
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     */
    public function testRememberMeNoArray() {
        $_COOKIE['CookiePreferences'] = '';
        date_default_timezone_set("America/New_York");
        $user = User::withId(4);
        $user->login(true);
        $userInfo = $user->getDataArray();
        CustomAsserts::timeWithin(2, $userInfo['lastLogin']);
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 4 ORDER BY time DESC LIMIT 1;");
        $this->assertEquals('Logged In', $log['action']);
        //TODO - check session
        //TODO - check no cookies
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     */
    public function testRememberMeNoPreferences() {
        $_COOKIE['CookiePreferences'] = '["analytics"]';
        date_default_timezone_set("America/New_York");
        $user = User::withId(4);
        $user->login(true);
        $userInfo = $user->getDataArray();
        CustomAsserts::timeWithin(2, $userInfo['lastLogin']);
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 4 ORDER BY time DESC LIMIT 1;");
        $this->assertEquals('Logged In', $log['action']);
        //TODO - check session
        //TODO - check no cookies
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     */
    public function testRememberMe() {
        $_COOKIE['CookiePreferences'] = '["preferences", "analytics"]';
        date_default_timezone_set("America/New_York");
        $user = User::withId(4);
        $user->login(true);
        $userInfo = $user->getDataArray();
        CustomAsserts::timeWithin(2, $userInfo['lastLogin']);
        $log = $this->sql->getRow("SELECT * FROM `user_logs` WHERE `user` = 4 ORDER BY time DESC LIMIT 1;");
        $this->assertEquals('Logged In', $log['action']);
        //TODO - check session
        //TODO - check cookies
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     */
    public function testSetResetCode() {
        $user = User::withId(4);
        $code = $user->setResetCode();
        $this->assertEquals($code, $this->sql->getRow("SELECT * FROM users WHERE id = 4;")['resetKey']);
        $this->assertEquals($code, $user->getDataBasic()['resetKey']);
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     */
    public function testBadUserUpdateUser() {
        $_COOKIE ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('User not authorized to update user');
        $user = User::withId(4);
        $user->update(array());
    }

    /**
     * @throws SqlException
     * @throws UserException
     */
    public function testUpdateUserNoEmail() {
        $_COOKIE ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Email is required');
        $user = User::withId(4);
        $user->update(array());
    }

    /**
     * @throws SqlException
     * @throws UserException
     */
    public function testUpdateUserBlankEmail() {
        $params = [
            'email' => ''
        ];
        $_COOKIE ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Email can not be blank');
        $user = User::withId(4);
        $user->update($params);
    }

    /**
     * @throws SqlException
     * @throws UserException
     */
    public function testUpdateUserDuplicateEmail() {
        $params = [
            'email' => 'msaperst@gmail.com'
        ];
        $_COOKIE ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('That email already exists in the system: try logging in with it');
        $user = User::withId(4);
        $user->update($params);
    }

    /**
     * @throws SqlException
     * @throws UserException
     */
    public function testUpdateUserBadRole() {
        $params = [
            'email' => 'unique@gmail.com',
            'role' => 'foo'
        ];
        $_COOKIE ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Role is not valid');
        $user = User::withId(4);
        $user->update($params);
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     * @throws UserException
     */
    public function testUpdateUserSameEmail() {
        $params = [
            'email' => 'uploader@example.org'
        ];
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
    }

    /**
     * @throws BadUserException
     * @throws UserException
     * @throws SqlException
     */
    public function testUpdateUserAllBasicValuesNonAdmin() {
        $params = [
            'email' => 'upload@example.org',
            'active' => 0,
            'firstName' => 'u',
            'lastName' => 't',
            'role' => 'admin'
        ];
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
    }

    /**
     * @throws BadUserException
     * @throws UserException
     * @throws SqlException
     */
    public function testUpdateUserAllBasicValuesAdmin() {
        $params = [
            'email' => 'upload@example.org',
            'active' => 0,
            'firstName' => 'u',
            'lastName' => 't',
            'role' => 'admin'
        ];
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
    }

    /**
     * @throws SqlException
     * @throws UserException
     */
    public function testUpdateUserPasswordNoCurrent() {
        $params = [
            'email' => 'unique@gmail.com',
            'password' => 'newpassword'
        ];
        $_COOKIE ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Current password is required');
        $user = User::withId(4);
        $user->update($params);
    }

    /**
     * @throws SqlException
     * @throws UserException
     */
    public function testUpdateUserPasswordBlankCurrent() {
        $params = [
            'email' => 'unique@gmail.com',
            'password' => 'newpassword',
            'curPass' => ''
        ];
        $_COOKIE ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Current password can not be blank');
        $user = User::withId(4);
        $user->update($params);
    }

    /**
     * @throws SqlException
     * @throws UserException
     */
    public function testUpdateUserPasswordDoesNotMatch() {
        $params = [
            'email' => 'unique@gmail.com',
            'password' => 'newpassword',
            'curPass' => 'badPassword'
        ];
        $_COOKIE ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Current password does not match our records');
        $user = User::withId(4);
        $user->update($params);
    }

    /**
     * @throws SqlException
     * @throws UserException
     */
    public function testNoPasswordConfirmation() {
        $params = [
            'email' => 'uploader@example.org',
            'password' => 'newpassword',
            'curPass' => 'password'
        ];
        $_COOKIE ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Password confirmation is required');
        $user = User::withId(4);
        $user->update($params);
    }

    /**
     * @throws SqlException
     * @throws UserException
     */
    public function testBlankPasswordConfirmation() {
        $params = [
            'email' => 'uploader@example.org',
            'password' => 'newpassword',
            'curPass' => 'password',
            'passwordConfirm' => ''
        ];
        $_COOKIE ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Password confirmation can not be blank');
        $user = User::withId(4);
        $user->update($params);
    }

    /**
     * @throws SqlException
     * @throws UserException
     */
    public function testBadPasswordConfirmation() {
        $params = [
            'email' => 'uploader@example.org',
            'password' => 'newpassword',
            'curPass' => 'password',
            'passwordConfirm' => '123'
        ];
        $_COOKIE ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $this->expectException(BadUserException::class);
        $this->expectExceptionMessage('Password does not match password confirmation');
        $user = User::withId(4);
        $user->update($params);
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     * @throws UserException
     */
    public function testUpdateUserPasswordDoesMatch() {
        $params = [
            'email' => 'uploader@example.org',
            'password' => 'newpassword',
            'passwordConfirm' => 'newpassword',
            'curPass' => 'password'
        ];
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
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     * @throws UserException
     */
    public function testUpdateUserPassword() {
        $params = [
            'email' => 'uploader@example.org',
            'password' => 'newpassword',
            'passwordConfirm' => 'newpassword',
        ];
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
    }
}