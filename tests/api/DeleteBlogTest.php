<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'sql.php';

class DeleteBlogTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('999', 'Sample Blog', '2031-01-01', '', 0)");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '1', 'posts/2031/01/01/sample.jpg', 300, 400, 0, 0)");
        $this->sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 29)");
        $this->sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('999', '2', 'Some blog text')");
        $oldmask = umask(0);
        mkdir('content/blog/2031/01/01', 0777, true);
        touch('content/blog/2031/01/01/sample.jpg');
        chmod('content/blog/2031/01/01/sample.jpg', 0777);
        umask($oldmask);
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = 999;");
        $this->sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;");
        $this->sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
        system("rm -rf " . escapeshellarg('content/blog/2031'));
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/delete-blog.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], 'localhost');
        try {
            $this->http->request('POST', 'api/delete-blog.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoBlog() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-blog.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id is required", (string)$response->getBody());
    }

    public function testBlankBlog() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-blog.php', [
            'form_params' => [
                'post' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id can not be blank", (string)$response->getBody());
    }

    public function testLetterBlog() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-blog.php', [
            'form_params' => [
                'post' => 'a'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id does not match any blogs", (string)$response->getBody());
    }

    public function testBadBlog() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-blog.php', [
            'form_params' => [
                'post' => 9999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id does not match any blogs", (string)$response->getBody());
    }

    public function testDeleteBlog() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-blog.php', [
            'form_params' => [
                'post' => 999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 999;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 999;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 999;"));
        $this->assertFalse(file_exists('content/blog/2031/01/01/sample.jpg'));
    }
}

?>