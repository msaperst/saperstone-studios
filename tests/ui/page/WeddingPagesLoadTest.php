<?php

namespace ui\page;

use CustomAsserts;
use Facebook\WebDriver\WebDriverBy;
use Google\Exception;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestBase.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';

class WeddingPagesLoadTest extends TestBase {

    public function testDetailsPage() {
        $this->driver->get($this->baseUrl . 'wedding/details.php');
        $this->assertEquals('Wedding Session Details', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testEngagementPage() {
        $this->driver->get($this->baseUrl . 'wedding/engagement.php');
        $this->assertEquals('Engagement Session', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testExperiencePage() {
        $this->driver->get($this->baseUrl . 'wedding/experience.php');
        $this->assertEquals('The Wedding Experience', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testIndexPage() {
        $this->driver->get($this->baseUrl . 'wedding/index.php');
        $this->assertEquals('Weddings', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testNightPage() {
        $this->driver->get($this->baseUrl . 'wedding/night.php');
        $this->assertEquals('Night Photography', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testPhotoboothPage() {
        $this->driver->get($this->baseUrl . 'wedding/photobooth.php');
        $this->assertEquals('Photobooth', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testProcessPage() {
        $this->driver->get($this->baseUrl . 'wedding/process.php');
        $this->assertEquals('The Process', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testProductsPage() {
        $this->driver->get($this->baseUrl . 'wedding/products.php');
        $this->assertEquals('Products & Investment', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals(7, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testRetouchPage() {
        $this->driver->get($this->baseUrl . 'wedding/retouch.php');
        $this->assertEquals('Retouch', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testStudioPage() {
        $this->driver->get($this->baseUrl . 'wedding/studio.php');
        $this->assertEquals('Home Studio', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home Wedding Details Studio', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    // dynamic pages

    /**
     * @throws Exception
     */
    public function testGalleriesPageBad() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/wedding/galleries.php?w=\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/wedding/galleries.php?w=\' target=\'_blank\'>%s://%s/wedding/galleries.php?w=</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testGalleries() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=17');
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('edit-gallery-btn'))));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('save-gallery-btn'))));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('sort-gallery-btn'))));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('delete-image-btn'))));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('edit-image-btn'))));
    }

    public function testGalleriesAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=17');
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('edit-gallery-btn'))->isDisplayed());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('save-gallery-btn'))->isDisplayed());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('sort-gallery-btn'))->isDisplayed());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('delete-image-btn'))->isDisplayed());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('edit-image-btn'))->isDisplayed());
    }

    public function testGalleriesPage17() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=17');
        $this->assertEquals('National Harbor Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGallerySurprise ProposalsNational Harbor', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('National-Harbor'))->isDisplayed());
//        $this->assertEquals('National Harbor Gallery', $this->driver->findElement(WebDriverBy::id('National-Harbor'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('National-Harbor-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage18() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=18');
        $this->assertEquals('DC Mall Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGallerySurprise ProposalsDC Mall', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('DC-Mall'))->isDisplayed());
