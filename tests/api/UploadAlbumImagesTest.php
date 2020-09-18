<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UploadAlbumImagesTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('998', 'sample-album', 'sample album for testing', 'sample', 5);");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4, '123');");
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (1, '998');");
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (1, '999');");
        $oldmask = umask(0);
        mkdir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample');
        chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample', 0777);
        umask($oldmask);
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 998;");
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = 998;");
        $this->sql->executeStatement("DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = 999;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 998;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 999;");
        $this->sql->executeStatement("DELETE FROM `user_logs` WHERE `user_logs`.`album` = 998;");
        $this->sql->executeStatement("DELETE FROM `user_logs` WHERE `user_logs`.`album` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
        system("rm -rf " . escapeshellarg(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample'));
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/upload-album-images.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals('You must be logged in to perform this action', $e->getResponse()->getBody());
        }
    }

    public function testNoAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-album-images.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", (string)$response->getBody());
    }

    public function testBlankAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-album-images.php', [
            'form_params' => [
                'album' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", (string)$response->getBody());
    }

    public function testLetterAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-album-images.php', [
            'form_params' => [
                'album' => 'a'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    public function testBadAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-album-images.php', [
            'form_params' => [
                'album' => 9999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    public function testUploaderCantGetOtherAlbum() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
            ], getenv('DB_HOST'));
            $this->http->request('POST', 'api/upload-album-images.php', [
                'form_params' => [
                    'album' => 998
                ],
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(403, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testUploadNoImages() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-album-images.php', [
            'form_params' => [
                'album' => 998
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("File(s) are required", (string)$response->getBody());
    }

    public function testSingleFile() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-album-images.php', [
            'multipart' => [
                [
                    'name' => 'album',
                    'contents' => 998,
                ],
                [
                    'name' => 'myfile',
                    'contents' => fopen(dirname(__DIR__) . '/resources/flower.jpeg', 'r'),
                    'filename' => 'flower.jpeg',
                    'headers' => ['Content-Type:' => 'image/png']
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['flower.jpeg'], json_decode($response->getBody(), true));
        $images = $this->sql->getRows("SELECT * FROM album_images WHERE album = 998");
        $this->assertEquals(1, sizeof($images));
        $this->assertEquals(998, $images[0]['album']);
        $this->assertEquals('flower.jpeg', $images[0]['title']);
        $this->assertEquals(0, $images[0]['sequence']);
        $this->assertEquals('', $images[0]['caption']);
        $this->assertEquals('/albums/sample/flower.jpeg', $images[0]['location']);
        $this->assertEquals(1600, $images[0]['width']);
        $this->assertEquals(1200, $images[0]['height']);
        $this->assertEquals(1, $images[0]['active']);
        $logs = $this->sql->getRows("SELECT * FROM user_logs WHERE album = 998 ORDER BY time DESC");
        $this->assertEquals(0, sizeof($logs));
        $album = $this->sql->getRow("SELECT * FROM albums WHERE id = 998");
        $this->assertEquals(1, $album['images']);
        $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower.jpeg'));
    }

    public function testUploaderFile() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-album-images.php', [
            'multipart' => [
                [
                    'name' => 'album',
                    'contents' => 999,
                ],
                [
                    'name' => 'myfile',
                    'contents' => fopen(dirname(__DIR__) . '/resources/flower.jpeg', 'r'),
                    'filename' => 'flower.jpeg',
                    'headers' => ['Content-Type:' => 'image/png']
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['flower.jpeg'], json_decode($response->getBody(), true));
        $images = $this->sql->getRows("SELECT * FROM album_images WHERE album = 999");
        $this->assertEquals(1, sizeof($images));
        $this->assertEquals(999, $images[0]['album']);
        $this->assertEquals('flower.jpeg', $images[0]['title']);
        $this->assertEquals(0, $images[0]['sequence']);
        $this->assertEquals('', $images[0]['caption']);
        $this->assertEquals('/albums/sample/flower.jpeg', $images[0]['location']);
        $this->assertEquals(1600, $images[0]['width']);
        $this->assertEquals(1200, $images[0]['height']);
        $this->assertEquals(1, $images[0]['active']);
        $logs = $this->sql->getRows("SELECT * FROM user_logs WHERE album = 999 ORDER BY time DESC");
        $this->assertEquals(1, sizeof($logs));
        $this->assertEquals(4, $logs[0]['user']);
        $this->assertEquals('Added Image', $logs[0]['action']);
        $this->assertEquals(0, $logs[0]['what']);
        $this->assertEquals(999, $logs[0]['album']);
        $album = $this->sql->getRow("SELECT * FROM albums WHERE id = 999");
        $this->assertEquals(1, $album['images']);
        $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/albums/sample/flower.jpeg'));
    }
}