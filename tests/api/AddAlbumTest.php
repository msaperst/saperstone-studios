<?php

namespace api;

use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class AddAlbumTest extends TestCase {
    private $http;
    private $sql;

    public function setUp(): void {
        $this->http = new ApiTestClient([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/'
        ]);
        $this->sql = new Sql();
        $this->sql->executeStatement(
            "INSERT INTO `albums` (`id`, `name`, `description`, `location`, `code`) VALUES ('999', 'sample-album', 'sample album for testing', '', 'search-for-me');"
        );
    }

    public function tearDown(): void {
        $this->sql->executeStatement("DELETE FROM `albums_for_users` WHERE `album` = 999;");
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `id` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
        $this->http = null;
    }

    private function loggedInCookies(): CookieJar {
        return CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
    }

    public function testMustBeLoggedIn(): void {
        $response = $this->http->request('POST', 'api/add-album.php', [
            'http_errors' => false,
            'form_params' => [
                'code' => 'search-for-me'
            ]
        ]);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('You must be logged in to perform this action', (string)$response->getBody());
        $this->assertEquals(0, $this->sql->getRowCount(
            "SELECT * FROM `albums_for_users` WHERE `user` = 3 AND `album` = 999;"
        ));
    }

    public function testAlbumCodeRequired(): void {
        $response = $this->http->request('POST', 'api/add-album.php', [
            'http_errors' => false,
            'cookies' => $this->loggedInCookies()
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Album code is required', (string)$response->getBody());
    }

    public function testBadAlbumCode(): void {
        $response = $this->http->request('POST', 'api/add-album.php', [
            'http_errors' => false,
            'form_params' => [
                'code' => 'not-an-album'
            ],
            'cookies' => $this->loggedInCookies()
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('That code does not match any albums', (string)$response->getBody());
        $this->assertEquals(0, $this->sql->getRowCount(
            "SELECT * FROM `albums_for_users` WHERE `user` = 3 AND `album` = 999;"
        ));
    }

    public function testAlbumCodeIsCaseSensitive(): void {
        $response = $this->http->request('POST', 'api/add-album.php', [
            'http_errors' => false,
            'form_params' => [
                'code' => 'SEARCH-FOR-ME'
            ],
            'cookies' => $this->loggedInCookies()
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('That code does not match any albums', (string)$response->getBody());
    }

    public function testAddsAlbumToCurrentUser(): void {
        $response = $this->http->request('POST', 'api/add-album.php', [
            'form_params' => [
                'code' => 'search-for-me'
            ],
            'cookies' => $this->loggedInCookies()
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('999', (string)$response->getBody());

        $albums = $this->sql->getRows(
            "SELECT * FROM `albums_for_users` WHERE `user` = 3 AND `album` = 999;"
        );
        $this->assertCount(1, $albums);
        $this->assertEquals('3', $albums[0]['user']);
        $this->assertEquals(999, $albums[0]['album']);
    }

    public function testAddingAlbumAgainIsIdempotent(): void {
        $this->sql->executeStatement(
            "INSERT INTO `albums_for_users` (`user`, `album`) VALUES (3, 999);"
        );

        $response = $this->http->request('POST', 'api/add-album.php', [
            'form_params' => [
                'code' => 'search-for-me'
            ],
            'cookies' => $this->loggedInCookies()
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('999', (string)$response->getBody());
        $this->assertEquals(1, $this->sql->getRowCount(
            "SELECT * FROM `albums_for_users` WHERE `user` = 3 AND `album` = 999;"
        ));
    }
}
