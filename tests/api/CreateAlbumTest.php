<?php

namespace api;

use CustomAsserts;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class CreateAlbumTest extends TestCase {
    /**
     * @var Client
     */
    private $http;
    /**
     * @var Sql
     */
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/create-album.php');
        } catch (GuzzleException | ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        try {
            $this->http->request('POST', 'api/create-album.php', [
                'cookies' => $cookieJar
            ]);
        } catch (GuzzleException | ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testNoAlbumName() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-album.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album name is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankAlbumName() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-album.php', [
            'form_params' => [
                'name' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album name can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBadDate() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
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

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function testJustAlbumName() {   // done as an admin
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/create-album.php', [
                'form_params' => array(
                    'name' => 'Sample Album'
                ),
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $albumId = (string)$response->getBody();
            $this->assertEquals(1, preg_match("/^[\d]+$/", $albumId));
            $album = $this->sql->getRow("SELECT * FROM `albums` WHERE `albums`.`id` = $albumId;");
            $albumLocation = $album['location'];
            $this->assertEquals($albumId, $album['id']);
            $this->assertEquals('Sample Album', $album['name']);
            $this->assertEquals('', $album['description']);
            CustomAsserts::timeWithin(2, $album['date']);
            $this->assertNull($album['lastAccessed']);
            $this->assertStringStartsWith('SampleAlbum_', $album['location']);
            CustomAsserts::timestampWithin(2, explode('_', $album['location'])[1]);
            $this->assertNull($album['code']);
            $this->assertEquals(1, $album['owner']);
            $this->assertEquals(0, $album['images']);
            $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "content/albums/$albumLocation"));
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * `albums_for_users` WHERE `albums_for_users`.`album` = $albumId;"));
        } finally {
            rmdir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "content/albums/$albumLocation");
            $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = $albumId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        }
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function testAllDetails() {      // done as an uploader
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
            ], getenv('DB_HOST'));
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
            $albumLocation = $album['location'];
            $this->assertStringStartsWith('SampleAlbum_', $album['location']);
            CustomAsserts::timestampWithin(2, explode('_', $album['location'])[1]);
            $this->assertNull($album['code']);
            $this->assertEquals(4, $album['owner']);
            $this->assertEquals(0, $album['images']);
            $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "content/albums/$albumLocation"));
            $albumsForUsers = $this->sql->getRows("SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = $albumId;");
            $this->assertEquals(1, sizeOf($albumsForUsers));
            $this->assertEquals($albumId, $albumsForUsers[0]['album']);
            $this->assertEquals(4, $albumsForUsers[0]['user']);
            $userLogs = $this->sql->getRows("SELECT * FROM `user_logs` WHERE `user_logs`.`album` = $albumId ORDER BY time DESC;");
            $this->assertTrue(1 <= sizeOf($userLogs));
            $this->assertEquals(4, $userLogs[0]['user']);
            CustomAsserts::timeWithin(2, $userLogs[0]['time']);
            $this->assertEquals('Created Album', $userLogs[0]['action']);
            $this->assertNull($userLogs[0]['what']);
            $this->assertEquals($albumId, $userLogs[0]['album']);
        } finally {
            rmdir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "content/albums/$albumLocation");
            $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = $albumId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
            $this->sql->executeStatement("DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = $albumId;");
            $this->sql->executeStatement("DELETE FROM `user_logs` WHERE `user_logs`.`album` = $albumId;");
        }
    }
}