<?php
namespace ui;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;

class SampleSeleniumTest extends TestCase {
    private $driver;

    public function setUp() {
        $host = 'http://127.0.0.1:4444';
        $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox());
        $this->driver->get('http://' . getenv('DB_HOST') . ':90/');
    }

    public function tearDown() {
        $this->driver->quit();
    }

    public function testSample() {
        $this->assertEquals('Photography Services', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
    }
}