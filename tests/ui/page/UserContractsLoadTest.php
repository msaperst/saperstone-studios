<?php

namespace ui\page;

use Facebook\WebDriver\WebDriverBy;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestBase.php';
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class UserContractsLoadTest extends TestBase {

    public function testCommercialContract() {
        $this->driver->get($this->baseUrl . 'user/contract/commercial.php');
        $this->assertTrue($this->driver->findElement(WebDriverBy::xpath('//*[text()="401"]'))->isDisplayed());
    }

    public function testCommercialContractAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/contract/commercial.php');
        $this->assertEquals('Saperstone Studios LLC. Commercial Contract', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
        $this->assertEquals(16, sizeof($this->driver->findElements(WebDriverBy::tagName('p'))));
    }

    public function testContractorContract() {
        $this->driver->get($this->baseUrl . 'user/contract/contractor.php');
        $this->assertTrue($this->driver->findElement(WebDriverBy::xpath('//*[text()="401"]'))->isDisplayed());
    }

    public function testContractorContractAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/contract/contractor.php');
        $this->assertEquals('Saperstone Studios LLC. Contractor Contract', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
        $this->assertEquals(6, sizeof($this->driver->findElements(WebDriverBy::tagName('p'))));
    }

    public function testEventContract() {
        $this->driver->get($this->baseUrl . 'user/contract/event.php');
        $this->assertTrue($this->driver->findElement(WebDriverBy::xpath('//*[text()="401"]'))->isDisplayed());
    }

    public function testEventContractAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/contract/event.php');
        $this->assertEquals('Saperstone Studios LLC. Event Contract', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
        $this->assertEquals(17, sizeof($this->driver->findElements(WebDriverBy::tagName('p'))));
    }

    public function testPartnershipContract() {
        $this->driver->get($this->baseUrl . 'user/contract/partnership.php');
        $this->assertTrue($this->driver->findElement(WebDriverBy::xpath('//*[text()="401"]'))->isDisplayed());
    }

    public function testPartnershipContractAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/contract/partnership.php');
        $this->assertEquals('Saperstone Studios LLC. Partnership Contract', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
        $this->assertEquals(21, sizeof($this->driver->findElements(WebDriverBy::tagName('p'))));
    }

    public function testPortraitContract() {
        $this->driver->get($this->baseUrl . 'user/contract/portrait.php');
        $this->assertTrue($this->driver->findElement(WebDriverBy::xpath('//*[text()="401"]'))->isDisplayed());
    }

    public function testPortraitContractAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/contract/portrait.php');
        $this->assertEquals('Saperstone Studios LLC. Portrait Contract', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
        $this->assertEquals(15, sizeof($this->driver->findElements(WebDriverBy::tagName('p'))));
    }

    public function testWeddingContract() {
        $this->driver->get($this->baseUrl . 'user/contract/wedding.php');
        $this->assertTrue($this->driver->findElement(WebDriverBy::xpath('//*[text()="401"]'))->isDisplayed());
    }

    public function testWeddingContractAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/contract/wedding.php');
        $this->assertEquals('Saperstone Studios LLC. Wedding Contract', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
        $this->assertEquals(17, sizeof($this->driver->findElements(WebDriverBy::tagName('p'))));
    }
}