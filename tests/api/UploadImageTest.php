<?php

namespace api;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UploadImageTest extends TestCase {
    private $http;

    public function setUp(): void {
        $this->http = new ApiTestClient(['base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/']);
    }

    public function tearDown(): void {
        $this->http = NULL;
        @unlink(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/main/tmp_portraits.jpg');
        @unlink(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/main/tmp_security-test.jpg');
        @unlink(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/tmp_security-test.jpg');
        @unlink(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/b-nai-mitzvah/tmp_details.jpg');
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/upload-image.php');
            self::fail('Expected authorization request to be rejected');
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
            self::fail('Expected authorization request to be rejected');
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
        $this->assertEquals(400, $response->getStatusCode());
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
        $this->assertEquals(400, $response->getStatusCode());
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
        $this->assertEquals(400, $response->getStatusCode());
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
        $this->assertEquals(400, $response->getStatusCode());
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
        $this->assertEquals(400, $response->getStatusCode());
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
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals("Image does not meet the minimum width requirements of 1200px. Image is 1000 x 750", (string)$response->getBody());
        $this->assertFalse(file_exists(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/main/tmp_portraits.jpg'));
    }

    public function testRejectsNonImageUpload(): void {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-image.php', [
            'multipart' => [
                [
                    'name' => 'location',
                    'contents' => '..//img/main/security-test.jpg',
                ],
                [
                    'name' => 'min-width',
                    'contents' => '400',
                ],
                [
                    'name' => 'myfile',
                    'contents' => 'not an image',
                    'filename' => 'not-image.jpg',
                    'headers' => ['Content-Type:' => 'image/jpeg']
                ]
            ],
            'cookies' => $cookieJar
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Uploaded file is not a valid image', (string)$response->getBody());
        $this->assertFalse(file_exists(
            dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/main/tmp_security-test.jpg'
        ));
    }

    public function testRejectsLocationTraversalOutsidePublicRoot(): void {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/upload-image.php', [
            'multipart' => [
                [
                    'name' => 'location',
                    'contents' => '../../content/security-test.jpg',
                ],
                [
                    'name' => 'min-width',
                    'contents' => '400',
                ],
                [
                    'name' => 'myfile',
                    'contents' => fopen(dirname(__DIR__) . '/resources/flower.jpeg', 'r'),
                    'filename' => 'flower.jpeg',
                    'headers' => ['Content-Type:' => 'image/jpeg']
                ]
            ],
            'cookies' => $cookieJar
        ]);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Image location is not valid', (string)$response->getBody());
        $this->assertFalse(file_exists(
            dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/tmp_security-test.jpg'
        ));
    }

    public function testSingleFileToPreviouslyEmptySiteSection(): void {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $destinationDirectory = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'content/b-nai-mitzvah';
        $this->assertTrue(is_dir($destinationDirectory));
        $this->assertTrue(
            is_writable($destinationDirectory),
            'Fresh local content directory must be writable by the application'
        );

        $response = $this->http->request('POST', 'api/upload-image.php', [
            'multipart' => [
                [
                    'name' => 'location',
                    'contents' => '../b-nai-mitzvah/img/details.jpg',
                ],
                [
                    'name' => 'min-width',
                    'contents' => '400',
                ],
                [
                    'name' => 'myfile',
                    'contents' => fopen(dirname(__DIR__) . '/resources/flower.jpeg', 'r'),
                    'filename' => 'flower.jpeg',
                    'headers' => ['Content-Type:' => 'image/jpeg']
                ]
            ],
            'cookies' => $cookieJar
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', (string)$response->getBody());

        $uploaded = $destinationDirectory . DIRECTORY_SEPARATOR . 'tmp_details.jpg';
        $this->assertTrue(file_exists($uploaded));
        $size = getimagesize($uploaded);
        $this->assertEquals(1600, $size[0]);
        $this->assertEquals(1200, $size[1]);
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
        $this->assertTrue(file_exists(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/main/tmp_portraits.jpg'));
        $size = getimagesize(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'content/main/tmp_portraits.jpg');
        $this->assertEquals(1600, $size[0]);
        $this->assertEquals(1200, $size[1]);
    }
}
