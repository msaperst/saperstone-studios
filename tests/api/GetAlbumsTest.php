<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class GetAlbumsTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('997', 'sample-album', 'sample album for testing', 'sample', 1, '1234');");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ('998', 'sample-album', 'sample album for testing', 'sample', 5);");
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4, '123');");
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (4, '998');");
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (4, '999');");
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 997;");
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
            $this->http->request('GET', 'api/get-albums.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals('You must be logged in to perform this action', $e->getResponse()->getBody());
        }
    }

    public function testAdminUser() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/get-albums.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $albums = json_decode($response->getBody(), true)['data'];
        $this->assertTrue(3 <= sizeOf($albums));  //there may be more depending on other things in the test DB
        //need to find our albums...
        $id = 0;
        for( $id; $id < sizeof($albums); $id++) {
            if( $albums[$id]['id'] == 997) {
                break;
            }
        }
        $this->assertEquals(997, $albums[$id]['id']);
        $this->assertEquals('sample-album', $albums[$id]['name']);
        $this->assertEquals('sample album for testing', $albums[$id]['description']);
        $this->assertEquals(date('Y-m-d'), $albums[$id]['date']);
        $this->assertEquals(0, $albums[$id]['images']);
        $this->assertEquals(0, $albums[$id]['lastAccessed']);
        $this->assertEquals(1234, $albums[$id]['code']);
        $this->assertEquals(998, $albums[$id+1]['id']);
        $this->assertEquals('sample-album', $albums[$id+1]['name']);
        $this->assertEquals('sample album for testing', $albums[$id+1]['description']);
        $this->assertEquals(date('Y-m-d'), $albums[$id+1]['date']);
        $this->assertEquals(0, $albums[$id+1]['images']);
        $this->assertEquals(0, $albums[$id+1]['lastAccessed']);
        $this->assertEquals('', $albums[$id+1]['code']);
        $this->assertEquals(999, $albums[$id+2]['id']);
        $this->assertEquals('sample-album', $albums[$id+2]['name']);
        $this->assertEquals('sample album for testing', $albums[$id+2]['description']);
        $this->assertEquals(date('Y-m-d'), $albums[$id+2]['date']);
        $this->assertEquals(0, $albums[$id+2]['images']);
        $this->assertEquals(0, $albums[$id+2]['lastAccessed']);
        $this->assertEquals(123, $albums[$id+2]['code']);
    }

    public function testUploaderUser() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/get-albums.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $albums = json_decode($response->getBody(), true)['data'];
        $this->assertTrue(2 <= sizeOf($albums));  //there may be more depending on other things in the test DB
        $this->assertEquals(998, $albums[0]['id']);
        $this->assertEquals('sample-album', $albums[0]['name']);
        $this->assertEquals('sample album for testing', $albums[0]['description']);
        $this->assertEquals(date('Y-m-d'), $albums[0]['date']);
        $this->assertEquals(0, $albums[0]['images']);
        $this->assertEquals(999, $albums[1]['id']);
        $this->assertEquals('sample-album', $albums[1]['name']);
        $this->assertEquals('sample album for testing', $albums[1]['description']);
        $this->assertEquals(date('Y-m-d'), $albums[1]['date']);
        $this->assertEquals(0, $albums[1]['images']);
    }
}

?>