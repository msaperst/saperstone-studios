<?php

namespace ui\load;

use Facebook\WebDriver\WebDriverBy;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestBase.php';
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UserPagesLoadTest extends TestBase {

    public function testContractsPage() {
        $this->driver->get($this->baseUrl . 'user/contracts.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testContractsPageAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/contracts.php');
        $this->assertEquals('Manage Contracts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testIndexPage() {
        $this->driver->get($this->baseUrl . 'user/index.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testIndexPageAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/index.php');
        $this->assertEquals('Manage Albums', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('add-album-div'))));
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('add-album-btn'))->isDisplayed());
        $this->assertEquals(7, sizeof($this->driver->findElement(WebDriverBy::id('albums'))->findElement(WebDriverBy::tagName('thead'))->findElements(WebDriverBy::tagName('th'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testIndexPageAuth() {
        $this->driver->get($this->baseUrl);
        $this->loginAs('5510b5e6fffd897c234cafe499f76146');
        $this->driver->get($this->baseUrl . 'user/index.php');
        $this->assertEquals('View Albums', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('add-album-div'))->isDisplayed());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('add-album-btn'))));
        $this->assertEquals(4, sizeof($this->driver->findElement(WebDriverBy::id('albums'))->findElement(WebDriverBy::tagName('thead'))->findElements(WebDriverBy::tagName('th'))));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testProductsPage() {
        $this->driver->get($this->baseUrl . 'user/products.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testProductsPageAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/products.php');
        $this->assertEquals('Manage Products', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $sections = $this->driver->findElements(WebDriverBy::className('row'));
        $this->assertEquals(8, sizeof($sections));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testProductsPageSignature() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/products.php');
        $sections = $this->driver->findElements(WebDriverBy::className('row'));
        $this->assertEquals('Signature', $sections[1]->findElement(WebDriverBy::tagName('h2'))->getText());
        $signatures = $sections[1]->findElements(WebDriverBy::className('bootstrap-dialog'));
        $this->assertEquals(3, sizeof($signatures));
        $this->assertEquals(1, $signatures[0]->getAttribute('product-type'));
        $this->assertEquals(2, $signatures[1]->getAttribute('product-type'));
        $this->assertEquals(4, $signatures[2]->getAttribute('product-type'));
    }

    public function testProductsPageStandard() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/products.php');
        $sections = $this->driver->findElements(WebDriverBy::className('row'));
        $this->assertEquals('Standard', $sections[2]->findElement(WebDriverBy::tagName('h2'))->getText());
        $standard = $sections[2]->findElements(WebDriverBy::className('bootstrap-dialog'));
        $this->assertEquals(3, sizeof($standard));
        $this->assertEquals(3, $standard[0]->getAttribute('product-type'));
        $this->assertEquals(5, $standard[1]->getAttribute('product-type'));
        $this->assertEquals(6, $standard[2]->getAttribute('product-type'));
    }

    public function testProductsPagePrints() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/products.php');
        $sections = $this->driver->findElements(WebDriverBy::className('row'));
        $this->assertEquals('Prints', $sections[3]->findElement(WebDriverBy::tagName('h2'))->getText());
        $prints = $sections[3]->findElements(WebDriverBy::className('bootstrap-dialog'));
        $this->assertEquals(2, sizeof($prints));
        $this->assertEquals(7, $prints[0]->getAttribute('product-type'));
        $this->assertEquals(8, $prints[1]->getAttribute('product-type'));
    }

    public function testProductsPageDigital() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/products.php');
        $sections = $this->driver->findElements(WebDriverBy::className('row'));
        $this->assertEquals('Digital', $sections[4]->findElement(WebDriverBy::tagName('h2'))->getText());
        $digital = $sections[4]->findElements(WebDriverBy::className('bootstrap-dialog'));
        $this->assertEquals(2, sizeof($digital));
        $this->assertEquals(9, $digital[0]->getAttribute('product-type'));
        $this->assertEquals(10, $digital[1]->getAttribute('product-type'));
    }

    public function testProductsPageOther() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/products.php');
        $sections = $this->driver->findElements(WebDriverBy::className('row'));
        $this->assertEquals('Other', $sections[5]->findElement(WebDriverBy::tagName('h2'))->getText());
        $other = $sections[5]->findElements(WebDriverBy::className('bootstrap-dialog'));
        $this->assertEquals(0, sizeof($other));
    }

    public function testProfilePage() {
        $this->driver->get($this->baseUrl . 'user/profile.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testProfilePageAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/profile.php');
        $this->assertEquals('Manage Profile', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('msaperst', $this->driver->findElement(WebDriverBy::id('profile-username'))->getAttribute('value'));
        $this->assertEquals('Max', $this->driver->findElement(WebDriverBy::id('profile-firstname'))->getAttribute('value'));
        $this->assertEquals('Saperstone', $this->driver->findElement(WebDriverBy::id('profile-lastname'))->getAttribute('value'));
        $this->assertEquals('msaperst@gmail.com', $this->driver->findElement(WebDriverBy::id('profile-email'))->getAttribute('value'));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testUsagePage() {
        $this->driver->get($this->baseUrl . 'user/usage.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testUsagePageAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/usage.php');
        $this->assertEquals('Site Usage', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testUsersPage() {
        $this->driver->get($this->baseUrl . 'user/users.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testUsersPageAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/users.php');
        $this->assertEquals('Manage Users', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }
}