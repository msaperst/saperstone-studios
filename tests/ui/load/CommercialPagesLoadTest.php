<?php

namespace ui\load;

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
        $this->assertEquals('Home Commercial Details Studio', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    // dynamic pages

    public function testGalleriesPageBad() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleries() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=53');
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('edit-gallery-btn'))));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('save-gallery-btn'))));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('sort-gallery-btn'))));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('delete-image-btn'))));
    }

    public function testGalleriesAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=53');
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('edit-gallery-btn'))->isDisplayed());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('save-gallery-btn'))->isDisplayed());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('sort-gallery-btn'))->isDisplayed());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('delete-image-btn'))->isDisplayed());
    }

    public function testGalleriesPage53() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=53');
        $this->assertEquals('Studio Headshots Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home CommercialGalleryStudio Headshots', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Studio-Headshots'))->isDisplayed());
//        $this->assertEquals('Studio Headshots Gallery', $this->driver->findElement(WebDriverBy::id('Studio-Headshots'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Studio-Headshots-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage54() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=54');
        $this->assertEquals('On Location Headshots Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home CommercialGalleryOn Location Headshots', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('On-Location-Headshots'))->isDisplayed());
//        $this->assertEquals('On Location Headshots Gallery', $this->driver->findElement(WebDriverBy::id('On-Location-Headshots'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('On-Location-Headshots-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage55() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=55');
        $this->assertEquals('Company Headshots and Team Photos Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home CommercialGalleryCompany Headshots and Team Photos', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Company-Headshots-and-Team-Photos'))->isDisplayed());
//        $this->assertEquals('Company Headshots and Team Photos Gallery', $this->driver->findElement(WebDriverBy::id('On-Location-Headshots'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Company-Headshots-and-Team-Photos-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage58() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=58');
        $this->assertEquals('Photobooth Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home CommercialGalleryPhotobooth', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Photobooth'))->isDisplayed());
//        $this->assertEquals('Photobooth Gallery', $this->driver->findElement(WebDriverBy::id('On-Location-Headshots'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Photobooth-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage65() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=65');
        $this->assertEquals('Team Hot Cocoa Social Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home CommercialGalleryProfessional BrandingTeam Hot Cocoa Social', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Team-Hot-Cocoa-Social'))->isDisplayed());
//        $this->assertEquals('Team Hot Cocoa Social Gallery', $this->driver->findElement(WebDriverBy::id('On-Location-Headshots'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Team-Hot-Cocoa-Social-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage66() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=66');
        $this->assertEquals('Corporate Culture Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home CommercialGalleryProfessional BrandingCorporate Culture', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Corporate-Culture'))->isDisplayed());
//        $this->assertEquals('Corporate Culture Gallery', $this->driver->findElement(WebDriverBy::id('On-Location-Headshots'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Corporate-Culture-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage67() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=67');
        $this->assertEquals('Doctor Care Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home CommercialGalleryProfessional BrandingDoctor Care', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Doctor-Care'))->isDisplayed());
//        $this->assertEquals('Doctor Care Gallery', $this->driver->findElement(WebDriverBy::id('On-Location-Headshots'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Doctor-Care-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage68() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=68');
        $this->assertEquals('NeuroGrow - Brain Fitness Center Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home CommercialGalleryProfessional BrandingNeuroGrow - Brain Fitness Center', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('NeuroGrow---Brain-Fitness-Center'))->isDisplayed());
//        $this->assertEquals('NeuroGrow - Brain Fitness Center Gallery Gallery', $this->driver->findElement(WebDriverBy::id('On-Location-Headshots'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('NeuroGrow---Brain-Fitness-Center-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage69() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=69');
        $this->assertEquals('Company Meeting Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home CommercialGalleryEventsCompany Meeting', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Company-Meeting'))->isDisplayed());
//        $this->assertEquals('Company Meeting Gallery', $this->driver->findElement(WebDriverBy::id('On-Location-Headshots'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Company-Meeting-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage70() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=70');
        $this->assertEquals('Holiday Party Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home CommercialGalleryEventsHoliday Party', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Holiday-Party'))->isDisplayed());
//        $this->assertEquals('Holiday Party Gallery', $this->driver->findElement(WebDriverBy::id('On-Location-Headshots'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Holiday-Party-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleriesPage71() {
        $this->driver->get($this->baseUrl . 'commercial/galleries.php?w=71');
        $this->assertEquals('Corporate Picnic Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home CommercialGalleryEventsCorporate Picnic', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('gallery-comment'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Corporate-Picnic'))->isDisplayed());
//        $this->assertEquals('Corporate Picnic Gallery', $this->driver->findElement(WebDriverBy::id('On-Location-Headshots'))->findElement(WebDriverBy::className('modal-title'))->getText());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('Corporate-Picnic-carousel'))->isDisplayed());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPageBad() {
        $this->driver->get($this->baseUrl . 'commercial/gallery.php?w=54');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage52() {
        $this->driver->get($this->baseUrl . 'commercial/gallery.php?w=52');
        $this->assertEquals('Commercial Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home CommercialGallery', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(6, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage56() {
        $this->driver->get($this->baseUrl . 'commercial/gallery.php?w=56');
        $this->assertEquals('Professional Branding Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home CommercialGalleryProfessional Branding', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(4, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testGalleryPage57() {
        $this->driver->get($this->baseUrl . 'commercial/gallery.php?w=57');
        $this->assertEquals('Events Gallery', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home CommercialGalleryEvents', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(3, sizeof($this->driver->findElements(WebDriverBy::className('col-xs-12'))));
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
        $this->assertEquals('Home Commercial Raves', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(4, sizeof($this->driver->findElements(WebDriverBy::className('review-holder'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }
}