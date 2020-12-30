<?php

namespace ui\page;

use CustomAsserts;
use Facebook\WebDriver\WebDriverBy;
use Google\Exception;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestBase.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UserPagesLoadTest extends TestBase {

    /**
     * @throws Exception
     */
    public function testContractsPage() {
        $this->driver->get($this->baseUrl . 'user/contracts.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/user/contracts.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/user/contracts.php\' target=\'_blank\'>%s://%s/user/contracts.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testContractsPageAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/contracts.php');
        $this->assertEquals('Manage Contracts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    /**
     * @throws Exception
     */
    public function testIndexPage() {
        $this->driver->get($this->baseUrl . 'user/index.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/user/index.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/user/index.php\' target=\'_blank\'>%s://%s/user/index.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
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

    /**
     * @throws Exception
     */
    public function testProductsPage() {
        $this->driver->get($this->baseUrl . 'user/products.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/user/products.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/user/products.php\' target=\'_blank\'>%s://%s/user/products.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
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

    /**
     * @throws Exception
     */
    public function testProfilePage() {
        $this->driver->get($this->baseUrl . 'user/profile.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/user/profile.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/user/profile.php\' target=\'_blank\'>%s://%s/user/profile.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
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

    /**
     * @throws Exception
     */
    public function testUsagePage() {
        $this->driver->get($this->baseUrl . 'user/usage.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/user/usage.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/user/usage.php\' target=\'_blank\'>%s://%s/user/usage.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testUsagePageAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/usage.php');
        $this->assertEquals('Site Usage', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    /**
     * @throws Exception
     */
    public function testUsersPage() {
        $this->driver->get($this->baseUrl . 'user/users.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/user/users.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/user/users.php\' target=\'_blank\'>%s://%s/user/users.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testUsersPageAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/users.php');
        $this->assertEquals('Manage Users', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }
}