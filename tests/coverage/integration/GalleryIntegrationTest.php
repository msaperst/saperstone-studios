<?php

namespace coverage\integration;

use Exception;
use Gallery;
use PHPUnit\Framework\TestCase;
use Sql;
use TypeError;

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
            Gallery::withId("546fchgj78");
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

    public function testWithParams() {
        try {
            Gallery::withParams(NULL);
        } catch (Exception $e) {
            $this->assertEquals("Not yet implemented", $e->getMessage());
        }
    }

    public function testGetId() {
        $gallery = Gallery::withId('1');
        $this->assertEquals(1, $gallery->getId());
    }

    public function testGetTitle() {
        $gallery = Gallery::withId('1');
        $this->assertEquals('Portrait', $gallery->getTitle());
    }

    public function testGetCommentEmpty() {
        $gallery = Gallery::withId('1');
        $this->assertEquals('', $gallery->getComment());
    }

    public function testGetComment() {
        $gallery = Gallery::withId('29');
        $this->assertEquals('Hate making sure your prints are evenly spaced once hung on the wall?  Then this is the art product for you.  Each story grid comes with a paper template to hang on the wall.  Your template indicates where to place metal pegs which then make up a grid system on your wall.  Simply slip the prints onto the metal pegs and voila! Evenly spaced prints! These collages range in overall sizes from 2\' x 3\' all the way up to 4.5\' x 2\' or beyond and are totally customizable. Images are printed on either metal or a lustre photographic paper, your choice.', $gallery->getComment());
    }

    public function testGetImageEmpty() {
        $gallery = Gallery::withId('1');
        $this->assertEquals('', $gallery->getImage());
    }

    public function testGetImage() {
        $gallery = Gallery::withId('2');
        $this->assertEquals('maternity.jpg', $gallery->getImage());
    }

    public function testGetNav() {
        $gallery = Gallery::withId('1');
        $this->assertEquals('portrait', $gallery->getNav());
        $gallery = Gallery::withId('3');
        $this->assertEquals('portrait', $gallery->getNav());
        $gallery = Gallery::withId('15');
        $this->assertEquals('portrait', $gallery->getNav());
        $gallery = Gallery::withId('8');
        $this->assertEquals('wedding', $gallery->getNav());
        $gallery = Gallery::withId('10');
        $this->assertEquals('wedding', $gallery->getNav());
        $gallery = Gallery::withId('25');
        $this->assertEquals('wedding', $gallery->getNav());
        $gallery = Gallery::withId('28');
        $this->assertEquals('portrait', $gallery->getNav());
        $gallery = Gallery::withId('38');
        $this->assertEquals('wedding', $gallery->getNav());
        $gallery = Gallery::withId('40');
        $this->assertEquals('wedding', $gallery->getNav());
        $gallery = Gallery::withId('46');
        $this->assertEquals('wedding', $gallery->getNav());
        $gallery = Gallery::withId('52');
        $this->assertEquals('commercial', $gallery->getNav());
        $gallery = Gallery::withId('56');
        $this->assertEquals('commercial', $gallery->getNav());
        $gallery = Gallery::withId('71');
        $this->assertEquals('commercial', $gallery->getNav());
    }

    public function testGetBreadcrumbsBasicPortrait() {
        $gallery = Gallery::withId('1');
        $breadcrumbs = [
            0 => [
                'title' => 'Portrait',
                'link' => 'index.php'
            ],
            1 => [
                'title' => 'Gallery',
                'link' => ''
            ]
        ];
        $this->assertEquals($breadcrumbs, $gallery->getBreadcrumbs());
    }

    public function testGetBreadcrumbsSinglePortrait() {
        $gallery = Gallery::withId('3');
        $breadcrumbs = [
            0 => [
                'title' => 'Portrait',
                'link' => 'index.php'
            ],
            1 => [
                'title' => 'Gallery',
                'link' => 'gallery.php?w=1'
            ],
            2 => [
                'title' => 'Newborn',
                'link' => ''
            ]
        ];
        $this->assertEquals($breadcrumbs, $gallery->getBreadcrumbs());

    }

    public function testGetBreadcrumbsDoublePortrait() {
        $gallery = Gallery::withId('15');
        $breadcrumbs = [
            0 => [
                'title' => 'Portrait',
                'link' => 'index.php'
            ],
            1 => [
                'title' => 'Gallery',
                'link' => 'gallery.php?w=1'
            ],
            2 => [
                'title' => 'Newborn',
                'link' => 'gallery.php?w=3'
            ],
            3 => [
                'title' => 'Studio',
                'link' => ''
            ]
        ];
        $this->assertEquals($breadcrumbs, $gallery->getBreadcrumbs());
    }

    public function testGetBreadcrumbsBasicWedding() {
        $gallery = Gallery::withId('8');
        $breadcrumbs = [
            0 => [
                'title' => 'Wedding',
                'link' => 'index.php'
            ],
            1 => [
                'title' => 'Gallery',
                'link' => ''
            ]
        ];
        $this->assertEquals($breadcrumbs, $gallery->getBreadcrumbs());
    }

    public function testGetBreadcrumbsSingleWedding() {
        $gallery = Gallery::withId('10');
        $breadcrumbs = [
            0 => [
                'title' => 'Wedding',
                'link' => 'index.php'
            ],
            1 => [
                'title' => 'Gallery',
                'link' => 'gallery.php?w=8'
            ],
            2 => [
                'title' => 'Engagements',
                'link' => ''
            ]
        ];
        $this->assertEquals($breadcrumbs, $gallery->getBreadcrumbs());
    }

    public function testGetBreadcrumbsDoubleWedding() {
        $gallery = Gallery::withId('25');
        $breadcrumbs = [
            0 => [
                'title' => 'Wedding',
                'link' => 'index.php'
            ],
            1 => [
                'title' => 'Gallery',
                'link' => 'gallery.php?w=8'
            ],
            2 => [
                'title' => 'Weddings',
                'link' => 'gallery.php?w=11'
            ],
            3 => [
                'title' => 'Wedding 1',
                'link' => ''
            ]
        ];
        $this->assertEquals($breadcrumbs, $gallery->getBreadcrumbs());
    }

    public function testGetBreadcrumbsBasicCommercial() {
        $gallery = Gallery::withId('52');
        $breadcrumbs = [
            0 => [
                'title' => 'Commercial',
                'link' => 'index.php'
            ],
            1 => [
                'title' => 'Gallery',
                'link' => ''
            ]
        ];
        $this->assertEquals($breadcrumbs, $gallery->getBreadcrumbs());
    }

    public function testGetBreadcrumbsSingleCommercial() {
        $gallery = Gallery::withId('56');
        $breadcrumbs = [
            0 => [
                'title' => 'Commercial',
                'link' => 'index.php'
            ],
            1 => [
                'title' => 'Gallery',
                'link' => 'gallery.php?w=52'
            ],
            2 => [
                'title' => 'Professional Branding',
                'link' => ''
            ]
        ];
        $this->assertEquals($breadcrumbs, $gallery->getBreadcrumbs());
    }

    public function testGetBreadcrumbsDoubleCommercial() {
        $gallery = Gallery::withId('71');
        $breadcrumbs = [
            0 => [
                'title' => 'Commercial',
                'link' => 'index.php'
            ],
            1 => [
                'title' => 'Gallery',
                'link' => 'gallery.php?w=52'
            ],
            2 => [
                'title' => 'Events',
                'link' => 'gallery.php?w=57'
            ],
            3 => [
                'title' => 'Corporate Picnic',
                'link' => ''
            ]
        ];
        $this->assertEquals($breadcrumbs, $gallery->getBreadcrumbs());
    }


    public function testGetBreadcrumbsBasicPortraitProduct() {
        $gallery = Gallery::withId('28');
        $breadcrumbs = [
            0 => [
                'title' => 'Portrait',
                'link' => 'index.php'
            ],
            1 => [
                'title' => 'Services',
                'link' => 'details.php'
            ],
            2 => [
                'title' => 'Products',
                'link' => 'products.php'
            ],
            3 => [
                'title' => 'Gallery',
                'link' => ''
            ]
        ];
        $this->assertEquals($breadcrumbs, $gallery->getBreadcrumbs());
    }

    public function testGetBreadcrumbsSinglePortraitProduct() {
        $gallery = Gallery::withId('33');
        $breadcrumbs = [
            0 => [
                'title' => 'Portrait',
                'link' => 'index.php'
            ],
            1 => [
                'title' => 'Services',
                'link' => 'details.php'
            ],
            2 => [
                'title' => 'Products',
                'link' => 'products.php'
            ],
            3 => [
                'title' => 'Gallery',
                'link' => 'gallery.php?w=28'
            ],
            4 => [
                'title' => 'Stand Out Frames',
                'link' => ''
            ]
        ];
        $this->assertEquals($breadcrumbs, $gallery->getBreadcrumbs());
    }

    public function testGetBreadcrumbsBasicWeddingProduct() {
        $gallery = Gallery::withId('38');
        $breadcrumbs = [
            0 => [
                'title' => 'Wedding',
                'link' => 'index.php'
            ],
            1 => [
                'title' => 'Services',
                'link' => 'details.php'
            ],
            2 => [
                'title' => 'Products',
                'link' => 'products.php'
            ],
            3 => [
                'title' => 'Gallery',
                'link' => ''
            ]
        ];
        $this->assertEquals($breadcrumbs, $gallery->getBreadcrumbs());
    }

    public function testGetBreadcrumbsSingleWeddingProduct() {
        $gallery = Gallery::withId('40');
        $breadcrumbs = [
            0 => [
                'title' => 'Wedding',
                'link' => 'index.php'
            ],
            1 => [
                'title' => 'Services',
                'link' => 'details.php'
            ],
            2 => [
                'title' => 'Products',
                'link' => 'products.php'
            ],
            3 => [
                'title' => 'Gallery',
                'link' => 'gallery.php?w=38'
            ],
            4 => [
                'title' => 'Heirloom Albums',
                'link' => ''
            ]
        ];
        $this->assertEquals($breadcrumbs, $gallery->getBreadcrumbs());
    }

    public function testGetBreadcrumbsDoubleWeddingProduct() {
        $gallery = Gallery::withId('46');
        $breadcrumbs = [
            0 => [
                'title' => 'Wedding',
                'link' => 'index.php'
            ],
            1 => [
                'title' => 'Services',
                'link' => 'details.php'
            ],
            2 => [
                'title' => 'Products',
                'link' => 'products.php'
            ],
            3 => [
                'title' => 'Gallery',
                'link' => 'gallery.php?w=38'
            ],
            4 => [
                'title' => 'Heirloom Albums',
                'link' => 'gallery.php?w=40'
            ],
            5 => [
                'title' => 'Signature Albums',
                'link' => ''
            ]
        ];
        $this->assertEquals($breadcrumbs, $gallery->getBreadcrumbs());
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
        try {
            $gallery = Gallery::withId(1);
            $gallery->getParent();
        } catch (TypeError $e) {
            $this->assertEquals('Return value of Gallery::getParent() must be an instance of Gallery, null returned', $e->getMessage());
        }
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

    public function testGetImageLocation() {
        $gallery = Gallery::withId(0);
        $this->assertEquals('/img/main/leigh-ann/', $gallery->getImageLocation());
    }

    public function testGetBasicImageLocation() {
        $gallery = Gallery::withId(2);
        $this->assertEquals('/portrait/img/maternity/', $gallery->getImageLocation());
    }

    public function testGetNestedImageLocation() {
        $gallery = Gallery::withId(13);
        $this->assertEquals('/portrait/img/newborn/favorites/', $gallery->getImageLocation());
    }

    public function testGetNestedWImageLocation() {
        $gallery = Gallery::withId(18);
        $this->assertEquals('/wedding/img/surprise-proposals/dc-mall/', $gallery->getImageLocation());
    }

    public function testGetVeryNestedImageLocation() {
        $gallery = Gallery::withId(46);
        $this->assertEquals('/wedding/img/product/heirloom-albums/signature-albums/', $gallery->getImageLocation());
    }

    public function testGetSpecialImageLocation() {
        $gallery = Gallery::withId(16);
        $this->assertEquals('/img/main/home-studio/', $gallery->getImageLocation());
    }

    public function testGetProductImageLocation() {
        $gallery = Gallery::withId(29);
        $this->assertEquals('/portrait/img/product/story-grids/', $gallery->getImageLocation());
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

    public function testWithParams() {
        try {
            Gallery::withParams(null);
        } catch (Exception $e) {
            $this->assertEquals('Not yet implemented', $e->getMessage());
        }
    }
}