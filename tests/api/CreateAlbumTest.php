<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';

class CreateAlbumTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/create-album.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], 'localhost');
        try {
            $this->http->request('POST', 'api/create-album.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoAlbumName() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-album.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album name is required", (string)$response->getBody());
    }

    public function testBlankAlbumName() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-album.php', [
            'form_params' => [
                'name' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album name can not be blank", (string)$response->getBody());
    }

    public function testBadDate() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/create-album.php', [
            'form_params' => array(
                'name' => 'Sample Album',
                'date' => '1234'
            ),
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album date is not the correct format", (string)$response->getBody());
    }

    public function testJustAlbumName() {   // done as an admin
        try {
            date_default_timezone_set("America/New_York");
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/create-album.php', [
                'form_params' => array(
                    'name' => 'Sample Album'
                ),
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $albumId = $response->getBody();
            $this->assertEquals(1, preg_match("/^[\d]+$/", $albumId));
            $album = $this->sql->getRow("SELECT * FROM `albums` WHERE `albums`.`id` = $albumId;");
            $this->assertEquals($albumId, $album['id']);
            $this->assertEquals('Sample Album', $album['name']);
            $this->assertEquals('', $album['description']);
            $this->assertEquals(date("Y-m-d H:i:s"), $album['date']);
            $this->assertNull($album['lastAccessed']);
            $this->assertEquals('SampleAlbum_' . time(), $album['location']);
            $this->assertNull($album['code']);
            $this->assertEquals(1, $album['owner']);
            $this->assertEquals(0, $album['images']);
            $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/SampleAlbum_' . time()));
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * `albums_for_users` WHERE `albums_for_users`.`album` = $albumId;"));
        } finally {
            rmdir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/SampleAlbum_' . time());
            $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = $albumId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        }
    }

    public function testAllDetails() {      // done as an uploader
        try {
            date_default_timezone_set("America/New_York");
            $cookieJar = CookieJar::fromArray([
                'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/create-album.php', [
                'form_params' => array(
                    'name' => 'Sample Album',
                    'description' => 'Sample Album Description',
                    'date' => '2020-07-28'
                ),
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $albumId = $response->getBody();
            $this->assertEquals(1, preg_match("/^[\d]+$/", $albumId));
            $album = $this->sql->getRow("SELECT * FROM `albums` WHERE `albums`.`id` = $albumId;");
            $this->assertEquals($albumId, $album['id']);
            $this->assertEquals('Sample Album', $album['name']);
            $this->assertEquals('Sample Album Description', $album['description']);
            $this->assertEquals('2020-07-28 00:00:00', $album['date']);
            $this->assertNull($album['lastAccessed']);
            $this->assertEquals('SampleAlbum_' . time(), $album['location']);
            $this->assertNull($album['code']);
            $this->assertEquals(4, $album['owner']);
            $this->assertEquals(0, $album['images']);
            $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/SampleAlbum_' . time()));
            $albumsForUsers = $this->sql->getRows("SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = $albumId;");
            $this->assertEquals(1, sizeOf($albumsForUsers));
            $this->assertEquals($albumId, $albumsForUsers[0]['album']);
            $this->assertEquals(4, $albumsForUsers[0]['user']);
            $userLogs = $this->sql->getRows("SELECT * FROM `user_logs` WHERE `user_logs`.`album` = $albumId;");
            $this->assertEquals(1, sizeOf($userLogs));
            $this->assertEquals(4, $userLogs[0]['user']);
            $this->assertEquals(date("Y-m-d H:i:s"), $userLogs[0]['time']);
            $this->assertEquals('Created Album', $userLogs[0]['action']);
            $this->assertNull($userLogs[0]['what']);
            $this->assertEquals($albumId, $userLogs[0]['album']);
        } finally {
            rmdir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/SampleAlbum_' . time());
            $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = $albumId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
            $this->sql->executeStatement("DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = $albumId;");
            $this->sql->executeStatement("DELETE FROM `user_logs` WHERE `user_logs`.`album` = $albumId;");
        }
    }

    public function testCantCreateFolder() {
        rename(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/tmp_albums');
        try {
            date_default_timezone_set("America/New_York");
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], 'localhost');
            $response = $this->http->request('POST', 'api/create-album.php', [
                'form_params' => array(
                    'name' => 'Sample Album'
                ),
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals("<br />\n<b>Warning</b>:  mkdir(): File exists in <b>/var/www/public/api/create-album.php</b> on line <b>49</b><br />\nmkdir(): File exists<br/>Unable to create album", (string)$response->getBody());
        } finally {
            rename(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/tmp_albums', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums');
        }
    }
}

?>