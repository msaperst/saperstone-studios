<?php

namespace coverage\integration;

use Exception;
use Gallery;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class GalleryIntegrationTest extends TestCase {

    public function testNullGalleryId() {
        try {
            Gallery::withId(NULL);
        } catch (Exception $e) {
            $this->assertEquals("Gallery id is required", $e->getMessage());
        }
    }

    public function testBlankGalleryId() {
        try {
            Gallery::withId("");
        } catch (Exception $e) {
            $this->assertEquals("Gallery id can not be blank", $e->getMessage());
        }
    }

    public function testLetterGalleryId() {
        try {
            Gallery::withId("a");
        } catch (Exception $e) {
            $this->assertEquals("Gallery id does not match any galleries", $e->getMessage());
        }
    }

    public function testBadGalleryId() {
        try {
            Gallery::withId(8999);
        } catch (Exception $e) {
            $this->assertEquals("Gallery id does not match any galleries", $e->getMessage());
        }
    }

    public function testBadStringGalleryId() {
        try {
            Gallery::withId("8999");
        } catch (Exception $e) {
            $this->assertEquals("Gallery id does not match any galleries", $e->getMessage());
        }
    }

    public function testGetId() {
        $gallery = Gallery::withId('1');
        $this->assertEquals(1, $gallery->getId());
    }

    public function testAllDataLoadedNoParent() {
        $gallery = Gallery::withId(1);
        $galleryInfo = $gallery->getDataArray();
        $this->assertEquals(1, $galleryInfo['id']);
        $this->assertNull($galleryInfo['parent']);
        $this->assertNull($galleryInfo['image']);
        $this->assertEquals('Portrait', $galleryInfo['title']);
        $this->assertNull($galleryInfo['comment']);
    }

    public function testAllDataLoadedParent() {
        $gallery = Gallery::withId(2);
        $galleryInfo = $gallery->getDataArray();
        $this->assertEquals(2, $galleryInfo['id']);
        $this->assertEquals(1, $galleryInfo['parent']);
        $this->assertEquals('maternity.jpg', $galleryInfo['image']);
        $this->assertEquals('Maternity', $galleryInfo['title']);
        $this->assertNull($galleryInfo['comment']);
    }

    public function testGetParentNoParent() {
        $gallery = Gallery::withId(1);
        $this->assertNull($gallery->getParent());
    }

    public function testGetParentParent() {
        $gallery = Gallery::withId(2);
        $parent = $gallery->getParent()->getDataArray();
        $this->assertEquals(1, $parent['id']);
        $this->assertNull($parent['parent']);
        $this->assertNull($parent['image']);
        $this->assertEquals('Portrait', $parent['title']);
        $this->assertNull($parent['comment']);
    }

    public function testUpdateNull() {
        $gallery = Gallery::withId(1);
        $gallery->update(NULL);
        $galleryInfo = $gallery->getDataArray();
        $this->assertEquals(1, $galleryInfo['id']);
        $this->assertNull($galleryInfo['parent']);
        $this->assertNull($galleryInfo['image']);
        $this->assertEquals('Portrait', $galleryInfo['title']);
        $this->assertNull($galleryInfo['comment']);
    }

    public function testUpdateNothing() {
        $gallery = Gallery::withId(1);
        $gallery->update(['x' => 2]);
        $galleryInfo = $gallery->getDataArray();
        $this->assertEquals(1, $galleryInfo['id']);
        $this->assertNull($galleryInfo['parent']);
        $this->assertNull($galleryInfo['image']);
        $this->assertEquals('Portrait', $galleryInfo['title']);
        $this->assertNull($galleryInfo['comment']);
    }

    public function testUpdateTitle() {
        try {
            $gallery = Gallery::withId(1);
            $gallery->update(['title' => 'New Title']);
            $galleryInfo = $gallery->getDataArray();
            $this->assertEquals(1, $galleryInfo['id']);
            $this->assertNull($galleryInfo['parent']);
            $this->assertNull($galleryInfo['image']);
            $this->assertEquals('New Title', $galleryInfo['title']);
            $this->assertNull($galleryInfo['comment']);
        } finally {
            $sql = new Sql();
            $sql->executeStatement("UPDATE galleries SET title='Portrait' WHERE id='1';");
            $sql->disconnect();
        }
    }
}