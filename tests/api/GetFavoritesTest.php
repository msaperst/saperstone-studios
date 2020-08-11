<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;
use ZipArchive;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class GetFavoritesTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES (997, 'sample-album-download-all', 'sample album for testing', 'sample');");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES (998, 'sample-album-download-all', 'sample album for testing', 'sample');");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES (999, 'sample-album-download-all', 'sample album for testing', 'sample');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) VALUES (NULL, 997, 'file1', 1, '/albums/sample/file1', '600', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) VALUES (NULL, 998, 'file1', 1, '/albums/sample/file1', '600', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `location`, `width`, `height`, `active`) VALUES (NULL, 999, 'file1', 1, '/albums/sample/file1', '600', '400', '1');");
    }

    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 997");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 997");
        $this->sql->executeStatement("DELETE FROM `favorites` WHERE `favorites`.`album` = 997");
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 998");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 998");
        $this->sql->executeStatement("DELETE FROM `favorites` WHERE `favorites`.`album` = 998");
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 999");
        $this->sql->executeStatement("DELETE FROM `favorites` WHERE `favorites`.`album` = 999");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testUnAuthUserNoFavorites() {
        $response = $this->http->request('GET', 'api/get-favorites.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(), json_decode($response->getBody(), true));
    }

    public function testUnAuthUserFavorites() {
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 997,
                'image' => '0'
            ]
        ]);
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 997,
                'image' => '1'
            ]
        ]);
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 998,
                'image' => '1'
            ]
        ]);
        $response = $this->http->request('GET', 'api/get-favorites.php');
        $this->assertEquals(200, $response->getStatusCode());
        $favorites = json_decode($response->getBody(), true);
        $this->assertEquals(2, sizeof($favorites));
        $this->assertEquals(1, sizeof($favorites[997]));
        $this->assertEquals(1, $favorites[997][0]['id']);
        $this->assertEquals(997, $favorites[997][0]['album']);
        $this->assertEquals('file1', $favorites[997][0]['title']);
        $this->assertEquals(1, $favorites[997][0]['sequence']);
        $this->assertEquals('', $favorites[997][0]['caption']);
        $this->assertEquals('/albums/sample/file1', $favorites[997][0]['location']);
        $this->assertEquals(600, $favorites[997][0]['width']);
        $this->assertEquals(400, $favorites[997][0]['height']);
        $this->assertEquals(1, $favorites[997][0]['active']);
        $this->assertEquals(1, sizeof($favorites[998]));
        $this->assertEquals(2, $favorites[998][0]['id']);
        $this->assertEquals(998, $favorites[998][0]['album']);
        $this->assertEquals('file1', $favorites[998][0]['title']);
        $this->assertEquals(1, $favorites[998][0]['sequence']);
        $this->assertEquals('', $favorites[998][0]['caption']);
        $this->assertEquals('/albums/sample/file1', $favorites[998][0]['location']);
        $this->assertEquals(600, $favorites[998][0]['width']);
        $this->assertEquals(400, $favorites[998][0]['height']);
        $this->assertEquals(1, $favorites[998][0]['active']);
    }

    public function testUnAuthUserFavoritesAlbum() {
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 997,
                'image' => '0'
            ]
        ]);
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 997,
                'image' => '1'
            ]
        ]);
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 998,
                'image' => '1'
            ]
        ]);
        $response = $this->http->request('GET', 'api/get-favorites.php', [
            'query' => [
                'album' => 997
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $favorites = json_decode($response->getBody(), true);
        $this->assertEquals(1, sizeof($favorites));
        $this->assertEquals(1, $favorites[0]['id']);
        $this->assertEquals(997, $favorites[0]['album']);
        $this->assertEquals('file1', $favorites[0]['title']);
        $this->assertEquals(1, $favorites[0]['sequence']);
        $this->assertEquals('', $favorites[0]['caption']);
        $this->assertEquals('/albums/sample/file1', $favorites[0]['location']);
        $this->assertEquals(600, $favorites[0]['width']);
        $this->assertEquals(400, $favorites[0]['height']);
        $this->assertEquals(1, $favorites[0]['active']);
    }

    public function testAuthUserNoFavorites() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/get-favorites.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(), json_decode($response->getBody(), true));
    }

    public function testAuthUserFavorites() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 997,
                'image' => '0'
            ],
            'cookies' => $cookieJar
        ]);
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 997,
                'image' => '1'
            ],
            'cookies' => $cookieJar
        ]);
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 998,
                'image' => '1'
            ],
            'cookies' => $cookieJar
        ]);
        $response = $this->http->request('GET', 'api/get-favorites.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $favorites = json_decode($response->getBody(), true);
        $this->assertEquals(2, sizeof($favorites));
        $this->assertEquals(1, sizeof($favorites[997]));
        $this->assertEquals(1, $favorites[997][0]['id']);
        $this->assertEquals(997, $favorites[997][0]['album']);
        $this->assertEquals('file1', $favorites[997][0]['title']);
        $this->assertEquals(1, $favorites[997][0]['sequence']);
        $this->assertEquals('', $favorites[997][0]['caption']);
        $this->assertEquals('/albums/sample/file1', $favorites[997][0]['location']);
        $this->assertEquals(600, $favorites[997][0]['width']);
        $this->assertEquals(400, $favorites[997][0]['height']);
        $this->assertEquals(1, $favorites[997][0]['active']);
        $this->assertEquals(1, sizeof($favorites[998]));
        $this->assertEquals(2, $favorites[998][0]['id']);
        $this->assertEquals(998, $favorites[998][0]['album']);
        $this->assertEquals('file1', $favorites[998][0]['title']);
        $this->assertEquals(1, $favorites[998][0]['sequence']);
        $this->assertEquals('', $favorites[998][0]['caption']);
        $this->assertEquals('/albums/sample/file1', $favorites[998][0]['location']);
        $this->assertEquals(600, $favorites[998][0]['width']);
        $this->assertEquals(400, $favorites[998][0]['height']);
        $this->assertEquals(1, $favorites[998][0]['active']);
    }

    public function testAuthUserFavoritesAlbum() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 997,
                'image' => '0'
            ],
            'cookies' => $cookieJar
        ]);
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 997,
                'image' => '1'
            ],
            'cookies' => $cookieJar
        ]);
        $this->http->request('POST', 'api/set-favorite.php', [
            'form_params' => [
                'album' => 998,
                'image' => '1'
            ],
            'cookies' => $cookieJar
        ]);
        $response = $this->http->request('GET', 'api/get-favorites.php', [
            'query' => [
                'album' => 997
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $favorites = json_decode($response->getBody(), true);
        $this->assertEquals(1, sizeof($favorites));
        $this->assertEquals(1, $favorites[0]['id']);
        $this->assertEquals(997, $favorites[0]['album']);
        $this->assertEquals('file1', $favorites[0]['title']);
        $this->assertEquals(1, $favorites[0]['sequence']);
        $this->assertEquals('', $favorites[0]['caption']);
        $this->assertEquals('/albums/sample/file1', $favorites[0]['location']);
        $this->assertEquals(600, $favorites[0]['width']);
        $this->assertEquals(400, $favorites[0]['height']);
        $this->assertEquals(1, $favorites[0]['active']);
    }
}

?>