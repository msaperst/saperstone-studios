<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UpdateAlbumTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('998', 'sample-album', 'sample album for testing', 'sample', 5);");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4, 123);");
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 998;");
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testNoAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-album.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", (string)$response->getBody());
    }

    public function testBlankAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-album.php', [
            'form_params' => [
                'id' => ''
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
        $response = $this->http->request('POST', 'api/update-album.php', [
            'form_params' => [
                'id' => 'a'
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
        $response = $this->http->request('POST', 'api/update-album.php', [
            'form_params' => [
                'id' => 9999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    public function testUploaderCantUpdateOtherAlbum() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
            ], getenv('DB_HOST'));
            $this->http->request('POST', 'api/update-album.php', [
                'form_params' => [
                    'id' => 998
                ],
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testNoAlbumName() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-album.php', [
            'form_params' => [
                'id' => 998
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album name is required", (string)$response->getBody());
    }

    public function testBlankAlbumName() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-album.php', [
            'form_params' => [
                'id' => 998,
                'name' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album name can not be blank", (string)$response->getBody());
    }

    public function testAdminCanUpdateAnyAlbum() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-album.php', [
            'form_params' => [
                'id' => 998,
                'name' => 'New Album'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $albumInfo = $this->sql->getRow('SELECT * FROM albums WHERE id = 998');
        $this->assertEquals(998, $albumInfo['id']);
        $this->assertEquals('New Album', $albumInfo['name']);
        $this->assertEquals('', $albumInfo['description']);
        $this->assertStringStartsWith(date('Y-m-d H:i:'), $albumInfo['date']);
        $this->assertNull($albumInfo['lastAccessed']);
        $this->assertEquals('sample', $albumInfo['location']);
        $this->assertEquals('', $albumInfo['code']);
        $this->assertEquals('5', $albumInfo['owner']);
        $this->assertEquals(0, $albumInfo['images']);
    }

    public function testUploaderCanUpdateOwnAlbum() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-album.php', [
            'form_params' => [
                'id' => 999,
                'name' => 'Updated Album'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $albumInfo = $this->sql->getRow('SELECT * FROM albums WHERE id = 999');
        $this->assertEquals(999, $albumInfo['id']);
        $this->assertEquals('Updated Album', $albumInfo['name']);
        $this->assertEquals('', $albumInfo['description']);
        $this->assertStringStartsWith(date('Y-m-d H:i:'), $albumInfo['date']);
        $this->assertNull($albumInfo['lastAccessed']);
        $this->assertEquals('sample', $albumInfo['location']);
        $this->assertNull($albumInfo['code']);
        $this->assertEquals('4', $albumInfo['owner']);
        $this->assertEquals(0, $albumInfo['images']);
    }

    public function testUpdateFullUploaderCantSetCode() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-album.php', [
            'form_params' => [
                'id' => 999,
                'name' => 'Updated Album',
                'date' => '2020-01-01',
                'description' => 'some album information',
                'code' => 456
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $albumInfo = $this->sql->getRow('SELECT * FROM albums WHERE id = 999');
        $this->assertEquals(999, $albumInfo['id']);
        $this->assertEquals('Updated Album', $albumInfo['name']);
        $this->assertEquals('some album information', $albumInfo['description']);
        $this->assertStringStartsWith('2020-01-01 00:00:00', $albumInfo['date']);
        $this->assertNull($albumInfo['lastAccessed']);
        $this->assertEquals('sample', $albumInfo['location']);
        $this->assertNull($albumInfo['code']);
        $this->assertEquals('4', $albumInfo['owner']);
        $this->assertEquals(0, $albumInfo['images']);
    }

    public function testUpdateAdminSetCode() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-album.php', [
            'form_params' => [
                'id' => 999,
                'name' => 'Updated Album',
                'date' => '2020-01-01',
                'description' => 'some album information',
                'code' => 456
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $albumInfo = $this->sql->getRow('SELECT * FROM albums WHERE id = 999');
        $this->assertEquals(999, $albumInfo['id']);
        $this->assertEquals('Updated Album', $albumInfo['name']);
        $this->assertEquals('some album information', $albumInfo['description']);
        $this->assertStringStartsWith('2020-01-01 00:00:00', $albumInfo['date']);
        $this->assertNull($albumInfo['lastAccessed']);
        $this->assertEquals('sample', $albumInfo['location']);
        $this->assertEquals('456', $albumInfo['code']);
        $this->assertEquals('4', $albumInfo['owner']);
        $this->assertEquals(0, $albumInfo['images']);
    }

    public function testUpdateAdminSetDuplicateCode() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-album.php', [
            'form_params' => [
                'id' => 998,
                'name' => 'Updated Album',
                'date' => '2020-01-01',
                'description' => 'some album information',
                'code' => 123
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album code already exists", (string)$response->getBody());
        $albumInfo = $this->sql->getRow('SELECT * FROM albums WHERE id = 998');
        $this->assertEquals(998, $albumInfo['id']);
        $this->assertEquals('Updated Album', $albumInfo['name']);
        $this->assertEquals('some album information', $albumInfo['description']);
        $this->assertStringStartsWith('2020-01-01 00:00:00', $albumInfo['date']);
        $this->assertNull($albumInfo['lastAccessed']);
        $this->assertEquals('sample', $albumInfo['location']);
        $this->assertNull($albumInfo['code']);
        $this->assertEquals('5', $albumInfo['owner']);
        $this->assertEquals(0, $albumInfo['images']);
    }
}