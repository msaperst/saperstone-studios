<?php

namespace coverage\integration;

use User;
use Exception;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UserIntegrationTest extends TestCase {
    private $sql;

    public function setUp() {
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `users` (`id`, `usr`, `pass`, `firstName`, `lastName`, `email`, `role`, `hash`, `active`, `created`, `lastLogin`, `resetKey`) VALUES (899, 'test', 'user', 'test', 'user', 'test@example.com', 'downloader', '12345', '0', '2020-01-01 10:10:10', '2020-01-01 20:10:10', '123')");
    }

    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM `users` WHERE `users`.`id` = 899;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testNullUserId() {
        try {
            new User(NULL);
        } catch (Exception $e) {
            $this->assertEquals("User id is required", $e->getMessage());
        }
    }

    public function testBlankUserId() {
        try {
            new User("");
        } catch (Exception $e) {
            $this->assertEquals("User id can not be blank", $e->getMessage());
        }
    }

    public function testLetterUserId() {
        try {
            new User("a");
        } catch (Exception $e) {
            $this->assertEquals("User id does not match any users", $e->getMessage());
        }
    }

    public function testBadUserId() {
        try {
            new User(8999);
        } catch (Exception $e) {
            $this->assertEquals("User id does not match any users", $e->getMessage());
        }
    }

    public function testBadStringUserId() {
        try {
            new User("8999");
        } catch (Exception $e) {
            $this->assertEquals("User id does not match any users", $e->getMessage());
        }
    }

    public function testGetId() {
        $user = new User('899');
        $this->assertEquals(899, $user->getId());
    }

    public function testGetUsr() {
        $user = new User('899');
        $this->assertEquals('test', $user->getUsr());
    }

    public function testGetHash() {
        $user = new User('899');
        $this->assertEquals('12345', $user->getHash());
    }

    public function testGetRole() {
        $user = new User('899');
        $this->assertEquals('downloader', $user->getRole());
    }

    public function testGetFirstName() {
        $user = new User('899');
        $this->assertEquals('test', $user->getFirstName());
    }

    public function testGetLastName() {
        $user = new User('899');
        $this->assertEquals('user', $user->getLastName());
    }

    public function testGetEmail() {
        $user = new User('899');
        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function testAllData() {
        $user = new User(899);
        $userInfo = $user->getDataArray();
        $this->assertEquals(899, $userInfo['id']);
        $this->assertEquals('test', $userInfo['usr']);
        $this->assertEquals('user', $userInfo['pass']);
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
        $user = new User(899);
        try {
            $user->delete();
        } catch (Exception $e) {
            $this->assertEquals("User not authorized to delete user", $e->getMessage());
        }
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `users` WHERE `users`.`id` = 899;"));
    }

    public function testDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $user = new User(899);
        $user->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `users` WHERE `users`.`id` = 899;"));
    }
}