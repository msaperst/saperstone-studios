<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Sql;
use SqlException;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UpdateGalleryImageTest extends TestCase {
    /**
     * @var Client
     */
    private $http;
    /**
     * @var Sql
     */
    private $sql;

    /**
     * @throws SqlException
     */
    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `galleries` (`id`, `parent`, `image`, `title`, `comment`) VALUES ('999', '1', 'sample.jpg', 'Sample', NULL);");
        $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (998, '999', '', '0', '', '/portrait/img/sample/sample1.jpg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (999, '999', '', '1', '', 'img/sample/sample2.jpg', '300', '400', '1');");
        $oldMask = umask(0);
        mkdir(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/sample');
        chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/sample', 0777);
        touch(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/sample/sample1.jpg');
        chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/sample/sample1.jpg', 0777);
        touch(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/sample/sample2.jpg');
        chmod(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/sample/sample2.jpg', 0777);
        umask($oldMask);
    }

    /**
     * @throws SqlException
     */
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
        system("rm -rf " . escapeshellarg(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/x.jpg'));
        system("rm -rf " . escapeshellarg(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/x 5 &.jpg'));
        $this->sql->disconnect();
    }

    /**
     * @throws GuzzleException
     */
    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/update-gallery-image.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        try {
            $this->http->request('POST', 'api/update-gallery-image.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testNoGallery() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-image.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankGallery() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-image.php', [
            'form_params' => [
                'gallery' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testLetterGallery() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-image.php', [
            'form_params' => [
                'gallery' => '546fchgj78'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id does not match any galleries", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBadGalleryId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-image.php', [
            'form_params' => [
                'gallery' => 9999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id does not match any galleries", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-image.php', [
            'form_params' => [
                'gallery' => 999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image id is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-image.php', [
            'form_params' => [
                'gallery' => 999,
                'image' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image id can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBadImage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-image.php', [
            'form_params' => [
                'gallery' => 999,
                'image' => '1234'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Image id does not match any images", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoTitle() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-image.php', [
            'form_params' => [
                'gallery' => 999,
                'image' => 998
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Title is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankTitle() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-image.php', [
            'form_params' => [
                'gallery' => 999,
                'image' => 998,
                'title' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Title can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoFilename() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-image.php', [
            'form_params' => [
                'gallery' => 999,
                'image' => 998,
                'title' => 'sample'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Filename is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankFilename() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-image.php', [
            'form_params' => [
                'gallery' => 999,
                'image' => 998,
                'title' => 'sample',
                'filename' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Filename can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testUpdateSimple() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-image.php', [
            'form_params' => [
                'gallery' => 999,
                'image' => 998,
                'title' => 'sample',
                'filename' => '/portrait/img/x.jpg'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $image = $this->sql->getRow("SELECT * FROM `gallery_images` WHERE `gallery_images`.`id` = 998;");
        $this->assertEquals(998, $image['id']);
        $this->assertEquals(999, $image['gallery']);
        $this->assertEquals('sample', $image['title']);
        $this->assertEquals(0, $image['sequence']);
        $this->assertEquals('', $image['caption']);
        $this->assertEquals('/portrait/img/x.jpg', $image['location']);
        $this->assertEquals(300, $image['width']);
        $this->assertEquals(400, $image['height']);
        $this->assertEquals(1, $image['active']);
        $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/x.jpg'));
    }

    /**
     * @throws GuzzleException
     */
    public function testUpdateComplex() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-image.php', [
            'form_params' => [
                'gallery' => 999,
                'image' => 998,
                'title' => '"\'123$!?',
                'caption' => 'I like dirt',
                'filename' => '/portrait/img/x 5 &.jpg'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $image = $this->sql->getRow("SELECT * FROM `gallery_images` WHERE `gallery_images`.`id` = 998;");
        $this->assertEquals(998, $image['id']);
        $this->assertEquals(999, $image['gallery']);
        $this->assertEquals('"\'123$!?', $image['title']);
        $this->assertEquals(0, $image['sequence']);
        $this->assertEquals('I like dirt', $image['caption']);
        $this->assertEquals('/portrait/img/x 5 &.jpg', $image['location']);
        $this->assertEquals(300, $image['width']);
        $this->assertEquals(400, $image['height']);
        $this->assertEquals(1, $image['active']);
        $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/x 5 &.jpg'));
    }

    /**
     * @throws GuzzleException
     */
    public function testUpdateNoFile() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/update-gallery-image.php', [
            'form_params' => [
                'gallery' => 999,
                'image' => 999,
                'title' => 'sample',
                'filename' => 'img/x.jpg'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Unable to find original image to rename!", (string)$response->getBody());
        $image = $this->sql->getRow("SELECT * FROM `gallery_images` WHERE `gallery_images`.`id` = 999;");
        $this->assertEquals(999, $image['id']);
        $this->assertEquals(999, $image['gallery']);
        $this->assertEquals('sample', $image['title']);
        $this->assertEquals(1, $image['sequence']);
        $this->assertEquals('', $image['caption']);
        $this->assertEquals('img/sample/sample2.jpg', $image['location']);
        $this->assertEquals(300, $image['width']);
        $this->assertEquals(400, $image['height']);
        $this->assertEquals(1, $image['active']);
        $this->assertTrue(file_exists(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'content/portrait/sample/sample2.jpg'));
    }
}