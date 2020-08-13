<?php

namespace coverage\integration;

use Album;
use Exception;
use Gallery;
use Image;
use phpDocumentor\Reflection\Types\Null_;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ImageIntegrationTest extends TestCase {

    private $sql;

    public function setUp() {
        date_default_timezone_set("America/New_York");
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4, '123');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (998, '999', '', '1', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (999, '999', '', '2', '', '', '300', '400', '1');");
    }

    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testNullImageSequence() {
        try {
            new Image(new Gallery(2), NULL);
        } catch (Exception $e) {
            $this->assertEquals("Image id is required", $e->getMessage());
        }
    }

    public function testBlankImageSequence() {
        try {
            new Image(new Gallery(2), "");
        } catch ( Exception $e) {
            $this->assertEquals("Image id can not be blank", $e->getMessage());
        }
    }

    public function testLetterImageSequence() {
        try {
            new Image(new Gallery(2), "a");
        } catch ( Exception $e) {
            $this->assertEquals("Image id does not match any images", $e->getMessage());
        }
    }

    public function testBadImageSequence() {
        try {
            new Image(new Gallery(2), 9999);
        } catch ( Exception $e) {
            $this->assertEquals("Image id does not match any images", $e->getMessage());
        }
    }

    public function testBadStringImageSequence() {
        try {
            new Image(new Gallery(2), "9999");
        } catch ( Exception $e) {
            $this->assertEquals("Image id does not match any images", $e->getMessage());
        }
    }

    public function testNullContainer() {
        try {
            new Image(Null, 1);
        } catch ( Exception $e) {
            $this->assertEquals("Parent (album or gallery) is required", $e->getMessage());
        }
    }

    public function testStringContainer() {
        try {
            new Image('hi', 1);
        } catch ( Exception $e) {
            $this->assertEquals("Parent (album or gallery) is required", $e->getMessage());
        }
    }

    public function testArrayContainer() {
        try {
            new Image(array(), 1);
        } catch ( Exception $e) {
            $this->assertEquals("Parent (album or gallery) is required", $e->getMessage());
        }
    }

    public function testGetId() {
        $image = new Image(new Album(999), '1');
        $this->assertEquals(998,  $image->getId());
    }

    public function testGetSequence() {
        $image = new Image(new Album(999), '1');
        $this->assertEquals(1, $image->getSequence());
    }

    public function testAllDataLoaded() {
        $image = new Image(new Album(999), 2);
        $imageInfo = $image->getDataArray();
        $this->assertEquals(999, $imageInfo['id']);
        $this->assertFalse(isset($imageInfo['gallery']));
        $this->assertEquals(999, $imageInfo['album']);
        $this->assertEquals('', $imageInfo['title']);
        $this->assertEquals(2, $imageInfo['sequence']);
        $this->assertEquals('', $imageInfo['caption']);
        $this->assertEquals('', $imageInfo['location']);
        $this->assertEquals('300', $imageInfo['width']);
        $this->assertEquals(400, $imageInfo['height']);
        $this->assertEquals(1, $imageInfo['active']);
    }
}