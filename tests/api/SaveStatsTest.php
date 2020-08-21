<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SaveStatsTest extends TestCase {

    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->disconnect();
    }

    public function testSimpleSave() {
        date_default_timezone_set("America/New_York");
        $response = $this->http->request('GET', 'api/save-stats.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $usage = $this->sql->getRow("SELECT * FROM `usage` WHERE `user` IS NULL ORDER BY time DESC LIMIT 1;");
        $this->assertNull($usage['user']);
        $this->assertTrue(filter_var($usage['ip'], FILTER_VALIDATE_IP) !== false);
        $this->assertNull($usage['latitude']);
        $this->assertNull($usage['longitude']);
        $this->assertStringStartsWith(date('Y-m-d H:i:'), $usage['time']);
        $this->assertEquals('unknown', $usage['browser']);
        $this->assertEquals('unknown', $usage['version']);
        $this->assertEquals('unknown', $usage['os']);
        $this->assertEquals('GuzzleHttp/7', $usage['ua']);
        $this->assertNull($usage['width']);
        $this->assertNull($usage['height']);
        $this->assertEquals('', $usage['url']);
        $this->assertEquals(0, $usage['isTablet']);
        $this->assertEquals(0, $usage['isMobile']);
        $this->assertEquals(0, $usage['isAOL']);
        $this->assertEquals(0, $usage['isFacebook']);
        $this->assertEquals(0, $usage['isRobot']);
    }

    public function testComplexSave() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/save-stats.php', [
            'query' => [
                'resolution' => '20x50'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $usage = $this->sql->getRow("SELECT * FROM `usage` WHERE `user` = 3 ORDER BY time DESC LIMIT 1;");
        $this->assertEquals(3, $usage['user']);
        $this->assertTrue(filter_var($usage['ip'], FILTER_VALIDATE_IP) !== false);
        $this->assertNull($usage['latitude']);
        $this->assertNull($usage['longitude']);
        $this->assertStringStartsWith(date('Y-m-d H:i:'), $usage['time']);
        $this->assertEquals('unknown', $usage['browser']);
        $this->assertEquals('unknown', $usage['version']);
        $this->assertEquals('unknown', $usage['os']);
        $this->assertEquals('GuzzleHttp/7', $usage['ua']);
        $this->assertEquals('20', $usage['width']);
        $this->assertEquals('50', $usage['height']);
        $this->assertEquals('', $usage['url']);
        $this->assertEquals(0, $usage['isTablet']);
        $this->assertEquals(0, $usage['isMobile']);
        $this->assertEquals(0, $usage['isAOL']);
        $this->assertEquals(0, $usage['isFacebook']);
        $this->assertEquals(0, $usage['isRobot']);
    }

    public function testBadResolutionSave() {
        date_default_timezone_set("America/New_York");
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/save-stats.php', [
            'query' => [
                'resolution' => '2050'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $usage = $this->sql->getRow("SELECT * FROM `usage` WHERE `user` = 1 ORDER BY time DESC LIMIT 1;");
        $this->assertEquals(1, $usage['user']);
        $this->assertTrue(filter_var($usage['ip'], FILTER_VALIDATE_IP) !== false);
        $this->assertNull($usage['latitude']);
        $this->assertNull($usage['longitude']);
        $this->assertStringStartsWith(date('Y-m-d H:i:'), $usage['time']);
        $this->assertEquals('unknown', $usage['browser']);
        $this->assertEquals('unknown', $usage['version']);
        $this->assertEquals('unknown', $usage['os']);
        $this->assertEquals('GuzzleHttp/7', $usage['ua']);
        $this->assertNull($usage['width']);
        $this->assertNull($usage['height']);
        $this->assertEquals('', $usage['url']);
        $this->assertEquals(0, $usage['isTablet']);
        $this->assertEquals(0, $usage['isMobile']);
        $this->assertEquals(0, $usage['isAOL']);
        $this->assertEquals(0, $usage['isFacebook']);
        $this->assertEquals(0, $usage['isRobot']);
    }
}