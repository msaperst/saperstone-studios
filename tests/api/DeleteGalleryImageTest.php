<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class DeleteGalleryImageTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `galleries` (`id`, `parent`, `image`, `title`, `comment`) VALUES ('999', '1', 'sample.jpg', 'Sample', NULL);");
        $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (998, '999', '', '0', '', '/portrait/img/sample/sample1.jpg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (999, '999', '', '1', '', '/portrait/img/sample/sample2.jpg', '300', '400', '1');");
        $oldmask = umask(0);
        mkdir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/sample');
        chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/sample', 0777);
        touch(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/sample/sample1.jpg');
        chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/sample/sample1.jpg', 0777);
        touch(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/sample/sample2.jpg');
        chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/sample/sample2.jpg', 0777);
        umask($oldmask);
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `galleries` WHERE `galleries`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `gallery_images` WHERE `gallery_images`.`gallery` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `galleries`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `galleries` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `gallery_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `gallery_images` AUTO_INCREMENT = $count;");
        system("rm -rf " . escapeshellarg(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/sample'));
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/delete-gallery-image.php');
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
            $this->http->request('POST', 'api/delete-gallery-image.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoGallery() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-gallery-image.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id is required", (string)$response->getBody());
    }

    public function testBlankGallery() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-gallery-image.php', [
            'form_params' => [
                'gallery' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id can not be blank", (string)$response->getBody());
    }

    public function testLetterGallery() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-gallery-image.php', [
            'form_params' => [
                'gallery' => 'a'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id does not match any galleries", (string)$response->getBody());
    }

    public function testBadGalleryId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-gallery-image.php', [
            'form_params' => [
                'gallery' => 9999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id does not match any galleries", (string)$response->getBody());
    }

    public function testNoImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-gallery-image.php', [
            'form_params' => [
                'gallery' => 999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image id is required", (string)$response->getBody());
    }

    public function testBlankImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-gallery-image.php', [
            'form_params' => [
                'gallery' => 999,
                'image' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image id can not be blank", (string)$response->getBody());
    }

    public function testDeleteImage1() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-gallery-image.php', [
            'form_params' => [
                'gallery' => 999,
                'image' => 998
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $images = $this->sql->getRows("SELECT * FROM `gallery_images` WHERE `gallery_images`.`gallery` = 999;");
        $this->assertEquals(1, sizeOf($images));
        $this->assertEquals(999, $images[0]['id']);
        $this->assertEquals(999, $images[0]['gallery']);
        $this->assertEquals('', $images[0]['title']);
        $this->assertEquals(0, $images[0]['sequence']);
        $this->assertEquals('', $images[0]['caption']);
        $this->assertEquals('/portrait/img/sample/sample2.jpg', $images[0]['location']);
        $this->assertEquals(300, $images[0]['width']);
        $this->assertEquals(400, $images[0]['height']);
        $this->assertEquals(1, $images[0]['active']);
        $this->assertFalse(file_exists('content/portrait/sample/sample1.jpg'));
    }
}

?>