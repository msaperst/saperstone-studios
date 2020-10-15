<?php

use Behat\Behat\Context\Context;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverWait;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

/**
 * Defines application features from the specific context.
 */
class BaseFeatureContext implements Context {

    protected $driver;
    protected $wait;
    protected $baseUrl;
    protected $user;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct() {
        $host = 'http://127.0.0.1:4444/wd/hub';
        if (getenv('BROWSER') == 'firefox') {
            $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox());
        } else {
            $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
        }
        $this->wait = new WebDriverWait($this->driver, 10);
        $this->baseUrl = 'http://' . getenv('APP_URL') . ':90/';
        $this->user = new User();

        $this->driver->get($this->baseUrl);
        $cookie = new Cookie('CookiePreferences', '["preferences","analytics"]');
        $this->driver->manage()->addCookie($cookie);
        $cookie = new Cookie('CookieShow', 'true');
        $this->driver->manage()->addCookie($cookie);
        $this->driver->navigate()->refresh();
    }

    /**
     * @AfterScenario
     */
    public function cleanup() {
        $this->driver->takeScreenshot(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . "ui-" . getenv('BROWSER') . DIRECTORY_SEPARATOR . "sample.png");
        $this->driver->quit();
        // if we created a new user
        if( $this->user->getId() != '') {
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $this->user->delete();
            unset($_SESSION ['hash']);
            $sql = new Sql();
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }
}
