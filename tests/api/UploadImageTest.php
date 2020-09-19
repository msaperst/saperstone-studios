<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UploadImageTest extends TestCase {
    private $http;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
    }

    public function tearDown() {
        $this->http = NULL;
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/upload-image.php');
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
            $this->http->request('POST', 'api/upload-image.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoLocation() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-image.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image location is required", (string)$response->getBody());
    }

    public function testBlankLocation() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-image.php', [
            'form_params' => [
                'location' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image location can not be blank", (string)$response->getBody());
    }

    public function testNoWidth() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-image.php', [
            'form_params' => [
                'location' => 'maternity'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image minimum width is required", (string)$response->getBody());
    }

    public function testBlankWidth() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-image.php', [
            'form_params' => [
                'location' => 'maternity',
                'min-width' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image minimum width can not be blank", (string)$response->getBody());
    }

    public function testUploadNoImages() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-image.php', [
            'form_params' => [
                'location' => 'maternity',
                'min-width' => '300'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("File upload location is required", (string)$response->getBody());
    }

    public function testUploadSmallWidth() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-image.php', [
            'multipart' => [
                [
                    'name' => 'location',
                    'contents' => '..//img/main/portraits.jpg',
                ],
                [
                    'name' => 'min-width',
                    'contents' => '1200',
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
        $this->assertEquals("Image does not meet the minimum width requirements of 1200px. Image is 1000 x 750", (string)$response->getBody());
        $this->assertFalse(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/main/tmp_portraits.jpg'));
    }

    public function testSingleFile() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-image.php', [
            'multipart' => [
                [
                    'name' => 'location',
                    'contents' => '..//img/main/portraits.jpg',
                ],
                [
                    'name' => 'min-width',
                    'contents' => '400',
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
        $this->assertEquals('', (string)$response->getBody());
        $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/main/tmp_portraits.jpg'));
        $size = getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/main/tmp_portraits.jpg');
        $this->assertEquals(1600, $size[0]);
        $this->assertEquals(1200, $size[1]);
    }
}