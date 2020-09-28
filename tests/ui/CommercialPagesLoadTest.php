<?php

namespace ui;

use Facebook\WebDriver\WebDriverBy;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestBase.php';

class CommercialPagesLoadTest extends TestBase {

    public function testAboutPage() {
        $this->driver->get($this->baseUrl . 'commercial/about.php');
        $this->assertEquals('About Saperstone Studios', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testBackgroundPage() {
        $this->driver->get($this->baseUrl . 'commercial/background.php');
        $this->assertEquals('Background Options', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testDetailsPage() {
        $this->driver->get($this->baseUrl . 'commercial/details.php');
        $this->assertEquals('Details', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testExpectPage() {
        $this->driver->get($this->baseUrl . 'commercial/expect.php');
        $this->assertEquals('What to Expect', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testFaqPage() {
        $this->driver->get($this->baseUrl . 'commercial/faq.php');
        $this->assertEquals('FAQ', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testIndexPage() {
        $this->driver->get($this->baseUrl . 'commercial/index.php');
        $this->assertEquals('Commercial', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testPhotoboothPage() {
        $this->driver->get($this->baseUrl . 'commercial/photobooth.php');
        $this->assertEquals('Photobooth', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testPricingPage() {
        $this->driver->get($this->baseUrl . 'commercial/pricing.php');
        $this->assertEquals('Pricing', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testPriviaPage() {
        $this->driver->get($this->baseUrl . 'commercial/privia.php');
        $this->assertEquals('Privia Health Terms of Service', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testRetouchPage() {
        $this->driver->get($this->baseUrl . 'commercial/retouch.php');
        $this->assertEquals('Retouch', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testServicesPage() {
        $this->driver->get($this->baseUrl . 'commercial/services.php');
        $this->assertEquals('Services', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testStudioPage() {
        $this->driver->get($this->baseUrl . 'commercial/studio.php');
        $this->assertEquals('Home Studio', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    // dynamic pages

    public function testGalleriesPageBad() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage53() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=53');
        $this->assertEquals('Studio Headshots Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage54() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=54');
        $this->assertEquals('On Location Headshots Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage55() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=55');
        $this->assertEquals('Company Headshots and Team Photos Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage58() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=58');
        $this->assertEquals('Photobooth Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage65() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=65');
        $this->assertEquals('Team Hot Cocoa Social Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage66() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=66');
        $this->assertEquals('Corporate Culture Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage67() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=67');
        $this->assertEquals('Doctor Care Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage68() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=68');
        $this->assertEquals('NeuroGrow - Brain Fitness Center Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage69() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=69');
        $this->assertEquals('Company Meeting Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage70() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=70');
        $this->assertEquals('Holiday Party Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage71() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=71');
        $this->assertEquals('Corporate Picnic Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPageBad() {
        $this->driver->get($this->baseUrl . 'commercial/gallery.php?w=54');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage56() {
        $this->driver->get($this->baseUrl . 'commercial/gallery.php?w=56');
        $this->assertEquals('Professional Branding Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage57() {
        $this->driver->get($this->baseUrl . 'commercial/gallery.php?w=57');
        $this->assertEquals('Events Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testReviewsPageBlankC() {
        $this->driver->get($this->baseUrl . 'commercial/reviews.php?c=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testReviewsPageBadC() {
        $this->driver->get($this->baseUrl . 'commercial/reviews.php?c=abc');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testReviewsPageCommercial() {
        $this->driver->get($this->baseUrl . 'commercial/reviews.php?c=3');
        $this->assertEquals('Commercial Raves', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }
}