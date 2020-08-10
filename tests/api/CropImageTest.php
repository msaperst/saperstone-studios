<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class CropImageTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/crop-image.php');
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
            $this->http->request('POST', 'api/crop-image.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/crop-image.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image is required", (string)$response->getBody());
    }

    public function testBlankImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/crop-image.php', [
            'form_params' => [
                'image' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image can not be blank", (string)$response->getBody());
    }

    public function testBadImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/crop-image.php', [
            'form_params' => [
                'image' => 'image.jpg'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image does not exist", (string)$response->getBody());
    }

    public function testNoMaxWidth() {
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/tmp_flower.jpeg');
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/crop-image.php', [
            'form_params' => [
                'image' => '../blog/posts/tmp_flower.jpeg'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image max-width is required", (string)$response->getBody());
    }

    public function testBlankMaxWidth() {
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/tmp_flower.jpeg');
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/crop-image.php', [
            'form_params' => [
                'image' => '../blog/posts/tmp_flower.jpeg',
                'max-width' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image max-width can not be blank", (string)$response->getBody());
    }

    public function testNoTop() {
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/tmp_flower.jpeg');
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/crop-image.php', [
            'form_params' => [
                'image' => '../blog/posts/tmp_flower.jpeg',
                'max-width' => '300px'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image top is required", (string)$response->getBody());
    }

    public function testBlankTop() {
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/tmp_flower.jpeg');
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/crop-image.php', [
            'form_params' => [
                'image' => '../blog/posts/tmp_flower.jpeg',
                'max-width' => '300px',
                'top' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image top can not be blank", (string)$response->getBody());
    }

    public function testNoBottom() {
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/tmp_flower.jpeg');
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/crop-image.php', [
            'form_params' => [
                'image' => '../blog/posts/tmp_flower.jpeg',
                'max-width' => '300px',
                'top' => '10'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image bottom is required", (string)$response->getBody());
    }

    public function testBlankBottom() {
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/tmp_flower.jpeg');
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/crop-image.php', [
            'form_params' => [
                'image' => '../blog/posts/tmp_flower.jpeg',
                'max-width' => '300px',
                'top' => '10',
                'bottom' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image bottom can not be blank", (string)$response->getBody());
    }

    public function testCroppedImageTooSmall() {
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/tmp_flower.jpeg');
        chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/tmp_flower.jpeg', 0777);
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/crop-image.php', [
            'form_params' => [
                'image' => '../blog/posts/tmp_flower.jpeg',
                'max-width' => '100px',
                'top' => '-10',
                'bottom' => '140'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Cropped image is smaller than the required image', (string)$response->getBody());
        $this->assertFalse(file_exists('content/blog/tmp_flower.jpeg'));
    }

    public function testProperlyResizedImage() {
        try {
            copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/tmp_flower.jpeg');
            chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/tmp_flower.jpeg', 0777);
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/crop-image.php', [
                'form_params' => [
                    'image' => '../blog/posts/tmp_flower.jpeg',
                    'max-width' => '300px',
                    'top' => '10',
                    'bottom' => '110'
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals('', (string)$response->getBody());
            $size = getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/flower.jpeg');
            $this->assertEquals(300, $size[0]);
            $this->assertEquals(100, $size[1]);
        } finally {
            unlink(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/flower.jpeg');
        }
    }

    public function testTopLessThanZero() {
        try {
            copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/tmp_flower.jpeg');
            chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/tmp_flower.jpeg', 0777);
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/crop-image.php', [
                'form_params' => [
                    'image' => '../blog/posts/tmp_flower.jpeg',
                    'max-width' => '300px',
                    'top' => '-10',
                    'bottom' => '140'
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals('', (string)$response->getBody());
            $size = getimagesize(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/flower.jpeg');
            $this->assertEquals(300, $size[0]);
            $this->assertEquals(150, $size[1]);
        } finally {
            unlink(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/blog/flower.jpeg');
        }
    }
}

?>