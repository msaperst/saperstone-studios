<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';

class FindAlbumTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `code`) VALUES ('999', 'sample-album', 'sample album for testing', '', 'search-for-me');");
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testNoAlbumCode() {
        $response = $this->http->request('GET', 'api/find-album.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album code is required", (string)$response->getBody());
    }

    public function testBlankAlbumCode() {
        $response = $this->http->request('GET', 'api/find-album.php', [
            'query' => [
                'code' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album code can not be blank", (string)$response->getBody());
    }

    public function testBadAlbumCode() {
        $response = $this->http->request('GET', 'api/find-album.php', [
            'query' => [
                'code' => 'some crazy code'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("That code does not match any albums", (string)$response->getBody());
    }

    public function testAlbumCode() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('GET', 'api/find-album.php', [
            'query' => [
                'code' => 'search-for-me',
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(999, (string)$response->getBody());
        //UNABLE TO CHECK COOKIE
    }

    public function testAlbumCodeAgain() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191',
            'searched' => json_encode([
                999 => md5('albumsearch-for-me')
            ])
        ], 'localhost');
        $response = $this->http->request('GET', 'api/find-album.php', [
            'query' => [
                'code' => 'search-for-me',
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(999, (string)$response->getBody());
        //UNABLE TO CHECK COOKIE
    }

    public function testAlbumCodeCantAdd() {
        $response = $this->http->request('GET', 'api/find-album.php', [
            'query' => [
                'code' => 'search-for-me',
                'albumAdd' => 1
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(999, (string)$response->getBody());
        //UNABLE TO CHECK COOKIE
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = 999;"));
    }

    public function testAlbumCodeAdded() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('GET', 'api/find-album.php', [
            'query' => [
                'code' => 'search-for-me',
                'albumAdd' => 1
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(999, (string)$response->getBody());
        //UNABLE TO CHECK COOKIE
        $albums = $this->sql->getRows("SELECT * FROM `albums_for_users` WHERE `albums_for_users`.`album` = 999;");
        $this->assertEquals(1, sizeOf($albums));
        $this->assertEquals('1', $albums[0]['user']);
        $this->assertEquals(999, $albums[0]['album']);
    }
}

?>