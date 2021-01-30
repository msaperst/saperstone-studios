<?php

namespace ui\page;

use CustomAsserts;
use Facebook\WebDriver\WebDriverBy;
use Google\Exception;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestBase.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UserContractsLoadTest extends TestBase {

    /**
     * @throws Exception
     */
    public function testCommercialContract() {
        $this->driver->get($this->baseUrl . 'user/contract/commercial.php');
        $this->assertTrue($this->driver->findElement(WebDriverBy::xpath('//*[text()="401"]'))->isDisplayed());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/user/contract/commercial.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/user/contract/commercial.php\' target=\'_blank\'>%s://%s/user/contract/commercial.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testCommercialContractAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/contract/commercial.php');
        $this->assertEquals('Saperstone Studios LLC. Commercial Contract', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
        $this->assertEquals(16, sizeof($this->driver->findElements(WebDriverBy::tagName('p'))));
    }

    /**
     * @throws Exception
     */
    public function testContractorContract() {
        $this->driver->get($this->baseUrl . 'user/contract/contractor.php');
        $this->assertTrue($this->driver->findElement(WebDriverBy::xpath('//*[text()="401"]'))->isDisplayed());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/user/contract/contractor.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/user/contract/contractor.php\' target=\'_blank\'>%s://%s/user/contract/contractor.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testContractorContractAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/contract/contractor.php');
        $this->assertEquals('Saperstone Studios LLC. Contractor Contract', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
        $this->assertEquals(6, sizeof($this->driver->findElements(WebDriverBy::tagName('p'))));
    }

    /**
     * @throws Exception
     */
    public function testEventContract() {
        $this->driver->get($this->baseUrl . 'user/contract/event.php');
        $this->assertTrue($this->driver->findElement(WebDriverBy::xpath('//*[text()="401"]'))->isDisplayed());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/user/contract/event.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/user/contract/event.php\' target=\'_blank\'>%s://%s/user/contract/event.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testEventContractAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/contract/event.php');
        $this->assertEquals('Saperstone Studios LLC. Event Contract', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
        $this->assertEquals(17, sizeof($this->driver->findElements(WebDriverBy::tagName('p'))));
    }

    /**
     * @throws Exception
     */
    public function testPartnershipContract() {
        $this->driver->get($this->baseUrl . 'user/contract/partnership.php');
        $this->assertTrue($this->driver->findElement(WebDriverBy::xpath('//*[text()="401"]'))->isDisplayed());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/user/contract/partnership.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/user/contract/partnership.php\' target=\'_blank\'>%s://%s/user/contract/partnership.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testPartnershipContractAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/contract/partnership.php');
        $this->assertEquals('Saperstone Studios LLC. Partnership Contract', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
        $this->assertEquals(21, sizeof($this->driver->findElements(WebDriverBy::tagName('p'))));
    }

    /**
     * @throws Exception
     */
    public function testPortraitContract() {
        $this->driver->get($this->baseUrl . 'user/contract/portrait.php');
        $this->assertTrue($this->driver->findElement(WebDriverBy::xpath('//*[text()="401"]'))->isDisplayed());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/user/contract/portrait.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/user/contract/portrait.php\' target=\'_blank\'>%s://%s/user/contract/portrait.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testPortraitContractAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/contract/portrait.php');
        $this->assertEquals('Saperstone Studios LLC. Portrait Contract', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
        $this->assertEquals(15, sizeof($this->driver->findElements(WebDriverBy::tagName('p'))));
    }

    /**
     * @throws Exception
     */
    public function testWeddingContract() {
        $this->driver->get($this->baseUrl . 'user/contract/wedding.php');
        $this->assertTrue($this->driver->findElement(WebDriverBy::xpath('//*[text()="401"]'))->isDisplayed());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/user/contract/wedding.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/user/contract/wedding.php\' target=\'_blank\'>%s://%s/user/contract/wedding.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testWeddingContractAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/contract/wedding.php');
        $this->assertEquals('Saperstone Studios LLC. Wedding Contract', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
        $this->assertEquals(17, sizeof($this->driver->findElements(WebDriverBy::tagName('p'))));
    }
}