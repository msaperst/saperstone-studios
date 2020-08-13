<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class GetCartTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement('INSERT INTO `cart` VALUES(1,997,995,1,2)');
        $this->sql->executeStatement('INSERT INTO `cart` VALUES(1,997,996,0,0)');
        $this->sql->executeStatement('INSERT INTO `cart` VALUES(2,997,997,5,3)');
        $this->sql->executeStatement('INSERT INTO `cart` VALUES(1,998,998,31,1)');
        $this->sql->executeStatement('INSERT INTO `cart` VALUES(1,999,999,23,1)');
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (995, '997', 'm', '1', 'v', 'a', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (996, '997', 'n', '2', 'w', 'b', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (997, '997', 'o', '3', 'x', 'c', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (998, '998', 'p', '0', 'y', 'd', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (999, '999', 'q', '5', 'z', 'e', '300', '400', '1');");
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `cart` WHERE `cart`.`album` = 997;");
        $this->sql->executeStatement("DELETE FROM `cart` WHERE `cart`.`album` = 998;");
        $this->sql->executeStatement("DELETE FROM `cart` WHERE `cart`.`album` = 999;");
        $this->sql->executeStatement("DELETE FROM `cart` WHERE `cart`.`user` = 1;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 997;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 998;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/get-cart.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You must be logged in to perform this action", (string)$e->getResponse()->getBody());
        }
    }

    public function testGottenCart() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/get-cart.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $cartDetails = json_decode($response->getBody(), true);
        $this->assertEquals(3, sizeOf($cartDetails));
        $this->assertEquals(1, $cartDetails[0]['user']);
        $this->assertEquals(997, $cartDetails[0]['album']);
        $this->assertEquals(995, $cartDetails[0]['image']);
        $this->assertEquals(1, $cartDetails[0]['product']);
        $this->assertEquals(2, $cartDetails[0]['count']);
        $this->assertEquals(1, $cartDetails[0]['id']);
        $this->assertEquals('m', $cartDetails[0]['title']);
        $this->assertEquals(1, $cartDetails[0]['sequence']);
        $this->assertEquals('v', $cartDetails[0]['caption']);
        $this->assertEquals('a', $cartDetails[0]['location']);
        $this->assertEquals('300', $cartDetails[0]['width']);
        $this->assertEquals('400', $cartDetails[0]['height']);
        $this->assertEquals(1, $cartDetails[0]['active']);
        $this->assertEquals(1, $cartDetails[0]['product_type']);
        $this->assertEquals('12x12', $cartDetails[0]['size']);
        $this->assertEquals('300.00', $cartDetails[0]['price']);
        $this->assertEquals('signature', $cartDetails[0]['category']);
        $this->assertEquals('Acrylic Prints', $cartDetails[0]['name']);
        $this->assertEquals(array(), $cartDetails[0]['options']);

        $this->assertEquals(1, $cartDetails[1]['user']);
        $this->assertEquals(998, $cartDetails[1]['album']);
        $this->assertEquals(998, $cartDetails[1]['image']);
        $this->assertEquals(31, $cartDetails[1]['product']);
        $this->assertEquals(1, $cartDetails[1]['count']);
        $this->assertEquals(10, $cartDetails[1]['id']);
        $this->assertEquals('p', $cartDetails[1]['title']);
        $this->assertEquals(0, $cartDetails[1]['sequence']);
        $this->assertEquals('y', $cartDetails[1]['caption']);
        $this->assertEquals('d', $cartDetails[1]['location']);
        $this->assertEquals('300', $cartDetails[1]['width']);
        $this->assertEquals('400', $cartDetails[1]['height']);
        $this->assertEquals(1, $cartDetails[1]['active']);
        $this->assertEquals(10, $cartDetails[1]['product_type']);
        $this->assertEquals('Full', $cartDetails[1]['size']);
        $this->assertEquals('80.00', $cartDetails[1]['price']);
        $this->assertEquals('digital', $cartDetails[1]['category']);
        $this->assertEquals('Per File', $cartDetails[1]['name']);
        $this->assertEquals(array(), $cartDetails[1]['options']);

        $this->assertEquals(1, $cartDetails[2]['user']);
        $this->assertEquals(999, $cartDetails[2]['album']);
        $this->assertEquals(999, $cartDetails[2]['image']);
        $this->assertEquals(23, $cartDetails[2]['product']);
        $this->assertEquals(1, $cartDetails[2]['count']);
        $this->assertEquals(7, $cartDetails[2]['id']);
        $this->assertEquals('q', $cartDetails[2]['title']);
        $this->assertEquals(5, $cartDetails[2]['sequence']);
        $this->assertEquals('z', $cartDetails[2]['caption']);
        $this->assertEquals('e', $cartDetails[2]['location']);
        $this->assertEquals('300', $cartDetails[2]['width']);
        $this->assertEquals('400', $cartDetails[2]['height']);
        $this->assertEquals(1, $cartDetails[2]['active']);
        $this->assertEquals(7, $cartDetails[2]['product_type']);
        $this->assertEquals('8 Wallets', $cartDetails[2]['size']);
        $this->assertEquals('50.00', $cartDetails[2]['price']);
        $this->assertEquals('prints', $cartDetails[2]['category']);
        $this->assertEquals('Gift Prints', $cartDetails[2]['name']);
        $this->assertEquals(2, sizeof($cartDetails[2]['options']));
        $this->assertEquals('Glossy', $cartDetails[2]['options'][0]);
        $this->assertEquals('Lustre', $cartDetails[2]['options'][1]);
    }
}