<?php

namespace ui;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;

class SampleSeleniumTest extends TestCase {
    private $driver;

    public function setUp() {
        $host = 'http://localhost:4444/wd/hub';
        if (getenv('BROWSER') == 'firefox') {
            $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox());
        } else {
            $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
        }
        $this->driver->get('http://' . getenv('DB_HOST') . ':90/');
    }

    public function tearDown() {
        $this->driver->takeScreenshot(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . "ui-" . getenv('BROWSER') . DIRECTORY_SEPARATOR . "{$this->getName()}.png");
        $this->driver->quit();
    }

    public function testSample() {
        $this->assertEquals('Photography Services', $this->driver->findElement(WebDriverBy::tagName('h2'))->getText());
    }
}