<?php

namespace coverage\integration;

use Exception;
use Gallery;
use PHPUnit\Framework\TestCase;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class GalleryIntegrationTest extends TestCase {

    public function testNullGalleryId() {
        try {
            new Gallery(NULL);
        } catch (Exception $e) {
            $this->assertEquals("Gallery id is required", $e->getMessage());
        }
    }

    public function testBlankGalleryId() {
        try {
            new Gallery("");
        } catch ( Exception $e) {
            $this->assertEquals("Gallery id can not be blank", $e->getMessage());
        }
    }

    public function testLetterGalleryId() {
        try {
            new Gallery("a");
        } catch ( Exception $e) {
            $this->assertEquals("Gallery id does not match any galleries", $e->getMessage());
        }
    }

    public function testBadGalleryId() {
        try {
            new Gallery(9999);
        } catch ( Exception $e) {
            $this->assertEquals("Gallery id does not match any galleries", $e->getMessage());
        }
    }

    public function testBadStringGalleryId() {
        try {
            new Gallery("9999");
        } catch ( Exception $e) {
            $this->assertEquals("Gallery id does not match any galleries", $e->getMessage());
        }
    }

    public function testGetId() {
        $gallery = new Gallery('1');
        $this->assertEquals(1, $gallery->getId());
    }

    public function testAllDataLoadedNoParent() {
        $gallery = new Gallery(1);
        $galleryInfo = $gallery->getDataArray();
        $this->assertEquals(1, $galleryInfo['id']);
        $this->assertNull($galleryInfo['parent']);
        $this->assertNull($galleryInfo['image']);
        $this->assertEquals('Portrait', $galleryInfo['title']);
        $this->assertNull($galleryInfo['comment']);
    }

    public function testAllDataLoadedParent() {
        $gallery = new Gallery(2);
        $galleryInfo = $gallery->getDataArray();
        $this->assertEquals(2, $galleryInfo['id']);
        $this->assertEquals(1, $galleryInfo['parent']);
        $this->assertEquals('maternity.jpg', $galleryInfo['image']);
        $this->assertEquals('Maternity', $galleryInfo['title']);
        $this->assertNull($galleryInfo['comment']);
    }

    public function testGetParentNoParent() {
        $gallery = new Gallery(1);
        $this->assertNull($gallery->getParent());
    }

    public function testGetParentParent() {
        $gallery = new Gallery(2);
        $parent = $gallery->getParent()->getDataArray();
        $this->assertEquals(1, $parent['id']);
        $this->assertNull($parent['parent']);
        $this->assertNull($parent['image']);
        $this->assertEquals('Portrait', $parent['title']);
        $this->assertNull($parent['comment']);
    }
}