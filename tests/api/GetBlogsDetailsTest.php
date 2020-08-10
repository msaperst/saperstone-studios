<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class GetBlogsDetailsTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('997', 'Sample Blog', '2031-01-01', '', 0, 0)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('998', 'Sample Blog', '2031-01-01', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('999', 'Sample Blog', '2031-01-01', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('998', 29)");
        $this->sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 29)");
        $this->sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 30)");
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 997;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 998;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;");
        $this->sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 998;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testAllBlogsDetails() {
        $response = $this->http->request('GET', 'api/get-blogs-details.php');
        $this->assertEquals(200, $response->getStatusCode());
        $blogsDetails = json_decode($response->getBody(), true);
        $this->assertEquals(1, sizeof($blogsDetails));
        $this->assertTrue(2 <= sizeof($blogsDetails['data']));   //there may be more depending on other things in the test DB
        $this->assertEquals(998, $blogsDetails['data'][0]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails['data'][0]['title']);
        $this->assertNull($blogsDetails['data'][0]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails['data'][0]['date']);
        $this->assertEquals('', $blogsDetails['data'][0]['preview']);
        $this->assertEquals(0, $blogsDetails['data'][0]['offset']);
        $this->assertEquals(1, $blogsDetails['data'][0]['active']);
        $this->assertEquals(0, $blogsDetails['data'][0]['twitter']);
        $this->assertEquals(999, $blogsDetails['data'][1]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails['data'][1]['title']);
        $this->assertNull($blogsDetails['data'][1]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails['data'][1]['date']);
        $this->assertEquals('', $blogsDetails['data'][1]['preview']);
        $this->assertEquals(0, $blogsDetails['data'][1]['offset']);
        $this->assertEquals(1, $blogsDetails['data'][1]['active']);
        $this->assertEquals(0, $blogsDetails['data'][1]['twitter']);
    }

    public function testAllBlogsDetailsByTag() {
        $response = $this->http->request('GET', 'api/get-blogs-details.php', [
            'query' => [
                'tag' => ['30']
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $blogsDetails = json_decode($response->getBody(), true);
        $this->assertEquals(1, sizeof($blogsDetails));
        $this->assertTrue(1 <= sizeof($blogsDetails['data']));    //there may be more depending on other things in the test DB
        $this->assertEquals(999, $blogsDetails['data'][0]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails['data'][0]['title']);
        $this->assertNull($blogsDetails['data'][0]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails['data'][0]['date']);
        $this->assertEquals('', $blogsDetails['data'][0]['preview']);
        $this->assertEquals(0, $blogsDetails['data'][0]['offset']);
        $this->assertEquals(1, $blogsDetails['data'][0]['active']);
        $this->assertEquals(0, $blogsDetails['data'][0]['twitter']);
    }

    public function testAllBlogsDetailsByTags() {
        $response = $this->http->request('GET', 'api/get-blogs-details.php', [
            'query' => [
                'tag' => ['29', '30']
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $blogsDetails = json_decode($response->getBody(), true);
        $this->assertEquals(1, sizeof($blogsDetails));
        $this->assertEquals(1, sizeof($blogsDetails['data']));
        $this->assertEquals(999, $blogsDetails['data'][0]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails['data'][0]['title']);
        $this->assertNull($blogsDetails['data'][0]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails['data'][0]['date']);
        $this->assertEquals('', $blogsDetails['data'][0]['preview']);
        $this->assertEquals(0, $blogsDetails['data'][0]['offset']);
        $this->assertEquals(1, $blogsDetails['data'][0]['active']);
        $this->assertEquals(0, $blogsDetails['data'][0]['twitter']);
    }

    public function testAllBlogsDetailsByTagsNoMatch() {
        $response = $this->http->request('GET', 'api/get-blogs-details.php', [
            'query' => [
                'tag' => ['29', '30', '31']
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $blogsDetails = json_decode($response->getBody(), true);
        $this->assertEquals(1, sizeof($blogsDetails));
        $this->assertEquals(0, sizeof($blogsDetails['data']));
    }

    public function testAllBlogsDetailsAdmin() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/get-blogs-details.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $blogsDetails = json_decode($response->getBody(), true);
        $this->assertEquals(1, sizeof($blogsDetails));
        $this->assertTrue(2 <= sizeof($blogsDetails['data']));       //there may be more depending on other things in the test DB
        $this->assertEquals(998, $blogsDetails['data'][0]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails['data'][0]['title']);
        $this->assertNull($blogsDetails['data'][0]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails['data'][0]['date']);
        $this->assertEquals('', $blogsDetails['data'][0]['preview']);
        $this->assertEquals(0, $blogsDetails['data'][0]['offset']);
        $this->assertEquals(1, $blogsDetails['data'][0]['active']);
        $this->assertEquals(0, $blogsDetails['data'][0]['twitter']);
        $this->assertEquals(999, $blogsDetails['data'][1]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails['data'][1]['title']);
        $this->assertNull($blogsDetails['data'][1]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails['data'][1]['date']);
        $this->assertEquals('', $blogsDetails['data'][1]['preview']);
        $this->assertEquals(0, $blogsDetails['data'][1]['offset']);
        $this->assertEquals(1, $blogsDetails['data'][1]['active']);
        $this->assertEquals(0, $blogsDetails['data'][1]['twitter']);
    }

    public function testAllBlogsDetailsAdminInactive() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/get-blogs-details.php', [
            'query' => [
                'a' => '1'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $blogsDetails = json_decode($response->getBody(), true);
        $this->assertEquals(1, sizeof($blogsDetails));
        $this->assertTrue(3 <= sizeof($blogsDetails['data']));       //there may be more depending on other things in the test DB
        $this->assertEquals(997, $blogsDetails['data'][0]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails['data'][0]['title']);
        $this->assertNull($blogsDetails['data'][0]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails['data'][0]['date']);
        $this->assertEquals('', $blogsDetails['data'][0]['preview']);
        $this->assertEquals(0, $blogsDetails['data'][0]['offset']);
        $this->assertEquals(0, $blogsDetails['data'][0]['active']);
        $this->assertEquals(0, $blogsDetails['data'][0]['twitter']);

        $this->assertEquals(998, $blogsDetails['data'][1]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails['data'][1]['title']);
        $this->assertNull($blogsDetails['data'][1]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails['data'][1]['date']);
        $this->assertEquals('', $blogsDetails['data'][1]['preview']);
        $this->assertEquals(0, $blogsDetails['data'][1]['offset']);
        $this->assertEquals(1, $blogsDetails['data'][1]['active']);
        $this->assertEquals(0, $blogsDetails['data'][1]['twitter']);

        $this->assertEquals(999, $blogsDetails['data'][2]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails['data'][2]['title']);
        $this->assertNull($blogsDetails['data'][2]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails['data'][2]['date']);
        $this->assertEquals('', $blogsDetails['data'][2]['preview']);
        $this->assertEquals(0, $blogsDetails['data'][2]['offset']);
        $this->assertEquals(1, $blogsDetails['data'][2]['active']);
        $this->assertEquals(0, $blogsDetails['data'][2]['twitter']);
    }

    public function testAllBlogsDetailsLimit() {
        $response = $this->http->request('GET', 'api/get-blogs-details.php', [
            'query' => [
                'start' => '1',
                'howMany' => '1'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $blogsDetails = json_decode($response->getBody(), true);
        $this->assertEquals(1, sizeof($blogsDetails));
        $this->assertEquals(1, sizeof($blogsDetails['data']));
        $this->assertEquals(999, $blogsDetails['data'][0]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails['data'][0]['title']);
        $this->assertNull($blogsDetails['data'][0]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails['data'][0]['date']);
        $this->assertEquals('', $blogsDetails['data'][0]['preview']);
        $this->assertEquals(0, $blogsDetails['data'][0]['offset']);
        $this->assertEquals(1, $blogsDetails['data'][0]['active']);
        $this->assertEquals(0, $blogsDetails['data'][0]['twitter']);
    }
}

?>