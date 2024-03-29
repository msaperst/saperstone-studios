<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class GetAllFavoritesTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('998', 'sample-album', 'sample album for testing', 'sample', 4, '123');");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4, '123');");
        $this->sql->executeStatement("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES (1, '998', 996);");
        $this->sql->executeStatement("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES (1, '998', 997);");
        $this->sql->executeStatement("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES (1, '999', 998);");
        $this->sql->executeStatement("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('192.168.1.2', '998', 996);");
        $this->sql->executeStatement("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('192.168.1.2', '999', 999);");
        $this->sql->executeStatement("INSERT INTO `favorites` (`user`, `album`, `image`) VALUES ('192.168.1.3', '998', 997);");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (996, '998', '', '0', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (997, '998', '', '1', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (998, '999', '', '1', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (999, '999', '', '3', '', '', '300', '400', '1');");
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 998;");
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `favorites` WHERE `favorites`.`album` = 998;");
        $this->sql->executeStatement("DELETE FROM `favorites` WHERE `favorites`.`album` = 999;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 998;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/get-all-favorites.php');
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
            $this->http->request('POST', 'api/get-all-favorites.php', [
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
        $response = $this->http->request('GET', 'api/get-all-favorites.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $favorites = json_decode($response->getBody(), true);
        $this->assertTrue(3 <= sizeOf($favorites));  //there may be more depending on other things in the test DB
        $this->assertTrue(2 <= sizeOf($favorites[1]));   //there may be more depending on other things in the test DB
        $this->assertEquals(2, sizeOf($favorites[1][998]));
        $this->assertEquals('', $favorites[1][998][0]['title']);
        $this->assertEquals(0, $favorites[1][998][0]['sequence']);
        $this->assertEquals('', $favorites[1][998][0]['location']);
        $this->assertEquals('msaperst', $favorites[1][998][0]['usr']);
        $this->assertEquals('', $favorites[1][998][1]['title']);
        $this->assertEquals(1, $favorites[1][998][1]['sequence']);
        $this->assertEquals('', $favorites[1][998][1]['location']);
        $this->assertEquals('msaperst', $favorites[1][998][1]['usr']);
        $this->assertEquals(1, sizeOf($favorites[1][999]));
        $this->assertEquals('', $favorites[1][999][0]['title']);
        $this->assertEquals(1, $favorites[1][999][0]['sequence']);
        $this->assertEquals('', $favorites[1][999][0]['location']);
        $this->assertEquals('msaperst', $favorites[1][999][0]['usr']);
        $this->assertEquals(2, sizeOf($favorites['192.168.1.2']));
        $this->assertEquals(1, sizeOf($favorites['192.168.1.2'][998]));
        $this->assertEquals('', $favorites['192.168.1.2'][998][0]['title']);
        $this->assertEquals(0, $favorites['192.168.1.2'][998][0]['sequence']);
        $this->assertEquals('', $favorites['192.168.1.2'][998][0]['location']);
        $this->assertNull($favorites['192.168.1.2'][998][0]['usr']);
        $this->assertEquals(1, sizeOf($favorites['192.168.1.2'][999]));
        $this->assertEquals('', $favorites['192.168.1.2'][999][0]['title']);
        $this->assertEquals(3, $favorites['192.168.1.2'][999][0]['sequence']);
        $this->assertEquals('', $favorites['192.168.1.2'][999][0]['location']);
        $this->assertNull($favorites['192.168.1.2'][999][0]['usr']);
        $this->assertEquals(1, sizeOf($favorites['192.168.1.3']));
        $this->assertEquals(1, sizeOf($favorites['192.168.1.3'][998]));
        $this->assertEquals('', $favorites['192.168.1.3'][998][0]['title']);
        $this->assertEquals(1, $favorites['192.168.1.3'][998][0]['sequence']);
        $this->assertEquals('', $favorites['192.168.1.3'][998][0]['location']);
        $this->assertNull($favorites['192.168.1.3'][998][0]['usr']);
    }

    public function testAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/get-all-favorites.php', [
            'query' => [
                'album' => '999'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $favorites = json_decode($response->getBody(), true);
        $this->assertEquals(2, sizeOf($favorites));
        $this->assertEquals(1, sizeOf($favorites[1]));
        $this->assertEquals('', $favorites[1][0]['title']);
        $this->assertEquals(1, $favorites[1][0]['sequence']);
        $this->assertEquals('', $favorites[1][0]['location']);
        $this->assertEquals('msaperst', $favorites[1][0]['usr']);
        $this->assertEquals(1, sizeOf($favorites['192.168.1.2']));
        $this->assertEquals('', $favorites['192.168.1.2'][0]['title']);
        $this->assertEquals(3, $favorites['192.168.1.2'][0]['sequence']);
        $this->assertEquals('', $favorites['192.168.1.2'][0]['location']);
        $this->assertNull($favorites['192.168.1.2'][0]['usr']);
    }
}

?>