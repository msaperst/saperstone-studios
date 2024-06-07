<?php

namespace api;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class GetBlogsSearchDetailsTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('997', 'Sample Blog', '2031-01-01', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('998', 'Sample Blog', '2031-01-01', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('999', 'Sample Blog', '2031-01-01', '', 0, 1)");
        $this->sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 29)");
        $this->sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('997', 29)");
        $this->sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('997', 30)");
        $this->sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('999', '2', 'Some blog text xyz')");
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 997;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 998;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 997;");
        $this->sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;");
        $this->sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testGetBlogNoTerm() {
        $response = $this->http->request('GET', 'api/get-blogs-search-details.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNull(json_decode($response->getBody(), true));
    }

    public function testGetBlogAll() {
        $response = $this->http->request('GET', 'api/get-blogs-search-details.php', [
            'query' => [
                'searchTerm' => 'Sample'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $blogsDetails = json_decode($response->getBody(), true);
        $this->assertTrue(3 <= sizeof($blogsDetails));  //there may be more depending on other things in the test DB
        $this->assertEquals(999, $blogsDetails[0]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails[0]['title']);
        $this->assertNull($blogsDetails[0]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails[0]['date']);
        $this->assertEquals('', $blogsDetails[0]['preview']);
        $this->assertEquals(0, $blogsDetails[0]['offset']);
        $this->assertEquals(1, $blogsDetails[0]['active']);
        $this->assertEquals(998, $blogsDetails[1]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails[1]['title']);
        $this->assertNull($blogsDetails[1]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails[1]['date']);
        $this->assertEquals('', $blogsDetails[1]['preview']);
        $this->assertEquals(0, $blogsDetails[1]['offset']);
        $this->assertEquals(1, $blogsDetails[1]['active']);
        $this->assertEquals(997, $blogsDetails[2]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails[2]['title']);
        $this->assertNull($blogsDetails[2]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails[2]['date']);
        $this->assertEquals('', $blogsDetails[2]['preview']);
        $this->assertEquals(0, $blogsDetails[2]['offset']);
        $this->assertEquals(1, $blogsDetails[2]['active']);
    }

    public function testGetBlogSecond() {
        $response = $this->http->request('GET', 'api/get-blogs-search-details.php', [
            'query' => [
                'searchTerm' => 'Sample',
                'start' => 1,
                'howMany' => 1
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $blogsDetails = json_decode($response->getBody(), true);
        $this->assertEquals(1, sizeof($blogsDetails));
        $this->assertEquals(998, $blogsDetails[0]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails[0]['title']);
        $this->assertNull($blogsDetails[0]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails[0]['date']);
        $this->assertEquals('', $blogsDetails[0]['preview']);
        $this->assertEquals(0, $blogsDetails[0]['offset']);
        $this->assertEquals(1, $blogsDetails[0]['active']);
    }

    public function testGetBlogText() {
        $response = $this->http->request('GET', 'api/get-blogs-search-details.php', [
            'query' => [
                'searchTerm' => 'xyz'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $blogsDetails = json_decode($response->getBody(), true);
        $this->assertEquals(1, sizeof($blogsDetails));
        $this->assertEquals(999, $blogsDetails[0]['id']);
        $this->assertEquals('Sample Blog', $blogsDetails[0]['title']);
        $this->assertNull($blogsDetails[0]['safe_title']);
        $this->assertEquals('2031-01-01', $blogsDetails[0]['date']);
        $this->assertEquals('', $blogsDetails[0]['preview']);
        $this->assertEquals(0, $blogsDetails[0]['offset']);
        $this->assertEquals(1, $blogsDetails[0]['active']);
    }

    public function testGetBlogTextNext() {
        $response = $this->http->request('GET', 'api/get-blogs-search-details.php', [
            'query' => [
                'searchTerm' => 'xyz',
                'start' => 1,
                'howMany' => 1
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(), json_decode($response->getBody(), true));
    }
}