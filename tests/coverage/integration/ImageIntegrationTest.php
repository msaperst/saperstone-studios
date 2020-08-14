<?php

namespace coverage\integration;

use Album;
use Exception;
use Gallery;
use Image;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ImageIntegrationTest extends TestCase {

    private $sql;

    public function setUp() {
        date_default_timezone_set("America/New_York");
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4, '123');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (998, '999', '', '1', '', '/albums/sample/sample.jpg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (999, '999', '', '2', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (998, '2', '', '1', '', '/albums/sample/sample.jpg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (999, '2', '', '1', '', '', '300', '400', '1');");
        $oldmask = umask(0);
        mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums');
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums', 0777);
        mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample');
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample', 0777);
        mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/full');
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/full', 0777);
        touch(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/sample.jpg');
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/sample.jpg', 0777);
        touch(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/full/sample.jpg');
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/full/sample.jpg', 0777);
        umask($oldmask);
    }

    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 999;");
        $this->sql->executeStatement("DELETE FROM `gallery_images` WHERE `gallery_images`.`id` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
        system("rm -rf " . escapeshellarg(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums'));
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
        } catch (Exception $e) {
            $this->assertEquals("Image id can not be blank", $e->getMessage());
        }
    }

    public function testLetterImageSequence() {
        try {
            new Image(new Gallery(2), "a");
        } catch (Exception $e) {
            $this->assertEquals("Image id does not match any images", $e->getMessage());
        }
    }

    public function testBadImageSequence() {
        try {
            new Image(new Gallery(2), 9999);
        } catch (Exception $e) {
            $this->assertEquals("Image id does not match any images", $e->getMessage());
        }
    }

    public function testBadStringImageSequence() {
        try {
            new Image(new Gallery(2), "9999");
        } catch (Exception $e) {
            $this->assertEquals("Image id does not match any images", $e->getMessage());
        }
    }

    public function testNullContainer() {
        try {
            new Image(Null, 1);
        } catch (Exception $e) {
            $this->assertEquals("Parent (album or gallery) is required", $e->getMessage());
        }
    }

    public function testStringContainer() {
        try {
            new Image('hi', 1);
        } catch (Exception $e) {
            $this->assertEquals("Parent (album or gallery) is required", $e->getMessage());
        }
    }

    public function testArrayContainer() {
        try {
            new Image(array(), 1);
        } catch (Exception $e) {
            $this->assertEquals("Parent (album or gallery) is required", $e->getMessage());
        }
    }

    public function testGetId() {
        $image = new Image(new Album(999), '1');
        $this->assertEquals(998, $image->getId());
    }

    public function testCanUserGetDataAlbumNobody() {
        $image = new Image(new Album(999), 1);
        $this->assertFalse($image->canUserGetData());
    }

    public function testCanUserGetDataAlbumAdmin() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $image = new Image(new Album(999), 1);
        $this->assertTrue($image->canUserGetData());
        unset($_SESSION['hash']);
    }

    public function testCanUserGetDataAlbumOwner() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $image = new Image(new Album(999), 1);
        $this->assertTrue($image->canUserGetData());
        unset($_SESSION['hash']);
    }

    public function testCanUserGetDataAlbumOtherUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $image = new Image(new Album(999), 1);
        $this->assertFalse($image->canUserGetData());
        unset($_SESSION['hash']);
    }

    public function testCanUserGetDataGalleryNobody() {
        $image = new Image(new Gallery(2), 999);
        $this->assertFalse($image->canUserGetData());
    }

    public function testCanUserGetDataGalleryAdmin() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $image = new Image(new Gallery(2), 999);
        $this->assertTrue($image->canUserGetData());
        unset($_SESSION['hash']);
    }

    public function testCanUserGetDataGalleryOtherUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $image = new Image(new Gallery(2), 999);
        $this->assertFalse($image->canUserGetData());
        unset($_SESSION['hash']);
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

    public function testDeleteNoAccess() {
        $image = new Image(new Album(999), 2);
        try {
            $image->delete();
        } catch (Exception $e) {
            $this->assertEquals("User not authorized to delete image", $e->getMessage());
        }
        $this->assertEquals(2, $this->sql->getRowCount("SELECT * FROM `album_images` WHERE `album_images`.`album` = 999;"));
        $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/sample.jpg'));
    }

    public function testDeleteNoLocation() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $image = new Image(new Album(999), 2);
        $image->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `album_images` WHERE `album_images`.`album` = 999;"));
    }

    public function testDeleteAlbum() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $image = new Image(new Album(999), 1);
        $image->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `album_images` WHERE `album_images`.`album` = 999;"));
        $this->assertEquals(0, $this->sql->getRow("SELECT * FROM `album_images` WHERE `album_images`.`album` = 999;")['sequence']);
        $this->assertFalse(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/sample.jpg'));
        $this->assertFalse(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/full/sample.jpg'));
    }

    public function testDeleteGallery() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $image = new Image(new Gallery(2), 998);
        $image->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `gallery_images` WHERE `gallery_images`.`gallery` = 2;"));
        $this->assertEquals(0, $this->sql->getRow("SELECT * FROM `gallery_images` WHERE `gallery_images`.`gallery` = 2;")['sequence']);
        $this->assertFalse(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/sample.jpg'));
    }
}