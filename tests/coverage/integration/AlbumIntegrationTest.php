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
            Album::withId(NULL);
        } catch (Exception $e) {
            $this->assertEquals("Album id is required", $e->getMessage());
        }
    }

    public function testBlankAlbumId() {
        try {
            Album::withId("");
        } catch (Exception $e) {
            $this->assertEquals("Album id can not be blank", $e->getMessage());
        }
    }

    public function testLetterAlbumId() {
        try {
            Album::withId("a");
        } catch (Exception $e) {
            $this->assertEquals("Album id does not match any albums", $e->getMessage());
        }
    }

    public function testBadAlbumId() {
        try {
            Album::withId(8999);
        } catch (Exception $e) {
            $this->assertEquals("Album id does not match any albums", $e->getMessage());
        }
    }

    public function testBadStringAlbumId() {
        try {
            Album::withId("8999");
        } catch (Exception $e) {
            $this->assertEquals("Album id does not match any albums", $e->getMessage());
        }
    }

    public function testGetId() {
        $album = Album::withId('899');
        $this->assertEquals(899, $album->getId());
    }

    public function testGetName() {
        $album = Album::withId('899');
        $this->assertEquals('sample-album', $album->getName());
    }

    public function testGetOwner() {
        $album = Album::withId('899');
        $this->assertEquals(4, $album->getOwner());
    }

    public function testGetLocation() {
        $album = Album::withId('899');
        $this->assertEquals('sample', $album->getLocation());
    }

    public function testBasicDataLoaded() {
        date_default_timezone_set("America/New_York");
        $album = Album::withId(899);
        $albumInfo = $album->getDataBasic();
        $this->assertEquals(4, sizeOf($albumInfo));
        $this->assertEquals('sample-album', $albumInfo['name']);
        $this->assertEquals('sample album for testing', $albumInfo['description']);
        $this->assertStringStartsWith(date("Y-m-d H:i"), $albumInfo['date']);
        $this->assertEquals('123', $albumInfo['code']);
    }

    public function testAllDataLoaded() {
        date_default_timezone_set("America/New_York");
        $album = Album::withId(899);
        $albumInfo = $album->getDataArray();
        $this->assertEquals(9, sizeOf($albumInfo));
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
        $album = Album::withId(899);
        $this->assertFalse($album->canUserGetData());
    }

    public function testCanUserGetDataAdmin() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = Album::withId(899);
        $this->assertTrue($album->canUserGetData());
        unset($_SESSION['hash']);
    }

    public function testCanUserGetDataOwner() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $album = Album::withId(899);
        $this->assertTrue($album->canUserGetData());
        unset($_SESSION['hash']);
    }

    public function testCanUserGetDataOtherUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $album = Album::withId(899);
        $this->assertFalse($album->canUserGetData());
        unset($_SESSION['hash']);
    }

    public function testIsSearchedForNoCode() {
        $album = Album::withId(898);
        $this->assertFalse($album->isSearchedFor());
    }

    public function testIsSearchedForNoSession() {
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
    }

    public function testIsSearchedForNoSessionSearch() {
        $_SESSION['search'] = array();
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_SESSION['search']);
    }

    public function testIsSearchedForEmptySessionSearch() {
        $_SESSION['searched'] = 'a';
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_SESSION['searched']);
    }

    public function testIsSearchedForEmptySessionSearchArray() {
        $_SESSION['searched'] = array();
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_SESSION['searched']);
    }

    public function testIsSearchedForNoSessionMatch() {
        $_SESSION['searched']['899'] = '5';
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_SESSION['searched']);
    }

    public function testIsSearchedForSessionMatch() {
        $_SESSION['searched']['899'] = md5("album123");
        $album = Album::withId(899);
        $this->assertTrue($album->isSearchedFor());
        unset($_SESSION['searched']);
    }


    public function testIsSearchedForNoCookie() {
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
    }

    public function testIsSearchedForNoCookieSearch() {
        $_COOKIE['search'] = array();
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_COOKIE['search']);
    }

    public function testIsSearchedForEmptyCookieSearch() {
        $_COOKIE['searched'] = 'a';
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_COOKIE['searched']);
    }

    public function testIsSearchedForEmptyCookieSearchArray() {
        $_COOKIE['searched'] = json_encode(array());
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_COOKIE['searched']);
    }

    public function testIsSearchedForNoCookieMatch() {
        $searched = array();
        $searched[899] = '5';
        $_COOKIE['searched'] = json_encode($searched);
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_COOKIE['searched']);
    }

    public function testIsSearchedForCookieMatch() {
        $searched = array();
        $searched[899] = md5("album123");
        $_COOKIE['searched'] = json_encode($searched);
        $album = Album::withId(899);
        $this->assertTrue($album->isSearchedFor());
        unset($_COOKIE['searched']);
    }

    public function testCanUserAccessNobody() {
        $album = Album::withId(899);
        $this->assertFalse($album->canUserAccess());
    }

    public function testCanUserAccessAdmin() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = Album::withId(899);
        $this->assertTrue($album->canUserAccess());
        unset($_SESSION['hash']);
    }

    public function testCanUserAccessOwner() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $album = Album::withId(899);
        $this->assertTrue($album->canUserAccess());
        unset($_SESSION['hash']);
    }

    public function testCanUserAccessOtherUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $album = Album::withId(899);
        $this->assertFalse($album->canUserAccess());
        unset($_SESSION['hash']);
    }

    public function testCanUserAccessAddedUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $album = Album::withId(898);
        $this->assertTrue($album->canUserAccess());
        unset($_SESSION['hash']);
    }

    public function testCanUserAccessSearched() {
        $_SESSION['searched']['899'] = md5("album123");
        $album = Album::withId(899);
        $this->assertTrue($album->canUserAccess());
        unset($_SESSION['searched']);
    }

    public function testDeleteNoAccess() {
        $album = Album::withId(899);
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
        $album = Album::withId(898);
        $album->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `albums` WHERE `albums`.`id` = 898;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `album_images` WHERE `album_images`.`album` = 898;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = 898;"));
    }

    public function testDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = Album::withId(899);
        $album->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `albums` WHERE `albums`.`id` = 899;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `album_images` WHERE `album_images`.`album` = 899;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = 899;"));
        $this->assertFalse(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/albums/sample/sample.jpg'));
    }

    public function testWithParamsNullParams() {
        try {
            Album::withParams(NULL);
        } catch (Exception $e) {
            $this->assertEquals('Album name is required', $e->getMessage());
        }
    }

    public function testWithParamsNoName() {
        try {
            Album::withParams(array());
        } catch (Exception $e) {
            $this->assertEquals('Album name is required', $e->getMessage());
        }
    }

    public function testWithParamsBlankName() {
        $params = [
            'name' => ''
        ];
        try {
            Album::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Album name can not be blank', $e->getMessage());
        }
    }

    public function testWithParamsBadDate() {
        $params = [
            'name' => 'Sample Album',
            'date' => 'some date'
        ];
        try {
            Album::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Album date is not the correct format', $e->getMessage());
        }
    }

    public function testWithParamsRegularUser() {
        $params = [
            'name' => 'Sample Album',
            'date' => '2020-01-01'
        ];
        try {
            $album = Album::withParams($params);
            $album->create();
        } catch (Exception $e) {
            $this->assertEquals('User not authorized to create album', $e->getMessage());
        }
    }

    public function testWithParamsBadFolder() {
        try {
            $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
            rename(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/tmp_albums');
            $params = [
                'name' => 'Sample Album',
                'date' => '2020-01-01'
            ];
            $album = Album::withParams($params);
            $album->create();
        } catch (Exception $e) {
            $this->assertEquals('mkdir(): No such file or directory<br/>Unable to create album', $e->getMessage());
        } finally {
            unset($_SESSION['hash']);
            rename(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/tmp_albums', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums');
        }
    }

    public function testWithParamsBasic() {
        date_default_timezone_set("America/New_York");
        try {
            $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
            $params = [
                'name' => 'Album Name'
            ];
            $album = Album::withParams($params);
            $this->assertEquals('', $album->getId());
            $this->assertEquals('Album Name', $album->getName());
            $this->assertEquals('', $album->getOwner());
            $this->assertEquals('', $album->getLocation());
            $albumId = $album->create();
            $albumInfo = $album->getDataArray();
            $this->assertEquals(9, sizeOf($albumInfo));
            $this->assertEquals($albumId, $albumInfo['id']);
            $this->assertEquals('Album Name', $albumInfo['name']);
            $this->assertEquals('', $albumInfo['description']);
            $this->assertStringStartsWith(date('Y-m-d H:i:'), $albumInfo['date']);
            $this->assertNull($albumInfo['lastAccessed']);
            $this->assertStringStartsWith('AlbumName_' . substr(time(), 0, -1), $albumInfo['location']);
            $this->assertEquals('', $albumInfo['code']);
            $this->assertEquals('4', $albumInfo['owner']);
            $this->assertEquals(0, $albumInfo['images']);
            $albums = $this->sql->getRows("SELECT * FROM `albums_for_users` WHERE album = $albumId");
            $this->assertEquals(1, sizeof($albums));
            $this->assertEquals(4, $albums[0]['user']);
            $logs = $this->sql->getRow("SELECT * FROM `user_logs` WHERE album = $albumId ORDER BY time DESC LIMIT 1;");
            $this->assertEquals(4, $logs['user']);
            $this->assertEquals('Created Album', $logs['action']);
            $this->assertNull($logs['what']);
        } finally {
            unset($_SESSION['hash']);
            $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = $albumId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        }
    }

    public function testWithParamsAll() {
        date_default_timezone_set("America/New_York");
        try {
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $params = [
                'name' => 'Album Name',
                'description' => 'some description',
                'date' => '2020-01-01'
            ];
            $album = Album::withParams($params);
            $this->assertEquals('', $album->getId());
            $this->assertEquals('Album Name', $album->getName());
            $this->assertEquals('', $album->getOwner());
            $this->assertEquals('', $album->getLocation());
            $albumId = $album->create();
            $albumInfo = $album->getDataArray();
            $this->assertEquals(9, sizeOf($albumInfo));
            $this->assertEquals($albumId, $albumInfo['id']);
            $this->assertEquals('Album Name', $albumInfo['name']);
            $this->assertEquals('some description', $albumInfo['description']);
            $this->assertEquals('2020-01-01 00:00:00', $albumInfo['date']);
            $this->assertNull($albumInfo['lastAccessed']);
            $this->assertEquals('AlbumName_' . time(), $albumInfo['location']);
            $this->assertEquals('', $albumInfo['code']);
            $this->assertEquals('1', $albumInfo['owner']);
            $this->assertEquals(0, $albumInfo['images']);
        } finally {
            unset($_SESSION['hash']);
            $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = $albumId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        }
    }

    public function testUpdateNullParams() {
        try {
            $album = Album::withId(899);
            $album->update(NULL);
        } catch (Exception $e) {
            $this->assertEquals('Album name is required', $e->getMessage());
        }
    }

    public function testUpdateNoName() {
        try {
            $album = Album::withId(899);
            $album->update(array());
        } catch (Exception $e) {
            $this->assertEquals('Album name is required', $e->getMessage());
        }
    }

    public function testUpdateBlankName() {
        $params = [
            'name' => ''
        ];
        try {
            $album = Album::withId(899);
            $album->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Album name can not be blank', $e->getMessage());
        }
    }

    public function testUpdateBadDate() {
        $params = [
            'name' => 'Sample Album',
            'date' => 'some date'
        ];
        try {
            $album = Album::withId(899);
            $album->update($params);
        } catch (Exception $e) {
            $this->assertEquals('Album date is not the correct format', $e->getMessage());
        }
    }

    public function testUpdateUnAuthUser() {
        $params = [
            'name' => 'Sample Album',
            'date' => '2020-01-01'
        ];
        try {
            $album = Album::withId(899);
            $album->update($params);
        } catch (Exception $e) {
            $this->assertEquals('User not authorized to update album', $e->getMessage());
        }
    }

    public function testUpdateWrongUser() {
        try {
            $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
            $params = [
                'name' => 'Sample Album',
                'date' => '2020-01-01'
            ];
            $album = Album::withId(898);
            $album->update($params);
        } catch (Exception $e) {
            $this->assertEquals('User not authorized to update album', $e->getMessage());
        } finally {
            unset($_SESSION['hash']);
        }
    }

    public function testUpdateAdminBasic() {
        date_default_timezone_set("America/New_York");
        try {
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $params = [
                'name' => 'Sample Album'
            ];
            $album = Album::withId(898);
            $album->update($params);
            $albumInfo = $album->getDataArray();
            $this->assertEquals(9, sizeOf($albumInfo));
            $this->assertEquals(898, $albumInfo['id']);
            $this->assertEquals('Sample Album', $albumInfo['name']);
            $this->assertEquals('', $albumInfo['description']);
            $this->assertStringStartsWith(date('Y-m-d H:i:'), $albumInfo['date']);
            $this->assertNull($albumInfo['lastAccessed']);
            $this->assertEquals('', $albumInfo['location']);
            $this->assertEquals('', $albumInfo['code']);
            $this->assertEquals('5', $albumInfo['owner']);
            $this->assertEquals(0, $albumInfo['images']);
        } finally {
            unset($_SESSION['hash']);
        }
    }

    public function testUpdateAdminBasicCode() {
        try {
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $params = [
                'name' => 'Sample Album',
                'date' => '2020-01-01',
                'code' => '1234'
            ];
            $album = Album::withId(898);
            $album->update($params);
            $albumInfo = $album->getDataArray();
            $this->assertEquals(9, sizeOf($albumInfo));
            $this->assertEquals(898, $albumInfo['id']);
            $this->assertEquals('Sample Album', $albumInfo['name']);
            $this->assertEquals('', $albumInfo['description']);
            $this->assertEquals('2020-01-01 00:00:00', $albumInfo['date']);
            $this->assertNull($albumInfo['lastAccessed']);
            $this->assertEquals('', $albumInfo['location']);
            $this->assertEquals('1234', $albumInfo['code']);
            $this->assertEquals('5', $albumInfo['owner']);
            $this->assertEquals(0, $albumInfo['images']);
        } finally {
            unset($_SESSION['hash']);
        }
    }

    public function testUpdateAdminBasicEmptyCode() {
        try {
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $params = [
                'name' => 'Sample Album',
                'date' => '2020-01-01',
                'code' => ''
            ];
            $album = Album::withId(898);
            $album->update($params);
            $albumInfo = $album->getDataArray();
            $this->assertEquals(9, sizeOf($albumInfo));
            $this->assertEquals(898, $albumInfo['id']);
            $this->assertEquals('Sample Album', $albumInfo['name']);
            $this->assertEquals('', $albumInfo['description']);
            $this->assertEquals('2020-01-01 00:00:00', $albumInfo['date']);
            $this->assertNull($albumInfo['lastAccessed']);
            $this->assertEquals('', $albumInfo['location']);
            $this->assertEquals('', $albumInfo['code']);
            $this->assertEquals('5', $albumInfo['owner']);
            $this->assertEquals(0, $albumInfo['images']);
        } finally {
            unset($_SESSION['hash']);
        }
    }

    public function testUpdateAdminBasicNonAdminCode() {
        try {
            $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
            $params = [
                'name' => 'Sample Album',
                'date' => '2020-01-01',
                'code' => '1234'
            ];
            $album = Album::withId(899);
            $album->update($params);
            $albumInfo = $album->getDataArray();
            $this->assertEquals(9, sizeOf($albumInfo));
            $this->assertEquals(899, $albumInfo['id']);
            $this->assertEquals('Sample Album', $albumInfo['name']);
            $this->assertEquals('', $albumInfo['description']);
            $this->assertEquals('2020-01-01 00:00:00', $albumInfo['date']);
            $this->assertNull($albumInfo['lastAccessed']);
            $this->assertEquals('sample', $albumInfo['location']);
            $this->assertEquals('', $albumInfo['code']);
            $this->assertEquals('4', $albumInfo['owner']);
            $this->assertEquals(0, $albumInfo['images']);
        } finally {
            unset($_SESSION['hash']);
        }
    }

    public function testUpdateAdminBasicDuplicateCode() {
        try {
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $params = [
                'name' => 'Sample Album',
                'date' => '2020-01-01',
                'code' => '123'
            ];
            $album = Album::withId(898);
            $album->update($params);
        } catch ( Exception $e) {
            $this->assertEquals('Album code already exists', $e->getMessage());
            $albumInfo = $album->getDataArray();
            $this->assertEquals(9, sizeOf($albumInfo));
            $this->assertEquals(898, $albumInfo['id']);
            $this->assertEquals('Sample Album', $albumInfo['name']);
            $this->assertEquals('', $albumInfo['description']);
            $this->assertEquals('2020-01-01 00:00:00', $albumInfo['date']);
            $this->assertNull($albumInfo['lastAccessed']);
            $this->assertEquals('', $albumInfo['location']);
            $this->assertEquals('', $albumInfo['code']);
            $this->assertEquals('5', $albumInfo['owner']);
            $this->assertEquals(0, $albumInfo['images']);
        } finally {
            unset($_SESSION['hash']);
        }
    }

    public function testUpdateAdminBasicNoCodeUpdate() {
        try {
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $params = [
                'name' => 'Sample Album',
                'date' => '2020-01-01',
                'code' => '123'
            ];
            $album = Album::withId(899);
            $album->update($params);
            $albumInfo = $album->getDataArray();
            $this->assertEquals(9, sizeOf($albumInfo));
            $this->assertEquals(899, $albumInfo['id']);
            $this->assertEquals('Sample Album', $albumInfo['name']);
            $this->assertEquals('', $albumInfo['description']);
            $this->assertEquals('2020-01-01 00:00:00', $albumInfo['date']);
            $this->assertNull($albumInfo['lastAccessed']);
            $this->assertEquals('sample', $albumInfo['location']);
            $this->assertEquals('123', $albumInfo['code']);
            $this->assertEquals('4', $albumInfo['owner']);
            $this->assertEquals(0, $albumInfo['images']);
        } finally {
            unset($_SESSION['hash']);
        }
    }

    public function testUpdateAdminFull() {
        try {
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $params = [
                'name' => 'Sample Album',
                'date' => '2020-01-01',
                'description' => 'some description'
            ];
            $album = Album::withId(898);
            $album->update($params);
            $albumInfo = $album->getDataArray();
            $this->assertEquals(9, sizeOf($albumInfo));
            $this->assertEquals(898, $albumInfo['id']);
            $this->assertEquals('Sample Album', $albumInfo['name']);
            $this->assertEquals('some description', $albumInfo['description']);
            $this->assertEquals('2020-01-01 00:00:00', $albumInfo['date']);
            $this->assertNull($albumInfo['lastAccessed']);
            $this->assertEquals('', $albumInfo['location']);
            $this->assertEquals('', $albumInfo['code']);
            $this->assertEquals('5', $albumInfo['owner']);
            $this->assertEquals(0, $albumInfo['images']);
        } finally {
            unset($_SESSION['hash']);
        }
    }
}