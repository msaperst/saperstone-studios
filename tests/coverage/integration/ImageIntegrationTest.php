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
    /**
     * @var Sql
     */
    private $sql;

    /**
     * @throws Exception
     */
    public function setUp() {
        date_default_timezone_set("America/New_York");
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('899', 'sample-album', 'sample album for testing', 'sample', 4, '123');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (898, '899', '', '1', '', '/albums/sample/sample.jpg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (899, '899', 'sample', '2', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (898, '1', '', '1', '', '/albums/sample/sample.jpg', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (899, '1', '', '1', '', '', '300', '400', '1');");
        $oldMask = umask(0);
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
        umask($oldMask);
    }

    /**
     * @throws Exception
     */
    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 899;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`id` = 898;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`id` = 899;");
        $this->sql->executeStatement("DELETE FROM `gallery_images` WHERE `gallery_images`.`id` = 898;");
        $this->sql->executeStatement("DELETE FROM `gallery_images` WHERE `gallery_images`.`id` = 899;");
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
            new Image(Gallery::withId(2), NULL);
        } catch (Exception $e) {
            $this->assertEquals("Image id is required", $e->getMessage());
        }
    }

    public function testBlankImageSequence() {
        try {
            new Image(Gallery::withId(2), "");
        } catch (Exception $e) {
            $this->assertEquals("Image id can not be blank", $e->getMessage());
        }
    }

    public function testAlbumLetterImageSequence() {
        try {
            new Image(Album::withId(899), "a");
        } catch (Exception $e) {
            $this->assertEquals("Image id does not match any images", $e->getMessage());
        }
    }

    public function testGalleryLetterImageSequence() {
        try {
            new Image(Gallery::withId(2), "a");
        } catch (Exception $e) {
            $this->assertEquals("Image id does not match any images", $e->getMessage());
        }
    }

    public function testBadImageSequence() {
        try {
            new Image(Gallery::withId(2), 8999);
        } catch (Exception $e) {
            $this->assertEquals("Image id does not match any images", $e->getMessage());
        }
    }

    public function testBadStringImageSequence() {
        try {
            new Image(Gallery::withId(2), "8999");
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

    /**
     * @throws Exception
     */
    public function testGetId() {
        $image = new Image(Album::withId(899), '1');
        $this->assertEquals(898, $image->getId());
    }

    /**
     * @throws Exception
     */
    public function testGetTitle() {
        $image = new Image(Album::withId(899), '2');
        $this->assertEquals('sample', $image->getTitle());
    }

    /**
     * @throws Exception
     */
    public function testGetLocation() {
        $image = new Image(Album::withId(899), '1');
        $this->assertEquals('/albums/sample/sample.jpg', $image->getLocation());
    }

    /**
     * @throws Exception
     */
    public function testCanUserGetDataAlbumNobody() {
        $image = new Image(Album::withId(899), 1);
        $this->assertFalse($image->canUserGetData());
    }

    /**
     * @throws Exception
     */
    public function testCanUserGetDataAlbumAdmin() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $image = new Image(Album::withId(899), 1);
        $this->assertTrue($image->canUserGetData());
        unset($_SESSION['hash']);
    }

    /**
     * @throws Exception
     */
    public function testCanUserGetDataAlbumOwner() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $image = new Image(Album::withId(899), 1);
        $this->assertTrue($image->canUserGetData());
        unset($_SESSION['hash']);
    }

    /**
     * @throws Exception
     */
    public function testCanUserGetDataAlbumOtherUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $image = new Image(Album::withId(899), 1);
        $this->assertFalse($image->canUserGetData());
        unset($_SESSION['hash']);
    }

    /**
     * @throws Exception
     */
    public function testCanUserGetDataGalleryNobody() {
        $image = new Image(Gallery::withId(1), 899);
        $this->assertFalse($image->canUserGetData());
    }

    /**
     * @throws Exception
     */
    public function testCanUserGetDataGalleryAdmin() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $image = new Image(Gallery::withId(1), 899);
        $this->assertTrue($image->canUserGetData());
        unset($_SESSION['hash']);
    }

    /**
     * @throws Exception
     */
    public function testCanUserGetDataGalleryOtherUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $image = new Image(Gallery::withId(1), 899);
        $this->assertFalse($image->canUserGetData());
        unset($_SESSION['hash']);
    }

    /**
     * @throws Exception
     */
    public function testAllDataLoaded() {
        $image = new Image(Album::withId(899), 2);
        $imageInfo = $image->getDataArray();
        $this->assertEquals(899, $imageInfo['id']);
        $this->assertFalse(isset($imageInfo['gallery']));
        $this->assertEquals(899, $imageInfo['album']);
        $this->assertEquals('sample', $imageInfo['title']);
        $this->assertEquals(2, $imageInfo['sequence']);
        $this->assertEquals('', $imageInfo['caption']);
        $this->assertEquals('', $imageInfo['location']);
        $this->assertEquals('300', $imageInfo['width']);
        $this->assertEquals(400, $imageInfo['height']);
        $this->assertEquals(1, $imageInfo['active']);
    }

    /**
     * @throws Exception
     */
    public function testDeleteNoAccess() {
        $image = new Image(Album::withId(899), 2);
        try {
            $image->delete();
        } catch (Exception $e) {
            $this->assertEquals("User not authorized to delete image", $e->getMessage());
        }
        $this->assertEquals(2, $this->sql->getRowCount("SELECT * FROM `album_images` WHERE `album_images`.`album` = 899;"));
        $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/sample.jpg'));
    }

    /**
     * @throws Exception
     */
    public function testDeleteNoLocation() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $image = new Image(Album::withId(899), 2);
        $image->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `album_images` WHERE `album_images`.`album` = 899;"));
    }

    /**
     * @throws Exception
     */
    public function testDeleteAlbum() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $image = new Image(Album::withId(899), 1);
        $image->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `album_images` WHERE `album_images`.`album` = 899;"));
        $this->assertEquals(0, $this->sql->getRow("SELECT * FROM `album_images` WHERE `album_images`.`album` = 899;")['sequence']);
        $this->assertFalse(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/sample.jpg'));
        $this->assertFalse(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/full/sample.jpg'));
    }

    /**
     * @throws Exception
     */
    public function testDeleteGallery() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $image = new Image(Gallery::withId(1), 898);
        $currentImages = $this->sql->getRowCount("SELECT * FROM `gallery_images` WHERE `gallery_images`.`gallery` = 1;");
        $image->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals($currentImages - 1, $this->sql->getRowCount("SELECT * FROM `gallery_images` WHERE `gallery_images`.`gallery` = 1;"));
        $this->assertEquals(0, $this->sql->getRow("SELECT * FROM `gallery_images` WHERE `gallery_images`.`gallery` = 1;")['sequence']);
        $this->assertFalse(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/albums/sample/sample.jpg'));
    }
}