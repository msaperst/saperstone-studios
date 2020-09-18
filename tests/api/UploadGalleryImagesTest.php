<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UploadGalleryImagesTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `gallery_images` WHERE `gallery_images`.`title` = 'flower.jpeg';");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `gallery_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `gallery_images` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/upload-gallery-images.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals('', $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        try {
            $this->http->request('POST', 'api/upload-gallery-images.php', [
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
        $response = $this->http->request('POST', 'api/upload-gallery-images.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id is required", (string)$response->getBody());
    }

    public function testBlankGalleryId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-gallery-images.php', [
            'form_params' => [
                'gallery' => ''
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
        $response = $this->http->request('POST', 'api/upload-gallery-images.php', [
            'form_params' => [
                'gallery' => '2134987c9v8b'
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
        $response = $this->http->request('POST', 'api/upload-gallery-images.php', [
            'form_params' => [
                'gallery' => 9999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id does not match any galleries", (string)$response->getBody());
    }

    public function testUploadNoImages() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-gallery-images.php', [
            'form_params' => [
                'gallery' => 2
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("File(s) are required", json_decode($response->getBody()));
    }

    public function testUploadSmallWidth() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-gallery-images.php', [
            'multipart' => [
                [
                    'name' => 'gallery',
                    'contents' => 2,
                ],
                [
                    'name' => 'myfile',
                    'contents' => fopen(dirname(__DIR__) . '/resources/flower-proof.jpeg', 'r'),
                    'filename' => 'flower.jpeg',
                    'headers' => ['Content-Type:' => 'image/png']
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image does not meet the minimum width requirements of 1140px. Image is 1000 x 750", json_decode($response->getBody()));
        $this->assertFalse(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/maternity/flower.jpeg'));
    }

    public function testUploadSmallHeight() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-gallery-images.php', [
            'multipart' => [
                [
                    'name' => 'gallery',
                    'contents' => 2,
                ],
                [
                    'name' => 'myfile',
                    'contents' => fopen(dirname(__DIR__) . '/resources/flower-short.jpeg', 'r'),
                    'filename' => 'flower.jpeg',
                    'headers' => ['Content-Type:' => 'image/png']
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image does not meet the minimum height requirements of 760px. Image is 1600 x 678", json_decode($response->getBody()));
        $this->assertFalse(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/maternity/flower.jpeg'));
    }

    public function testSingleFile() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-gallery-images.php', [
            'multipart' => [
                [
                    'name' => 'gallery',
                    'contents' => 2,
                ],
                [
                    'name' => 'myfile',
                    'contents' => fopen(dirname(__DIR__) . '/resources/flower.jpeg', 'r'),
                    'filename' => 'flower.jpeg',
                    'headers' => ['Content-Type:' => 'image/png']
                ]
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $x = json_decode($response->getBody());
        $this->assertEquals(['flower.jpeg'], json_decode($response->getBody(), true));
        $images = $this->sql->getRows("SELECT * FROM gallery_images WHERE gallery = 2 ORDER BY sequence DESC");
        $this->assertEquals(2, $images[0]['gallery']);
        $this->assertEquals('flower.jpeg', $images[0]['title']);
        $this->assertNotEquals(0, $images[0]['sequence']);
        $this->assertEquals('', $images[0]['caption']);
        $this->assertEquals('/portrait/img/maternity/flower.jpeg', $images[0]['location']);
        $this->assertEquals(1013, $images[0]['width']);
        $this->assertEquals(760, $images[0]['height']);
        $this->assertEquals(1, $images[0]['active']);
        $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/maternity/flower.jpeg'));
    }
}