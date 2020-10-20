<?php

namespace ui;

use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;

class TestBase extends TestCase {

    private $reportDir;
    private $reportFile;
    protected $driver;
    protected $baseUrl;
    protected $copyright = 'Copyright © Saperstone Studios 2020';

    public function setUp() {
        //setup our logging
        $this->reportDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . "ui-" . getenv('BROWSER') . DIRECTORY_SEPARATOR;
        $this->reportFile = $this->reportDir . 'index.html';
        if( !file_exists($this->reportDir)) {
            mkdir($this->reportDir);
        }
        if( !file_exists($this->reportFile) ) {
            $output = fopen( $this->reportFile, 'w' );
            fwrite($output, str_replace('$PAGE_TITLE', getenv('BROWSER') . ' Page Load Tests', file_get_contents('https://raw.githubusercontent.com/msaperst/saperstone-studios/feature/sqlRework/tests/resources/uiTestResultTemplate.html?token=AAURJLORMB3GDR6SI5RQRQC7R4ZDK')));
            fwrite($output, '<h1 align="center">' . getenv('BROWSER') . ' Page Load Tests</h1>');
            fclose($output);
        }
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
        $this->driver->takeScreenshot( $this->reportDir . $this->getName() . '.png');
        $this->driver->quit();
        $output = fopen( $this->reportFile, 'a' );
        fwrite($output, '<p><h2 class="r' . $this->getStatus() . '" style="cursor: pointer;" onclick="toggleImg(this)">' . $this->getName() . '</h2><img style="max-width: 100%; display: none;" src="data:image/png;base64,' . base64_encode($screenshot) . '"/></p>');
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