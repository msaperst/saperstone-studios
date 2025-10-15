<?php

namespace coverage\integration;

use Album;
use AlbumException;
use BadAlbumException;
use BadUserException;
use CustomAsserts;
use Exception;
use PHPUnit\Framework\TestCase;
use Sql;
use SqlException;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class AlbumIntegrationTest extends TestCase {

    private Sql $sql;

    private string $hash;

    private int $albumId;

    /**
     * @throws SqlException
     */
    public function setUp(): void {
        if (isset($_SESSION ['hash'])) {
            $this->hash = $_SESSION ['hash'];
        }
        unset($this->albumId);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('898', 'sample-album', 'sample album for testing', '', 5);");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('899', 'sample-album', 'sample album for testing', 'sample', 4, '123');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '898', '', '1', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (NULL, '899', '', '1', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (3, '898');");
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (1, '899');");
        $oldMask = umask(0);
        mkdir(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/albums/sample', 0777, true);
        chmod(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/albums/sample', 0777);
        touch(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/albums/sample/sample.jpg');
        chmod(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/albums/sample/sample.jpg', 0777);
        umask($oldMask);
    }

    /**
     * @throws Exception
     */
    public function tearDown(): void {
        if (isset($this->hash)) {
            $_SESSION ['hash'] = $this->hash;
        } else {
            unset($_SESSION ['hash']);
        }
        if (isset($this->albumId)) {
            $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = $this->albumId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        }
        unset($this->albumId);
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
        system("rm -rf " . escapeshellarg(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/albums'));
    }

    public function testNullAlbumId() {
        $this->expectException(BadAlbumException::class);
        $this->expectExceptionMessage('Album id is required');
        Album::withId(NULL);
    }

    public function testBlankAlbumId() {
        $this->expectException(BadAlbumException::class);
        $this->expectExceptionMessage('Album id can not be blank');
        Album::withId("");
    }

    public function testLetterAlbumId() {
        $this->expectException(BadAlbumException::class);
        $this->expectExceptionMessage('Album id does not match any albums');
        Album::withId("a");
    }

    public function testBadAlbumId() {
        $this->expectException(BadAlbumException::class);
        $this->expectExceptionMessage('Album id does not match any albums');
        Album::withId(8999);
    }

    public function testBadStringAlbumId() {
        $this->expectException(BadAlbumException::class);
        $this->expectExceptionMessage('Album id does not match any albums');
        Album::withId("8999");
    }

    /**
     * @throws Exception
     */
    public function testGetId() {
        $album = Album::withId('899');
        $this->assertEquals(899, $album->getId());
    }

    /**
     * @throws Exception
     */
    public function testGetName() {
        $album = Album::withId('899');
        $this->assertEquals('sample-album', $album->getName());
    }

    /**
     * @throws Exception
     */
    public function testGetDescription() {
        $album = Album::withId('899');
        $this->assertEquals('sample album for testing', $album->getDescription());
    }

    /**
     * @throws Exception
     */
    public function testGetOwner() {
        $album = Album::withId('899');
        $this->assertEquals(4, $album->getOwner());
    }

    /**
     * @throws Exception
     */
    public function testGetLocation() {
        $album = Album::withId('899');
        $this->assertEquals('sample', $album->getLocation());
    }

    /**
     * @throws Exception
     */
    public function testHasCodeTrue() {
        $album = Album::withId('899');
        $this->assertTrue($album->hasCode());
    }

    /**
     * @throws Exception
     */
    public function testHasCodeFalse() {
        $album = Album::withId('898');
        $this->assertFalse($album->hasCode());
    }

    /**
     * @throws Exception
     */
    public function testGetCodeTrue() {
        $album = Album::withId('899');
        $this->assertEquals('123', $album->getCode());
    }

    /**
     * @throws Exception
     */
    public function testGetCodeFalse() {
        $album = Album::withId('898');
        $this->assertNull($album->getCode());
    }

    /**
     * @throws Exception
     */
    public function testBasicDataLoaded() {
        date_default_timezone_set("America/New_York");
        $album = Album::withId(899);
        $albumInfo = $album->getDataBasic();
        $this->assertEquals(4, sizeOf($albumInfo));
        $this->assertEquals('sample-album', $albumInfo['name']);
        $this->assertEquals('sample album for testing', $albumInfo['description']);
        CustomAsserts::timeWithin(2, $albumInfo['date']);
        $this->assertEquals('123', $albumInfo['code']);
    }

    /**
     * @throws Exception
     */
    public function testAllDataLoaded() {
        date_default_timezone_set("America/New_York");
        $album = Album::withId(899);
        $albumInfo = $album->getDataArray();
        $this->assertEquals(9, sizeOf($albumInfo));
        $this->assertEquals(899, $albumInfo['id']);
        $this->assertEquals('sample-album', $albumInfo['name']);
        $this->assertEquals('sample album for testing', $albumInfo['description']);
        CustomAsserts::timeWithin(2, $albumInfo['date']);
        $this->assertNull($albumInfo['lastAccessed']);
        $this->assertEquals('sample', $albumInfo['location']);
        $this->assertEquals('123', $albumInfo['code']);
        $this->assertEquals(4, $albumInfo['owner']);
        $this->assertEquals(0, $albumInfo['images']);
    }

    /**
     * @throws Exception
     */
    public function testCanUserGetDataNobody() {
        $album = Album::withId(899);
        $this->assertFalse($album->canUserGetData());
    }

    /**
     * @throws Exception
     */
    public function testCanUserGetDataAdmin() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = Album::withId(899);
        $this->assertTrue($album->canUserGetData());
        unset($_SESSION['hash']);
    }

    /**
     * @throws Exception
     */
    public function testCanUserGetDataOwner() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $album = Album::withId(899);
        $this->assertTrue($album->canUserGetData());
        unset($_SESSION['hash']);
    }

    /**
     * @throws Exception
     */
    public function testCanUserGetDataOtherUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $album = Album::withId(899);
        $this->assertFalse($album->canUserGetData());
        unset($_SESSION['hash']);
    }

    /**
     * @throws Exception
     */
    public function testIsSearchedForNoCode() {
        $album = Album::withId(898);
        $this->assertFalse($album->isSearchedFor());
    }

    /**
     * @throws Exception
     */
    public function testIsSearchedForNoSession() {
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
    }

    /**
     * @throws Exception
     */
    public function testIsSearchedForNoSessionSearch() {
        $_SESSION['search'] = array();
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_SESSION['search']);
    }

    /**
     * @throws Exception
     */
    public function testIsSearchedForEmptySessionSearch() {
        $_SESSION['searched'] = 'a';
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_SESSION['searched']);
    }

    /**
     * @throws Exception
     */
    public function testIsSearchedForEmptySessionSearchArray() {
        $_SESSION['searched'] = array();
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_SESSION['searched']);
    }

    /**
     * @throws Exception
     */
    public function testIsSearchedForNoSessionMatch() {
        $_SESSION['searched']['899'] = '5';
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_SESSION['searched']);
    }

    /**
     * @throws Exception
     */
    public function testIsSearchedForSessionMatch() {
        $_SESSION['searched']['899'] = md5("album123");
        $album = Album::withId(899);
        $this->assertTrue($album->isSearchedFor());
        unset($_SESSION['searched']);
    }


    /**
     * @throws Exception
     */
    public function testIsSearchedForNoCookie() {
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
    }

    /**
     * @throws Exception
     */
    public function testIsSearchedForNoCookieSearch() {
        $_COOKIE['search'] = array();
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_COOKIE['search']);
    }

    /**
     * @throws Exception
     */
    public function testIsSearchedForEmptyCookieSearch() {
        $_COOKIE['searched'] = 'a';
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_COOKIE['searched']);
    }

    /**
     * @throws Exception
     */
    public function testIsSearchedForEmptyCookieSearchArray() {
        $_COOKIE['searched'] = json_encode(array());
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_COOKIE['searched']);
    }

    /**
     * @throws Exception
     */
    public function testIsSearchedForNoCookieMatch() {
        $searched = array();
        $searched[899] = '5';
        $_COOKIE['searched'] = json_encode($searched);
        $album = Album::withId(899);
        $this->assertFalse($album->isSearchedFor());
        unset($_COOKIE['searched']);
    }

    /**
     * @throws Exception
     */
    public function testIsSearchedForCookieMatch() {
        $searched = array();
        $searched[899] = md5("album123");
        $_COOKIE['searched'] = json_encode($searched);
        $album = Album::withId(899);
        $this->assertTrue($album->isSearchedFor());
        unset($_COOKIE['searched']);
    }

    /**
     * @throws Exception
     */
    public function testCanUserAccessNobody() {
        $album = Album::withId(899);
        $this->assertFalse($album->canUserAccess());
    }

    /**
     * @throws Exception
     */
    public function testCanUserAccessAdmin() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = Album::withId(899);
        $this->assertTrue($album->canUserAccess());
        unset($_SESSION['hash']);
    }

    /**
     * @throws Exception
     */
    public function testCanUserAccessOwner() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $album = Album::withId(899);
        $this->assertTrue($album->canUserAccess());
        unset($_SESSION['hash']);
    }

    /**
     * @throws Exception
     */
    public function testCanUserAccessOtherUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $album = Album::withId(899);
        $this->assertFalse($album->canUserAccess());
        unset($_SESSION['hash']);
    }

    /**
     * @throws Exception
     */
    public function testCanUserAccessAddedUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $album = Album::withId(898);
        $this->assertTrue($album->canUserAccess());
        unset($_SESSION['hash']);
    }

    /**
     * @throws Exception
     */
    public function testCanUserAccessSearched() {
        $_SESSION['searched']['899'] = md5("album123");
        $album = Album::withId(899);
        $this->assertTrue($album->canUserAccess());
        unset($_SESSION['searched']);
    }

    /**
     * @throws SqlException
     * @throws BadAlbumException
     * @throws BadUserException
     */
    public function testDeleteNoAccess() {
        $album = Album::withId(899);
        $this->expectException(AlbumException::class);
        $this->expectExceptionMessage('User not authorized to delete album');
        $album->delete();
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `albums` WHERE `albums`.`id` = 899;"));
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `album_images` WHERE `album_images`.`album` = 899;"));
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = 899;"));
        $this->assertTrue(file_exists(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/albums/sample/sample.jpg'));
    }

    /**
     * @throws Exception
     */
    public function testDeleteNoLocation() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = Album::withId(898);
        $album->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `albums` WHERE `albums`.`id` = 898;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `album_images` WHERE `album_images`.`album` = 898;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = 898;"));
    }

    /**
     * @throws Exception
     */
    public function testDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = Album::withId(899);
        $album->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `albums` WHERE `albums`.`id` = 899;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `album_images` WHERE `album_images`.`album` = 899;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = 899;"));
        $this->assertFalse(file_exists(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'content/albums/sample/sample.jpg'));
    }

    public function testWithParamsNullParams() {
        $this->expectException(BadAlbumException::class);
        $this->expectExceptionMessage('Album name is required');
        Album::withParams(NULL);
    }

    public function testWithParamsNoName() {
        $this->expectException(BadAlbumException::class);
        $this->expectExceptionMessage('Album name is required');
        Album::withParams(array());
    }

    public function testWithParamsBlankName() {
        $params = [
            'name' => ''
        ];
        $this->expectException(BadAlbumException::class);
        $this->expectExceptionMessage('Album name can not be blank');
        Album::withParams($params);
    }

    public function testWithParamsBadDate() {
        $params = [
            'name' => 'Sample Album',
            'date' => 'some date'
        ];
        $this->expectException(BadAlbumException::class);
        $this->expectExceptionMessage('Album date is not the correct format');
        Album::withParams($params);
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     * @throws BadAlbumException
     */
    public function testWithParamsRegularUser() {
        $params = [
            'name' => 'Sample Album',
            'date' => '2020-01-01'
        ];
        $this->expectException(AlbumException::class);
        $this->expectExceptionMessage('User not authorized to create album');
        $album = Album::withParams($params);
        $album->create();
    }

    /**
     * @throws BadUserException
     * @throws BadAlbumException
     * @throws SqlException
     */
    public function testWithParamsBadFolder() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        rename(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/albums', dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/tmp_albums');
        try {
            $params = [
                'name' => 'Sample Album',
                'date' => '2020-01-01'
            ];
            $album = Album::withParams($params);
            $this->expectException(AlbumException::class);
            $this->expectExceptionMessage('mkdir(): No such file or directory<br/>Unable to create album');
            $album->create();
        } finally {
            rename(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/tmp_albums', dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/albums');
        }
    }

    /**
     * @throws Exception
     */
    public function testWithParamsBasic() {
        sleep(1);   // putting in a sleep to avoid a duplicate key problem for logging
        date_default_timezone_set("America/New_York");
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $params = [
            'name' => 'Album Name'
        ];
        $album = Album::withParams($params);
        $this->assertEquals('', $album->getId());
        $this->assertEquals('Album Name', $album->getName());
        $this->assertEquals('', $album->getOwner());
        $this->assertEquals('', $album->getLocation());
        $this->albumId = $album->create();
        $albumInfo = $album->getDataArray();
        $this->assertEquals(9, sizeOf($albumInfo));
        $this->assertEquals($this->albumId, $albumInfo['id']);
        $this->assertEquals('Album Name', $albumInfo['name']);
        $this->assertEquals('', $albumInfo['description']);
        $this->assertNull($albumInfo['date']);
        $this->assertNull($albumInfo['lastAccessed']);
        $this->assertStringStartsWith('AlbumName_', $albumInfo['location']);
        CustomAsserts::timestampWithin(2, explode('_', $albumInfo['location'])[1]);
        $this->assertEquals('', $albumInfo['code']);
        $this->assertEquals('4', $albumInfo['owner']);
        $this->assertEquals(0, $albumInfo['images']);
        $albums = $this->sql->getRows("SELECT * FROM `albums_for_users` WHERE album = $this->albumId");
        $this->assertEquals(1, sizeof($albums));
        $this->assertEquals(4, $albums[0]['user']);
        $logs = $this->sql->getRow("SELECT * FROM `user_logs` WHERE album = $this->albumId ORDER BY time DESC LIMIT 1;");
        $this->assertEquals(4, $logs['user']);
        $this->assertEquals('Created Album', $logs['action']);
        $this->assertNull($logs['what']);
    }

    /**
     * @throws Exception
     */
    public function testWithParamsAll() {
        date_default_timezone_set("America/New_York");
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
        $this->albumId = $album->create();
        $albumInfo = $album->getDataArray();
        $this->assertEquals(9, sizeOf($albumInfo));
        $this->assertEquals($this->albumId, $albumInfo['id']);
        $this->assertEquals('Album Name', $albumInfo['name']);
        $this->assertEquals('some description', $albumInfo['description']);
        $this->assertEquals('2020-01-01 00:00:00', $albumInfo['date']);
        $this->assertNull($albumInfo['lastAccessed']);
        $this->assertStringStartsWith('AlbumName_', $albumInfo['location']);
        CustomAsserts::timestampWithin(2, explode('_', $albumInfo['location'])[1]);
        $this->assertEquals('', $albumInfo['code']);
        $this->assertEquals('1', $albumInfo['owner']);
        $this->assertEquals(0, $albumInfo['images']);
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     * @throws BadAlbumException
     */
    public function testUpdateNullParams() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = Album::withId(899);
        $this->expectException(AlbumException::class);
        $this->expectExceptionMessage('Album name is required');
        $album->update(NULL);

    }

    /**
     * @throws SqlException
     * @throws BadUserException
     * @throws BadAlbumException
     */
    public function testUpdateNoName() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = Album::withId(899);
        $this->expectException(AlbumException::class);
        $this->expectExceptionMessage('Album name is required');
        $album->update(array());
    }

    /**
     * @throws SqlException
     * @throws BadUserException
     * @throws BadAlbumException
     */
    public function testUpdateBlankName() {
        $params = [
            'name' => ''
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = Album::withId(899);
        $this->expectException(AlbumException::class);
        $this->expectExceptionMessage('Album name can not be blank');
        $album->update($params);

    }

    /**
     * @throws SqlException
     * @throws BadUserException
     * @throws BadAlbumException
     */
    public function testUpdateBadDate() {
        $params = [
            'name' => 'Sample Album',
            'date' => 'some date'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $album = Album::withId(899);
        $this->expectException(AlbumException::class);
        $this->expectExceptionMessage('Album date is not the correct format');
        $album->update($params);

    }

    /**
     * @throws SqlException
     * @throws BadUserException
     * @throws BadAlbumException
     */
    public function testUpdateUnAuthUser() {
        $params = [
            'name' => 'Sample Album',
            'date' => '2020-01-01'
        ];
        $album = Album::withId(899);
        $this->expectException(AlbumException::class);
        $this->expectExceptionMessage('User not authorized to update album');
        $album->update($params);

    }

    /**
     * @throws SqlException
     * @throws BadUserException
     * @throws BadAlbumException
     */
    public function testUpdateWrongUser() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $params = [
            'name' => 'Sample Album',
            'date' => '2020-01-01'
        ];
        $album = Album::withId(898);
        $this->expectException(AlbumException::class);
        $this->expectExceptionMessage('User not authorized to update album');
        $album->update($params);
    }

    /**
     * @throws Exception
     */
    public function testUpdateAdminBasic() {
        date_default_timezone_set("America/New_York");
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
        $this->assertNull($albumInfo['date']);
        $this->assertNull($albumInfo['lastAccessed']);
        $this->assertEquals('', $albumInfo['location']);
        $this->assertEquals('', $albumInfo['code']);
        $this->assertEquals('5', $albumInfo['owner']);
        $this->assertEquals(0, $albumInfo['images']);
    }

    /**
     * @throws Exception
     */
    public function testUpdateAdminBasicCode() {
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
    }

    /**
     * @throws Exception
     */
    public function testUpdateAdminBasicEmptyCode() {
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
    }

    /**
     * @throws Exception
     */
    public function testUpdateAdminBasicNonAdminCode() {
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
    }

    /**
     * @throws BadUserException
     * @throws BadAlbumException
     * @throws SqlException
     * @throws AlbumException
     */
    public function testUpdateAdminBasicDuplicateCode() {
        $album = new Album();
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $params = [
            'name' => 'Sample Album',
            'date' => '2020-01-01',
            'code' => '123'
        ];
        $album = Album::withId(898);
        $this->expectException(BadAlbumException::class);
        $this->expectExceptionMessage('Album code already exists');
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
    }

    /**
     * @throws Exception
     */
    public function testUpdateAdminBasicNoCodeUpdate() {
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
    }

    /**
     * @throws Exception
     */
    public function testUpdateAdminFull() {
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
    }
}