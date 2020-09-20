<?php

namespace api;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class GetGalleryImagesTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT IGNORE INTO `galleries` VALUES (999, NULL, '', 'sample gallery', NULL);");
        $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (996, '998', '', '1', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (997, '999', '', '1', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (998, '999', '', '2', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (999, '999', '', '3', '', '', '300', '400', '1');");

    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `galleries` WHERE `galleries`.`id` = 998;");
        $this->sql->executeStatement("DELETE FROM `galleries` WHERE `galleries`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `gallery_images` WHERE `gallery_images`.`gallery` = 998;");
        $this->sql->executeStatement("DELETE FROM `gallery_images` WHERE `gallery_images`.`gallery` = 999;");
        $this->sql->disconnect();
    }

    public function testNoGalleryId() {
        $response = $this->http->request('GET', 'api/get-gallery-images.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id is required", (string)$response->getBody());
    }

    public function testBlankGalleryId() {
        $response = $this->http->request('GET', 'api/get-gallery-images.php', [
            'query' => [
                'gallery' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id can not be blank", (string)$response->getBody());
    }

    public function testLetterGalleryId() {
        $response = $this->http->request('GET', 'api/get-gallery-images.php', [
            'query' => [
                'gallery' => '546fchgj78'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id does not match any galleries", (string)$response->getBody());
    }

    public function testBadGalleryId() {
        $response = $this->http->request('GET', 'api/get-gallery-images.php', [
            'query' => [
                'gallery' => 9999
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Gallery id does not match any galleries", (string)$response->getBody());
    }

    public function testGalleryAll() {
        $response = $this->http->request('GET', 'api/get-gallery-images.php', [
            'query' => [
                'gallery' => 999
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $galleryImages = json_decode($response->getBody(), true);
        $this->assertEquals(3, sizeOf($galleryImages));
        $this->assertEquals(997, $galleryImages[0]['id']);
        $this->assertEquals('', $galleryImages[0]['title']);
        $this->assertEquals(1, $galleryImages[0]['sequence']);
        $this->assertEquals('', $galleryImages[0]['location']);
        $this->assertEquals(300, $galleryImages[0]['width']);
        $this->assertEquals(400, $galleryImages[0]['height']);
        $this->assertEquals(998, $galleryImages[1]['id']);
        $this->assertEquals('', $galleryImages[1]['title']);
        $this->assertEquals(2, $galleryImages[1]['sequence']);
        $this->assertEquals('', $galleryImages[1]['location']);
        $this->assertEquals(300, $galleryImages[1]['width']);
        $this->assertEquals(400, $galleryImages[1]['height']);
        $this->assertEquals(999, $galleryImages[2]['id']);
        $this->assertEquals('', $galleryImages[2]['title']);
        $this->assertEquals(3, $galleryImages[2]['sequence']);
        $this->assertEquals('', $galleryImages[2]['location']);
        $this->assertEquals(300, $galleryImages[2]['width']);
        $this->assertEquals(400, $galleryImages[2]['height']);
    }

    public function testGallerySecond() {
        $response = $this->http->request('GET', 'api/get-gallery-images.php', [
            'query' => [
                'gallery' => 999,
                'start' => 1,
                'howMany' => 1
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $galleryImages = json_decode($response->getBody(), true);
        $this->assertEquals(1, sizeof($galleryImages));
        $this->assertEquals(998, $galleryImages[0]['id']);
        $this->assertEquals('', $galleryImages[0]['title']);
        $this->assertEquals(2, $galleryImages[0]['sequence']);
        $this->assertEquals('', $galleryImages[0]['location']);
        $this->assertEquals(300, $galleryImages[0]['width']);
        $this->assertEquals(400, $galleryImages[0]['height']);
    }

    public function testGalleryNone() {
        $response = $this->http->request('GET', 'api/get-gallery-images.php', [
            'query' => [
                'gallery' => 999,
                'start' => 99
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(), json_decode($response->getBody(), true));
    }
}

?>