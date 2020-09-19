<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UploadBlogImagesTest extends TestCase {
    private $http;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
    }

    public function tearDown() {
        $this->http = NULL;
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/upload-blog-images.php');
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
            $this->http->request('POST', 'api/upload-blog-images.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testUploadNoImages() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-blog-images.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("File(s) are required", json_decode($response->getBody()));
    }

    public function testUploadSmallWidth() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-blog-images.php', [
            'multipart' => [
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
        $this->assertEquals("Image does not meet the minimum width requirements of 1200px. Image is 1000 x 750", json_decode($response->getBody()));
        //TODO - unable to verify image not present
//        $this->assertFalse(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tmp/flower.jpeg'));
    }

    public function testSingleFile() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-blog-images.php', [
            'multipart' => [
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
        $this->assertEquals(['flower.jpeg'], json_decode($response->getBody(), true));
        //TODO - unable to verify image present
//        $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tmp/flower.jpeg'));
//        $size = getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tmp/flower.jpeg');
//        $this->assertEquals(1013, $size[0]);
//        $this->assertEquals(760, $size[1]);
    }
}