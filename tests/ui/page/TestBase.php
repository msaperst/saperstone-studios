<?php

namespace ui\page;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverDimension;
use PHPUnit\Framework\TestCase;

class TestBase extends TestCase {

    const string reportDir = __DIR__ . '/../../../reports/ui/';
    const string reportFile = TestBase::reportDir . 'index.html';
    protected RemoteWebDriver $driver;
    protected string $baseUrl;
    protected string $copyright;

    public static function setUpBeforeClass(): void {
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

    public function setUp(): void {
        $this->copyright = 'Copyright © Saperstone Studios ' . date("Y");
        $headless = getenv('HEADLESS') ? getenv('HEADLESS') : 'false';
        //setup our browser
        $host = 'http://127.0.0.1:4444';
        if (getenv('BROWSER') == 'firefox') {
            $desiredCapabilities = DesiredCapabilities::firefox();
            $firefoxOptions = new FirefoxOptions();
            if ($headless) {
                $firefoxOptions->addArguments(['-headless']);
            }
            $desiredCapabilities->setCapability(FirefoxOptions::CAPABILITY, $firefoxOptions);
        } else {
            $desiredCapabilities = DesiredCapabilities::chrome();
            $chromeOptions = new ChromeOptions();
            if ($headless) {
                $chromeOptions->addArguments(['-headless']);
            }
            $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        }
        $desiredCapabilities->setCapability('acceptSslCerts', true);
        $this->driver = RemoteWebDriver::create($host, $desiredCapabilities);
        $this->driver->manage()->window()->setSize(new WebDriverDimension(1000, 1000));
        $this->baseUrl = 'http://' . getenv('APP_URL') . ':90/';
    }

    public function tearDown(): void {
        $screenshot = $this->driver->takeScreenshot();
        $this->driver->takeScreenshot(TestBase::reportDir . 'screenshots' . DIRECTORY_SEPARATOR . $this->name() . '.png');
        $this->driver->quit();
        $output = fopen(TestBase::reportFile, 'a');
        fwrite($output, '<p><div style="cursor: pointer;" onclick="toggleImg(this)"><h2 class="r' . $this->status()->asInt() . '">' . $this->name() . '</h2>' . $this->status()->message() . '</div><img alt="screenshot" style="max-width: 100%; display: none;" src="data:image/png;base64,' . base64_encode($screenshot) . '"/></p>');
        fclose($output);
    }

    protected function acceptCookies(): void {
        $this->driver->get($this->baseUrl);
        $cookie = [
            'name' => 'CookiePreferences',
            'value' => '["preferences","analytics"]',
            'domain' => getenv('APP_URL'),
            'path' => '/',
            'secure' => false,
            'httpOnly' => false,
            'expiry' => time() + 3600,
        ];
        $this->driver->manage()->addCookie($cookie);
        $cookie['name'] = 'CookieShow';
        $cookie['value'] = 'true';
        $this->driver->manage()->addCookie($cookie);
    }

    protected function adminLogin(): void {
        $this->loginAs('1d7505e7f434a7713e84ba399e937191');
    }

    protected function loginAs($hash): void {
        $cookie = new Cookie('hash', $hash);
        $this->driver->manage()->addCookie($cookie);
    }
}
