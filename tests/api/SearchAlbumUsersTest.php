<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SearchAlbumUsersTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('998', 'sample-album', 'sample album for testing', 'sample', 5);");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4, '123');");
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (1, '999');");
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (3, '999');");
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (4, '999');");
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 998;");
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = 998;");
        $this->sql->executeStatement("DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/search-album-users.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        try {
            $this->http->request('POST', 'api/search-album-users.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-album-users.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", (string)$response->getBody());
    }

    public function testBlankAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-album-users.php', [
            'query' => [
                'album' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", (string)$response->getBody());
    }

    public function testLetterAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-album-users.php', [
            'query' => [
                'album' => 'a'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    public function testBadAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-album-users.php', [
            'query' => [
                'album' => 9999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    public function testNoKeywordNoUsers() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-album-users.php', [
            'query' => [
                'album' => 998
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(), json_decode($response->getBody(), true));
    }

    public function testNoKeywordUsers() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-album-users.php', [
            'query' => [
                'album' => 999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $users = json_decode($response->getBody(), true);
        $this->assertEquals(3, sizeOf($users));
        $this->assertEquals(1,$users[0]['id']);
        $this->assertEquals('msaperst',$users[0]['usr']);
        $this->assertEquals('admin',$users[0]['role']);
        $this->assertEquals(3,$users[1]['id']);
        $this->assertEquals('downloader',$users[1]['usr']);
        $this->assertEquals('downloader',$users[1]['role']);
        $this->assertEquals(4,$users[2]['id']);
        $this->assertEquals('uploader',$users[2]['usr']);
        $this->assertEquals('uploader',$users[2]['role']);
    }

    public function testKeywordBlankUsers() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-album-users.php', [
            'query' => [
                'album' => 999,
                'keyword' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $users = json_decode($response->getBody(), true);
        $this->assertEquals(3, sizeOf($users));
        $this->assertEquals(1,$users[0]['id']);
        $this->assertEquals('msaperst',$users[0]['usr']);
        $this->assertEquals('admin',$users[0]['role']);
        $this->assertEquals(3,$users[1]['id']);
        $this->assertEquals('downloader',$users[1]['usr']);
        $this->assertEquals('downloader',$users[1]['role']);
        $this->assertEquals(4,$users[2]['id']);
        $this->assertEquals('uploader',$users[2]['usr']);
        $this->assertEquals('uploader',$users[2]['role']);
    }

    public function testKeywordUsers() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-album-users.php', [
            'query' => [
                'album' => 999,
                'keyword' => 'r'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $users = json_decode($response->getBody(), true);
        $this->assertEquals(3, sizeOf($users));
        $this->assertEquals(1,$users[0]['id']);
        $this->assertEquals('msaperst',$users[0]['usr']);
        $this->assertEquals('admin',$users[0]['role']);
        $this->assertEquals(3,$users[1]['id']);
        $this->assertEquals('downloader',$users[1]['usr']);
        $this->assertEquals('downloader',$users[1]['role']);
        $this->assertEquals(4,$users[2]['id']);
        $this->assertEquals('uploader',$users[2]['usr']);
        $this->assertEquals('uploader',$users[2]['role']);
    }

    public function testKeywordsUsers() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-album-users.php', [
            'query' => [
                'album' => 999,
                'keyword' => 'der'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $users = json_decode($response->getBody(), true);
        $this->assertEquals(2, sizeOf($users));
        $this->assertEquals(3,$users[0]['id']);
        $this->assertEquals('downloader',$users[0]['usr']);
        $this->assertEquals('downloader',$users[0]['role']);
        $this->assertEquals(4,$users[1]['id']);
        $this->assertEquals('uploader',$users[1]['usr']);
        $this->assertEquals('uploader',$users[1]['role']);
    }

    public function testTooKeyword() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/search-album-users.php', [
            'query' => [
                'album' => 999,
                'keyword' => 'xyz'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(), json_decode($response->getBody(), true));
    }
}