//        $this->assertEquals('DC Mall Gallery', $this->driver->findElement(WebDriverBy::id('DC-Mall'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('DC-Mall-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage19() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=19');
        $this->assertEquals('Georgetown Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGallerySurprise ProposalsGeorgetown', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Georgetown'))->isDisplayed());
//        $this->assertEquals('Georgetown Gallery', $this->driver->findElement(WebDriverBy::id('Georgetown'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Georgetown-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage20() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=20');
        $this->assertEquals('Favorites Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGalleryEngagementsFavorites', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Favorites'))->isDisplayed());
//        $this->assertEquals('Favorites Gallery', $this->driver->findElement(WebDriverBy::id('Favorites'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Favorites-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage21() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=21');
        $this->assertEquals('Washington DC Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGalleryEngagementsWashington DC', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Washington-DC'))->isDisplayed());
//        $this->assertEquals('Washington DC Gallery', $this->driver->findElement(WebDriverBy::id('Washington-DC'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Washington-DC-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage22() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=22');
        $this->assertEquals('Old Town Alexandria Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGalleryEngagementsOld Town Alexandria', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Old-Town-Alexandria'))->isDisplayed());
//        $this->assertEquals('Old Town Alexandria Gallery', $this->driver->findElement(WebDriverBy::id('Old-Town-Alexandria'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Old-Town-Alexandria-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage23() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=23');
        $this->assertEquals('Paint War Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGalleryEngagementsPaint War', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Paint-War'))->isDisplayed());
//        $this->assertEquals('Paint War Gallery', $this->driver->findElement(WebDriverBy::id('Paint-War'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Paint-War-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage24() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=24');
        $this->assertEquals('Favorites Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGalleryWeddingsFavorites', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Favorites'))->isDisplayed());
//        $this->assertEquals('Favorites Gallery', $this->driver->findElement(WebDriverBy::id('Favorites'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Favorites-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage25() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=25');
        $this->assertEquals('Wedding 1 Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGalleryWeddingsWedding 1', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Wedding-1'))->isDisplayed());
//        $this->assertEquals('Wedding 1 Gallery', $this->driver->findElement(WebDriverBy::id('Wedding-1'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Wedding-1-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage26() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=26');
        $this->assertEquals('Wedding 2 Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGalleryWeddingsWedding 2', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Wedding-2'))->isDisplayed());
//        $this->assertEquals('Wedding 2 Gallery', $this->driver->findElement(WebDriverBy::id('Wedding-2'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Wedding-2-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage27() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=27');
        $this->assertEquals('Wedding 3 Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGalleryWeddingsWedding 3', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Wedding-3'))->isDisplayed());
//        $this->assertEquals('Wedding 3 Gallery', $this->driver->findElement(WebDriverBy::id('Wedding-3'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Wedding-3-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }


    public function testGalleriesPage37() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=37');
        $this->assertEquals('Photobooth Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGalleryPhotobooth', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Photobooth'))->isDisplayed());
//        $this->assertEquals('Photobooth Gallery', $this->driver->findElement(WebDriverBy::id('Photobooth'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Photobooth-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage39() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=39');
        $this->assertEquals('Story Grids Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingServicesProductsGalleryStory Grids', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('Hate making sure your prints are evenly spaced once hung on the wall? Then this is the art product for you. Each story grid comes with a paper template to hang on the wall. Your template indicates where to place metal pegs which then make up a grid system on your wall. Simply slip the prints onto the metal pegs and voila! Evenly spaced prints! These collages range in overall sizes from 2\' x 3\' all the way up to 4.5\' x 2\' or beyond and are totally customizable. Images are printed on either metal or a lustre photographic paper, your choice.', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Story-Grids'))->isDisplayed());
//        $this->assertEquals('Story Grids Gallery', $this->driver->findElement(WebDriverBy::id('Story Grids'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Story-Grids-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage40() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=40');
        $this->assertEquals('Heirloom Albums Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingServicesProductsGalleryHeirloom Albums', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('How are you planning to share all those amazing memories with future little ones? Heirloom albums are the perfect way to put all your favorite images in one place. Hand made and printed Fuji lustre paper that has a 100+ year rating (the highest in the industry), these albums will be sure to stand the test of time for generations to come.', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Heirloom-Albums'))->isDisplayed());
//        $this->assertEquals('Heirloom Albums Gallery', $this->driver->findElement(WebDriverBy::id('Heirloom Albums'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Heirloom-Albums-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage41() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=41');
        $this->assertEquals('Acrylic Prints Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingServicesProductsGalleryAcrylic Prints', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('These gorgeous portraits are printed on a metallic paper and mounted under acrylic for a frameless modern way to display your images. They stand out from the wall about 3/4 of an inch which gives depth. One image can stand alone or order multiples to display a series from your session.', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Acrylic-Prints'))->isDisplayed());
//        $this->assertEquals('Acrylic Prints Gallery', $this->driver->findElement(WebDriverBy::id('Studio'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Acrylic-Prints-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage42() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=42');
        $this->assertEquals('Keepsake Boxes Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingServicesProductsGalleryKeepsake Boxes', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('Perfect for anyone who wants to display a lot of images but doesn\'t have a ton of wall space. These custom 5x7 boxes come with 10 of your favorite images from your session printed on lustre paper and mounted on a rigid black styrene. Rotate through displaying your images on the included easel for all to enjoy.', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Keepsake-Boxes'))->isDisplayed());
//        $this->assertEquals('Keepsake Boxes Gallery', $this->driver->findElement(WebDriverBy::id('Keepsake Boxes'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Keepsake-Boxes-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage43() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=43');
        $this->assertEquals('Stand Out Frames Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingServicesProductsGalleryStand Out Frames', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('These lustre prints are mounted on 3/4 inch thick foam core and wrapped with either a black or white edge. Modern, sleek and light weight for easy hanging!', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Stand-Out-Frames'))->isDisplayed());
//        $this->assertEquals('Stand Out Frames Gallery', $this->driver->findElement(WebDriverBy::id('Stand-Out-Frames'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Stand-Out-Frames-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage44() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=44');
        $this->assertEquals('Canvas Prints Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingServicesProductsGalleryCanvas Prints', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('That classic, timeless look of canvas can\'t be beat. Archival quality stretched canvas over a solid wooden frame built to stand the test of time. Hang just one or multiples to create a cluster of images that tell a story from your session.', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Canvas-Prints'))->isDisplayed());
//        $this->assertEquals('Canvas Prints Gallery', $this->driver->findElement(WebDriverBy::id('Canvas-Prints'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Canvas-Prints-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage45() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=45');
        $this->assertEquals('Standard Albums Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingServicesProductsGalleryHeirloom AlbumsStandard Albums', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Standard-Albums'))->isDisplayed());
//        $this->assertEquals('Standard Albums Gallery', $this->driver->findElement(WebDriverBy::id('Standard-Albums'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Standard-Albums-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage46() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=46');
        $this->assertEquals('Signature Albums Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingServicesProductsGalleryHeirloom AlbumsSignature Albums', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Signature-Albums'))->isDisplayed());
//        $this->assertEquals('Signature Albums Gallery', $this->driver->findElement(WebDriverBy::id('Signature-Albums'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Signature-Albums-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage47() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=47');
        $this->assertEquals('Engagement Book Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingServicesProductsGalleryHeirloom AlbumsEngagement Book', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Engagement-Book'))->isDisplayed());
//        $this->assertEquals('Engagement Book Gallery', $this->driver->findElement(WebDriverBy::id('Engagement-Book'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Engagement-Book-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage63() {
        $this->driver->get($this->baseUrl . 'wedding/galleries.php?w=63');
        $this->assertEquals('Reveal Box Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingServicesProductsGalleryReveal Box', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('Hand made in Italy with a wide array of color options in leatherette to customize to your home and style. The Reveal Box holds 15 of your favorite images from your session on fine art paper either printed on a thick styrene at 8x10 or printed as 5x7 and placed in an 8x10 matte - Mix and match however you choose.

This box also allows for display versatility. Place your favorite image on top to be viewed through the glass window, place images in an 8x10 frame and put on your walls or tabletop or even place images as is on an easel. These images can go from box to frame and back again with extreme ease.', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Reveal-Box'))->isDisplayed());
//        $this->assertEquals('Reveal Box Gallery', $this->driver->findElement(WebDriverBy::id('Reveal-Box'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Reveal-Box-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    /**
     * @throws Exception
     */
    public function testGalleryPageBad() {
        $this->driver->get($this->baseUrl . 'wedding/gallery.php?w=17');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/wedding/gallery.php?w=17\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/wedding/gallery.php?w=17\' target=\'_blank\'>%s://%s/wedding/gallery.php?w=17</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testGalleryPage8() {
        $this->driver->get($this->baseUrl . 'wedding/gallery.php?w=8');
        $this->assertEquals('Wedding Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGallery', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(5, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage9() {
        $this->driver->get($this->baseUrl . 'wedding/gallery.php?w=9');
        $this->assertEquals('Surprise Proposals Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGallerySurprise Proposals', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(3, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage10() {
        $this->driver->get($this->baseUrl . 'wedding/gallery.php?w=10');
        $this->assertEquals('Engagements Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGalleryEngagements', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(4, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage11() {
        $this->driver->get($this->baseUrl . 'wedding/gallery.php?w=11');
        $this->assertEquals('Weddings Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingGalleryWeddings', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(4, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage38() {
        $this->driver->get($this->baseUrl . 'wedding/gallery.php?w=38');
        $this->assertEquals('Product Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingServicesProductsGallery', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(7, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage40() {
        $this->driver->get($this->baseUrl . 'wedding/gallery.php?w=40');
        $this->assertEquals('Heirloom Albums Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home WeddingServicesProductsGalleryHeirloom Albums', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(3, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    /**
     * @throws Exception
     */
    public function testReviewsPageBlankC() {
        $this->driver->get($this->baseUrl . 'wedding/reviews.php?c=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/wedding/reviews.php?c=\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/wedding/reviews.php?c=\' target=\'_blank\'>%s://%s/wedding/reviews.php?c=</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws Exception
     */
    public function testReviewsPageBadC() {
        $this->driver->get($this->baseUrl . 'wedding/reviews.php?c=abc');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/wedding/reviews.php?c=abc\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/wedding/reviews.php?c=abc\' target=\'_blank\'>%s://%s/wedding/reviews.php?c=abc</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testReviewsPageWedding() {
        $this->driver->get($this->baseUrl . 'wedding/reviews.php?c=2');
        $this->assertEquals('Wedding Raves', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home Wedding Raves', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(5, sizeof($this->driver->findElements(WebDriverBy::className('review-holder'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }
}