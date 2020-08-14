<?php

namespace coverage\integration;

use Album;
use Exception;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class AlbumIntegrationTest extends TestCase {
    private $sql;

    public function setUp() {
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('898', 'sample-album', 'sample album for testing', '', 5);");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('899', 'sample-album', 'sample album for testing', 'sample', 4, '123');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '898', '', '1', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '899', '', '1', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (3, '898');");
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (1, '899');");
        $oldmask = umask(0);
        mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums');
        mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample');
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample', 0777);
        touch(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/sample.jpg');
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/sample.jpg', 0777);
        umask($oldmask);
    }

    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 898;");
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 899;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 898;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 899;");
        $this->sql->executeStatement("DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = 898;");
        $this->sql->executeStatement("DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = 899;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
        system("rm -rf " . escapeshellarg(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums'));
    }

    public function testNullAlbumId() {
        try {
            new Album(NULL);
        } catch (Exception $e) {
            $this->assertEquals("Album id is required", $e->getMessage());
        }
    }

    public function testBlankAlbumId() {
        try {
            new Album("");
        } catch (Exception $e) {
            $this->assertEquals("Album id can not be blank", $e->getMessage());
        }
    }

    public function testLetterAlbumId() {
        try {
            new Album("a");
        } catch (Exception $e) {
            $this->assertEquals("Album id does not match any albums", $e->getMessage());
        }
    }

    public function testBadAlbumId() {
        try {
            new Album(8999);
        } catch (Exception $e) {
            $this->assertEquals("Album id does not match any albums", $e->getMessage());
        }
    }

    public function testBadStringAlbumId() {
        try {
            new Album("8999");
        } catch (Exception $e) {
            $this->assertEquals("Album id does not match any albums", $e->getMessage());
        }
    }

    public function testGetId() {
        $album = new Album('899');
        $this->assertEquals(899, $album->getId());
    }

    public function testGetName() {
        $album = new Album('899');
        $this->assertEquals('sample-album', $album->getName());
    }

    public function testGetOwner() {
        $album = new Album('899');
        $this->assertEquals(4, $album->getOwner());
    }

    public function testAllDataLoaded() {
        date_default_timezone_set("America/New_York");
        $album = new Album(899);
        $albumInfo = $album->getDataArray();
        $this->assertEquals(899, $albumInfo['id']);
        $this->assertEquals('sample-album', $albumInfo['name']);
        $this->assertEquals('sample album for testing', $albumInfo['description']);
        $this->assertStringStartsWith(date("Y-m-d H:i"), $albumInfo['date']);
        $this->assertNull($albumInfo['lastAccessed']);
        $this->assertEquals('sample', $albumInfo['location']);
        $this->assertEquals('123', $albumInfo['code']);
        $this->assertEquals(4, $albumInfo['owner']);
        $this->assertEquals(0, $albumInfo['images']);
    }

    public function testCanUserGetDataNobody() {
        $album = new Album(899);
        $this->assertFalse($album->canUserGetData());
    }

    public function testCanUserGetDataAdmin() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = new Album(899);
        $this->assertTrue($album->canUserGetData());
        unset($_SESSION['hash']);
    }

    public function testCanUserGetDataOwner() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $album = new Album(899);
        $this->assertTrue($album->canUserGetData());
        unset($_SESSION['hash']);
    }

    public function testCanUserGetDataOtherUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $album = new Album(899);
        $this->assertFalse($album->canUserGetData());
        unset($_SESSION['hash']);
    }

    public function testIsSearchedForNoCode() {
        $album = new Album(898);
        $this->assertFalse($album->isSearchedFor());
    }

    public function testIsSearchedForNoSession() {
        $album = new Album(899);
        $this->assertFalse($album->isSearchedFor());
    }

    public function testIsSearchedForNoSessionSearch() {
        $_SESSION['search'] = array();
        $album = new Album(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_SESSION['search']);
    }

    public function testIsSearchedForEmptySessionSearch() {
        $_SESSION['searched'] = 'a';
        $album = new Album(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_SESSION['searched']);
    }

    public function testIsSearchedForEmptySessionSearchArray() {
        $_SESSION['searched'] = array();
        $album = new Album(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_SESSION['searched']);
    }

    public function testIsSearchedForNoSessionMatch() {
        $_SESSION['searched']['899'] = '5';
        $album = new Album(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_SESSION['searched']);
    }

    public function testIsSearchedForSessionMatch() {
        $_SESSION['searched']['899'] = md5("album123");
        $album = new Album(899);
        $this->assertTrue($album->isSearchedFor());
        unset($_SESSION['searched']);
    }


    public function testIsSearchedForNoCookie() {
        $album = new Album(899);
        $this->assertFalse($album->isSearchedFor());
    }

    public function testIsSearchedForNoCookieSearch() {
        $_COOKIE['search'] = array();
        $album = new Album(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_COOKIE['search']);
    }

    public function testIsSearchedForEmptyCookieSearch() {
        $_COOKIE['searched'] = 'a';
        $album = new Album(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_COOKIE['searched']);
    }

    public function testIsSearchedForEmptyCookieSearchArray() {
        $_COOKIE['searched'] = json_encode(array());
        $album = new Album(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_COOKIE['searched']);
    }

    public function testIsSearchedForNoCookieMatch() {
        $searched = array();
        $searched[899] = '5';
        $_COOKIE['searched'] = json_encode($searched);
        $album = new Album(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_COOKIE['searched']);
    }

    public function testIsSearchedForCookieMatch() {
        $searched = array();
        $searched[899] = md5("album123");
        $_COOKIE['searched'] = json_encode($searched);
        $album = new Album(899);
        $this->assertTrue($album->isSearchedFor());
        unset($_COOKIE['searched']);
    }

    public function testCanUserAccessNobody() {
        $album = new Album(899);
        $this->assertFalse($album->canUserAccess());
    }

    public function testCanUserAccessAdmin() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = new Album(899);
        $this->assertTrue($album->canUserAccess());
        unset($_SESSION['hash']);
    }

    public function testCanUserAccessOwner() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $album = new Album(899);
        $this->assertTrue($album->canUserAccess());
        unset($_SESSION['hash']);
    }

    public function testCanUserAccessOtherUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $album = new Album(899);
        $this->assertFalse($album->canUserAccess());
        unset($_SESSION['hash']);
    }

    public function testCanUserAccessAddedUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $album = new Album(898);
        $this->assertTrue($album->canUserAccess());
        unset($_SESSION['hash']);
    }

    public function testCanUserAccessSearched() {
        $_SESSION['searched']['899'] = md5("album123");
        $album = new Album(899);
        $this->assertTrue($album->canUserAccess());
        unset($_SESSION['searched']);
    }

    public function testDeleteNoAccess() {
        $album = new Album(899);
        try {
            $album->delete();
        } catch (Exception $e) {
            $this->assertEquals("User not authorized to delete album", $e->getMessage());
        }
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `albums` WHERE `albums`.`id` = 899;"));
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `album_images` WHERE `album_images`.`album` = 899;"));
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = 899;"));
        $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/sample.jpg'));
    }

    public function testDeleteNoLocation() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = new Album(898);
        $album->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `albums` WHERE `albums`.`id` = 898;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `album_images` WHERE `album_images`.`album` = 898;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = 898;"));
    }

    public function testDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = new Album(899);
        $album->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `albums` WHERE `albums`.`id` = 899;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `album_images` WHERE `album_images`.`album` = 899;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = 899;"));
        $this->assertFalse(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/albums/sample/sample.jpg'));
    }
}