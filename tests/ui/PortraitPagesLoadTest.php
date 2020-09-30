<?php

namespace ui;

use Facebook\WebDriver\WebDriverBy;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestBase.php';

class PortraitPagesLoadTest extends TestBase {

    public function testDetailsPage() {
        $this->driver->get($this->baseUrl . 'portrait/details.php');
        $this->assertEquals('Portrait Session Details', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testFaqPage() {
        $this->driver->get($this->baseUrl . 'portrait/faq.php');
        $this->assertEquals('Portrait Session Frequently Asked Questions', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testIndexPage() {
        $this->driver->get($this->baseUrl . 'portrait/index.php');
        $this->assertEquals('Portraits', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testManipulationPage() {
        $this->driver->get($this->baseUrl . 'portrait/manipulation.php');
        $this->assertEquals('Other Image Edits', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testNewbornFaqPage() {
        $this->driver->get($this->baseUrl . 'portrait/newborn-faq.php');
        $this->assertEquals('Newborn Frequently Asked Questions', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testPortraitRetouchPage() {
        $this->driver->get($this->baseUrl . 'portrait/portrait-retouch.php');
        $this->assertEquals('Portrait Retouch', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testProcessPage() {
        $this->driver->get($this->baseUrl . 'portrait/process.php');
        $this->assertEquals('The Process', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testProductsPage() {
        $this->driver->get($this->baseUrl . 'portrait/products.php');
        $this->assertEquals('Products & Investment', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals(9, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testRestorationPage() {
        $this->driver->get($this->baseUrl . 'portrait/restoration.php');
        $this->assertEquals('Restoration', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testRetouchPage() {
        $this->driver->get($this->baseUrl . 'portrait/retouch.php');
        $this->assertEquals('Retouch', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testSessionsPage() {
        $this->driver->get($this->baseUrl . 'portrait/sessions.php');
        $this->assertEquals('Session Information', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testStudioPage() {
        $this->driver->get($this->baseUrl . 'portrait/studio.php');
        $this->assertEquals('Home Studio', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testWhatToWearPage() {
        $this->driver->get($this->baseUrl . 'portrait/what-to-wear.php');
        $this->assertEquals('What to Wear', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    // dynamic pages

    public function testGalleriesPageBad() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleries() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=2');
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('edit-gallery-btn'))));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('save-gallery-btn'))));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('sort-gallery-btn'))));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('delete-image-btn'))));
    }

    public function testGalleriesAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=2');
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('edit-gallery-btn'))->isDisplayed());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('save-gallery-btn'))->isDisplayed());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('sort-gallery-btn'))->isDisplayed());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('delete-image-btn'))->isDisplayed());
    }

    public function testGalleriesPage2() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=2');
        $this->assertEquals('Maternity Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitGalleryMaternity', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Maternity'))->isDisplayed());
//        $this->assertEquals('Maternity Gallery', $this->driver->findElement(WebDriverBy::id('Maternity'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Maternity-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage6() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=6');
        $this->assertEquals('Kids and Family Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitGalleryKids and Family', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Kids-and-Family'))->isDisplayed());
//        $this->assertEquals('Kids and Family Gallery', $this->driver->findElement(WebDriverBy::id('Kids-and-Family'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Kids-and-Family-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage7() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=7');
        $this->assertEquals('Seniors Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitGallerySeniors', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Seniors'))->isDisplayed());
//        $this->assertEquals('Seniors Gallery', $this->driver->findElement(WebDriverBy::id('Seniors'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Seniors-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage13() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=13');
        $this->assertEquals('Favorites Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitGalleryNewbornFavorites', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Favorites'))->isDisplayed());
//        $this->assertEquals('Favorites Gallery', $this->driver->findElement(WebDriverBy::id('Favorites'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Favorites-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage14() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=14');
        $this->assertEquals('At Your Home Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitGalleryNewbornAt Your Home', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('At-Your-Home'))->isDisplayed());
//        $this->assertEquals('At Your Home Gallery', $this->driver->findElement(WebDriverBy::id('At-Your-Home'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('At-Your-Home-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage15() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=15');
        $this->assertEquals('Studio Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitGalleryNewbornStudio', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Studio'))->isDisplayed());
//        $this->assertEquals('Studio Gallery', $this->driver->findElement(WebDriverBy::id('Studio'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Studio-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage29() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=29');
        $this->assertEquals('Story Grids Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitServicesProductsGalleryStory Grids', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('Hate making sure your prints are evenly spaced once hung on the wall? Then this is the art product for you. Each story grid comes with a paper template to hang on the wall. Your template indicates where to place metal pegs which then make up a grid system on your wall. Simply slip the prints onto the metal pegs and voila! Evenly spaced prints! These collages range in overall sizes from 2\' x 3\' all the way up to 4.5\' x 2\' or beyond and are totally customizable. Images are printed on either metal or a lustre photographic paper, your choice.', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Story-Grids'))->isDisplayed());
//        $this->assertEquals('Story Grids Gallery', $this->driver->findElement(WebDriverBy::id('Story Grids'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Story-Grids-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage30() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=30');
        $this->assertEquals('Heirloom Albums Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitServicesProductsGalleryHeirloom Albums', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('How are you planning to share all those amazing memories with future little ones? Heirloom albums are the perfect way to put all your favorite images in one place. Hand made and printed Fuji lustre paper that has a 100+ year rating (the highest in the industry), these albums will be sure to stand the test of time for generations to come.', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Heirloom-Albums'))->isDisplayed());
//        $this->assertEquals('Heirloom Albums Gallery', $this->driver->findElement(WebDriverBy::id('Heirloom Albums'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Heirloom-Albums-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage31() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=31');
        $this->assertEquals('Acrylic Prints Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitServicesProductsGalleryAcrylic Prints', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('These gorgeous portraits are printed on a metallic paper and mounted under acrylic for a frameless modern way to display your images. They stand out from the wall about 3/4 of an inch which gives depth. One image can stand alone or order multiples to display a series from your session.', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Acrylic-Prints'))->isDisplayed());
//        $this->assertEquals('Acrylic Prints Gallery', $this->driver->findElement(WebDriverBy::id('Studio'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Acrylic-Prints-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage32() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=32');
        $this->assertEquals('Keepsake Boxes Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitServicesProductsGalleryKeepsake Boxes', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('Perfect for anyone who wants to display a lot of images but doesn\'t have a ton of wall space. These custom 5x7 boxes come with 10 of your favorite images from your session printed on lustre paper and mounted on a rigid black styrene. Rotate through displaying your images on the included easel for all to enjoy.', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Keepsake-Boxes'))->isDisplayed());
//        $this->assertEquals('Keepsake Boxes Gallery', $this->driver->findElement(WebDriverBy::id('Keepsake Boxes'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Keepsake-Boxes-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage33() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=33');
        $this->assertEquals('Stand Out Frames Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitServicesProductsGalleryStand Out Frames', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('These lustre prints are mounted on 3/4 inch thick foam core and wrapped with either a black or white edge. Modern, sleek and light weight for easy hanging!', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Stand-Out-Frames'))->isDisplayed());
//        $this->assertEquals('Stand Out Frames Gallery', $this->driver->findElement(WebDriverBy::id('Stand-Out-Frames'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Stand-Out-Frames-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage34() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=34');
        $this->assertEquals('Canvas Prints Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitServicesProductsGalleryCanvas Prints', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('That classic, timeless look of canvas can\'t be beat. Archival quality stretched canvas over a solid wooden frame built to stand the test of time. Hang just one or multiples to create a cluster of images that tell a story from your session.', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Canvas-Prints'))->isDisplayed());
//        $this->assertEquals('Canvas Prints Gallery', $this->driver->findElement(WebDriverBy::id('Canvas-Prints'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Canvas-Prints-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage35() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=35');
        $this->assertEquals('In Studio Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitGallery6 MonthsIn Studio', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('In-Studio'))->isDisplayed());
//        $this->assertEquals('In Studio Gallery', $this->driver->findElement(WebDriverBy::id('In-Studio'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('In-Studio-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage36() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=36');
        $this->assertEquals('On Location Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitGallery6 MonthsOn Location', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('On-Location'))->isDisplayed());
//        $this->assertEquals('On Location Gallery', $this->driver->findElement(WebDriverBy::id('On-Location'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('On-Location-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage48() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=48');
        $this->assertEquals('Studio Sessions Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitGalleryFirst BirthdayStudio Sessions', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Studio-Sessions'))->isDisplayed());
//        $this->assertEquals('Studio Sessions Gallery', $this->driver->findElement(WebDriverBy::id('Studio-Sessions'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Studio-Sessions-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage49() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=49');
        $this->assertEquals('On Location Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitGalleryFirst BirthdayOn Location', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('On-Location'))->isDisplayed());
//        $this->assertEquals('On Location Gallery', $this->driver->findElement(WebDriverBy::id('On-Location'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('On-Location-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage50() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=50');
        $this->assertEquals('Album Block Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitServicesProductsGalleryAlbum Block', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('This wooden block holds 10 of your favorite 5x7 images and is perfect for display on a mantle, coffee table or shelf. Photos are mounted on a durable styrene, making it easy to rotate through displaying your images. Color options are black or white, your choice!', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Album-Block'))->isDisplayed());
//        $this->assertEquals('Album Block Gallery', $this->driver->findElement(WebDriverBy::id('Album-Block'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Album-Block-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage51() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=51');
        $this->assertEquals('Keepsake USB Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitServicesProductsGalleryKeepsake USB', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('Every time you order digital files, they are given to you in this keepsake USB case for safe keeping. Be sure to back these images up in multiple locations as digital media is forever changing!', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Keepsake-USB'))->isDisplayed());
//        $this->assertEquals('Keepsake USB Gallery', $this->driver->findElement(WebDriverBy::id('Keepsake-USB'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Keepsake-USB-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage62() {
        $this->driver->get($this->baseUrl . 'portrait/galleries.php?w=62');
        $this->assertEquals('Reveal Box Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitServicesProductsGalleryReveal Box', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('gallery-comment'))->isDisplayed());
        $this->assertEquals('Hand made in Italy with a wide array of color options in leatherette to customize to your home and style. The Reveal Box holds 15 of your favorite images from your session on fine art paper either printed on a thick styrene at 8x10 or printed as 5x7 and placed in an 8x10 matte - Mix and match however you choose.

This box also allows for display versatility. Place your favorite image on top to be viewed through the glass window, place images in an 8x10 frame and put on your walls or tabletop or even place images as is on an easel. These images can go from box to frame and back again with extreme ease.', $this->driver->findElement(WebDriverBy::id('gallery-comment'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Reveal-Box'))->isDisplayed());
//        $this->assertEquals('Reveal Box Gallery', $this->driver->findElement(WebDriverBy::id('Reveal-Box'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Reveal-Box-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPageBad() {
        $this->driver->get($this->baseUrl . 'portrait/gallery.php?w=2');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage1() {
        $this->driver->get($this->baseUrl . 'portrait/gallery.php?w=1');
        $this->assertEquals('Portrait Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitGallery', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(6, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage3() {
        $this->driver->get($this->baseUrl . 'portrait/gallery.php?w=3');
        $this->assertEquals('Newborn Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitGalleryNewborn', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(3, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage4() {
        $this->driver->get($this->baseUrl . 'portrait/gallery.php?w=4');
        $this->assertEquals('6 Months Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitGallery6 Months', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(2, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage5() {
        $this->driver->get($this->baseUrl . 'portrait/gallery.php?w=5');
        $this->assertEquals('First Birthday Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitGalleryFirst Birthday', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(2, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage28() {
        $this->driver->get($this->baseUrl . 'portrait/gallery.php?w=28');
        $this->assertEquals('Product Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home PortraitServicesProductsGallery', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(9, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testReviewsPageBlankC() {
        $this->driver->get($this->baseUrl . 'portrait/reviews.php?c=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testReviewsPageBadC() {
        $this->driver->get($this->baseUrl . 'portrait/reviews.php?c=abc');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testReviewsPagePortrait() {
        $this->driver->get($this->baseUrl . 'portrait/reviews.php?c=1');
        $this->assertEquals('Portrait Raves', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home Portrait Raves', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(5, sizeof($this->driver->findElements(WebDriverBy::className('review-holder'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }
}