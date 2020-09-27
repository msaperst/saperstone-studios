<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UpdateGalleryOrderTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `galleries` (`id`, `parent`, `image`, `title`, `comment`) VALUES ('999', '1', 'sample.jpg', 'Sample', NULL);");
        $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (998, '999', '', '0', '', '/portrait/img/sample/sample1.jpg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (999, '999', '', '1', '', '/portrait/img/sample/sample2.jpg', '300', '400', '1');");
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM galleries WHERE id = 999");
        $this->sql->executeStatement("DELETE FROM gallery_images WHERE gallery = 999");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `galleries`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `galleries` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `gallery_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `gallery_images` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/update-gallery-order.php');
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
            $this->http->request('POST', 'api/update-gallery-order.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoGalleryId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-order.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id is required", (string)$response->getBody());
    }

    public function testBlankGalleryId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-order.php', [
            'form_params' => [
                'id' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id can not be blank", (string)$response->getBody());
    }

    public function testLetterGalleryId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-order.php', [
            'form_params' => [
                'id' => '546fchgj78'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id does not match any galleries", (string)$response->getBody());
    }

    public function testBadGalleryId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-order.php', [
            'form_params' => [
                'id' => 9999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id does not match any galleries", (string)$response->getBody());
    }

    public function testNoImagesSet() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-order.php', [
            'form_params' => [
                'id' => 1
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery images are not in the correct format", (string)$response->getBody());
    }

    public function testImagesNotArray() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-order.php', [
            'form_params' => [
                'id' => 1,
                'imgs' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery images are not in the correct format", (string)$response->getBody());
    }

    public function testNotAllImages() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-order.php', [
            'form_params' => [
                'id' => 1,
                'imgs' => array()
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery images are not in the correct format", (string)$response->getBody());
    }

    public function testImages() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-order.php', [
            'form_params' => [
                'id' => 999,
                'imgs' => [
                    [
                        'id' => 999
                    ],
                    [
                        'id' => 998
                    ]
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $images = $this->sql->getRows("SELECT * FROM gallery_images WHERE gallery = 999");
        $this->assertEquals(998, $images[0]['id']);
        $this->assertEquals(1, $images[0]['sequence']);
        $this->assertEquals(999, $images[1]['id']);
        $this->assertEquals(0, $images[1]['sequence']);
    }
}