<?php

namespace ui\page;

use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;

class TestBase extends TestCase {

    const reportDir = __DIR__ . '/../../../reports/ui/';
    const reportFile = TestBase::reportDir . 'index.html';
    protected $driver;
    protected $baseUrl;
    protected $copyright = 'Copyright © Saperstone Studios 2020';

    public static function setUpBeforeClass() {
        // setup our logging
        if (!file_exists(TestBase::reportDir)) {
            mkdir(TestBase::reportDir);
        }
        if (!file_exists(TestBase::reportFile)) {
            $output = fopen(TestBase::reportFile, 'w');
            fwrite($output, str_replace('$PAGE_TITLE', getenv('BROWSER') . ' Page Load Tests', file_get_contents('https://gist.githubusercontent.com/msaperst/24d9a7d2e8f3e6ff1df26e5492a1b726/raw/1ede7c7c23ddb97153a464931e6bbf39cc737231/gistfile1.txt')));
            fwrite($output, '<h1 align="center">' . getenv('BROWSER') . ' Page Load Tests</h1>');
            fclose($output);
        }
    }

    public function setUp() {
        //setup our browser
        $host = 'http://127.0.0.1:4444/wd/hub';
        if (getenv('BROWSER') == 'firefox') {
            $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox());
        } else {
            $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
        }
        $this->baseUrl = 'http://' . getenv('APP_URL') . ':90/';
    }

    public function tearDown() {
        $screenshot = $this->driver->takeScreenshot();
        $this->driver->takeScreenshot(TestBase::reportDir . 'screenshots' . DIRECTORY_SEPARATOR . $this->getName() . '.png');
        $this->driver->quit();
        $output = fopen(TestBase::reportFile, 'a');
        fwrite($output, '<p><div style="cursor: pointer;" onclick="toggleImg(this)"><h2 class="r' . $this->getStatus() . '">' . $this->getName() . '</h2>' . $this->getStatusMessage() . '</div><img alt="screenshot" style="max-width: 100%; display: none;" src="data:image/png;base64,' . base64_encode($screenshot) . '"/></p>');
        fclose($output);
    }

    protected function acceptCookies() {
        $cookie = new Cookie('CookiePreferences', '["preferences","analytics"]');
        $this->driver->manage()->addCookie($cookie);
        $cookie = new Cookie('CookieShow', 'true');
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