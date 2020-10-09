<?php

namespace ui;

use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;

class TestBase extends TestCase {

    protected $driver;
    protected $baseUrl;
    protected $copyright = 'Copyright © Saperstone Studios 2020';

    public function setUp() {
        $host = 'http://127.0.0.1:4444/wd/hub';
        if (getenv('BROWSER') == 'firefox') {
            $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox());
        } else {
            $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
        }
        $this->baseUrl = 'http://' . getenv('APP_URL') . ':90/';
    }

    public function tearDown() {
        $this->driver->takeScreenshot(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . "ui-" . getenv('BROWSER') . DIRECTORY_SEPARATOR . "{$this->getName()}.png");
        $this->driver->quit();
    }

    protected function acceptCookies() {
        $cookie = new Cookie('CookiePreferences', '["preferences","analytics"]');
        $this->driver->manage()->addCookie($cookie);
    }

    protected function adminLogin() {
        $this->loginAs('1d7505e7f434a7713e84ba399e937191');
    }

    protected function loginAs($hash) {
        $cookie = new Cookie('hash', $hash);
        $this->driver->manage()->addCookie($cookie);
    }
}