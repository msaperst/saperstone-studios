<?php

namespace api;

use Blog;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use SocialMedia;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class PublishBlogPostTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('999', 'Sample Blog', '2031-01-01', '/posts/2031/01/01/flower.jpeg', 0)");
        $oldmask = umask(0);
        mkdir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/2031/01/01', 0777, true);
        copy(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/2031/01/01/flower.jpeg');
        chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/2031/01/01/flower.jpeg', 0777);
        umask($oldmask);
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
        system("rm -rf " . escapeshellarg(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/2031'));
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/publish-blog-post.php');
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
            $this->http->request('POST', 'api/publish-blog-post.php', [
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
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/publish-blog-post.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id is required", (string)$response->getBody());
    }

    public function testBlankBlog() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/publish-blog-post.php', [
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
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/publish-blog-post.php', [
            'form_params' => [
                'post' => 'a'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id does not match any blog posts", (string)$response->getBody());
    }

    public function testBadBlog() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/publish-blog-post.php', [
            'form_params' => [
                'post' => 9999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id does not match any blog posts", (string)$response->getBody());
    }

    public function testPublishBlog() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/publish-blog-post.php', [
                'form_params' => [
                    'post' => 999
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals("", (string)$response->getBody());
            $this->assertEquals(1, $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 999;")['active']);
            $this->assertNotEquals(0, $this->sql->getRow("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 999;")['twitter']);
        } finally {
            $socialMedia = new SocialMedia();
            $socialMedia->removeBlogFromTwitter(new Blog(999));
        }
    }
}