<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";

class GetAlbumTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement( "INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('998', 'sample-album', 'sample album for testing', 'sample', 5);" );
        $this->sql->executeStatement( "INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4, '123');" );
        $this->sql->executeStatement( "INSERT INTO `albums_for_users` (`user`, `album`) VALUES (1, '998');" );
        $this->sql->executeStatement( "INSERT INTO `albums_for_users` (`user`, `album`) VALUES (1, '999');" );
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement( "DELETE FROM `albums` WHERE `albums`.`id` = 998;" );
        $this->sql->executeStatement( "DELETE FROM `albums` WHERE `albums`.`id` = 999;" );
        $this->sql->executeStatement( "DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = 998;" );
        $this->sql->executeStatement( "DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = 999;" );
        $count = $this->sql->getRow( "SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement( "ALTER TABLE `albums` AUTO_INCREMENT = $count;" );
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        $response;
        try {
            $response = $this->http->request('GET', 'api/get-album.php');
        } catch ( GuzzleHttp\Exception\ClientException $e ) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals('You must be logged in to perform this action', $e->getResponse()->getBody() );
        }
    }
    
    public function testNoAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album.php', [
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", (string) $response->getBody());
    }

    public function testBlankAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album.php', [
                'query' => [
                    'id' => ''
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", (string) $response->getBody());
    }

    public function testLetterAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album.php', [
                'query' => [
                    'id' => 'a'
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string) $response->getBody());
    }

    public function testBadAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album.php', [
                'query' => [
                    'id' => 9999
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string) $response->getBody());
    }

    public function testUploaderCantGetOtherAlbum() {
        $response;
        try {
            $cookieJar = CookieJar::fromArray([
                        'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                    ], 'localhost');
            $response = $this->http->request('GET', 'api/get-album.php', [
                    'query' => [
                        'id' => 998
                    ],
                    'cookies' => $cookieJar
            ]);
        } catch ( GuzzleHttp\Exception\ClientException $e ) {
            $this->assertEquals(403, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody() );
        }
    }

    public function testAdminCanGetAnyAlbum() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album.php', [
                'query' => [
                    'id' => 998
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $albumInfo = json_decode($response->getBody(), true);
        $this->assertEquals(998, $albumInfo['id'] );
        $this->assertEquals('sample-album', $albumInfo['name'] );
        $this->assertEquals('sample album for testing', $albumInfo['description'] );
        $this->assertEquals(date ( "Y-m-d" ), $albumInfo['date'] );
        $this->assertNull($albumInfo['lastAccessed'] );
        $this->assertEquals('sample', $albumInfo['location'] );
        $this->assertEquals('', $albumInfo['code'] );
        $this->assertEquals(5, $albumInfo['owner'] );
        $this->assertEquals(0, $albumInfo['images'] );
    }

    public function testUploaderCanGetOwnAlbum() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album.php', [
                'query' => [
                    'id' => 999
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $albumInfo = json_decode($response->getBody(), true);
        $this->assertEquals(999, $albumInfo['id'] );
        $this->assertEquals('sample-album', $albumInfo['name'] );
        $this->assertEquals('sample album for testing', $albumInfo['description'] );
        $this->assertEquals(date ( "Y-m-d" ), $albumInfo['date'] );
        $this->assertNull($albumInfo['lastAccessed'] );
        $this->assertEquals('sample', $albumInfo['location'] );
        $this->assertEquals('123', $albumInfo['code'] );
        $this->assertEquals(4, $albumInfo['owner'] );
        $this->assertEquals(0, $albumInfo['images'] );
    }
}
?>