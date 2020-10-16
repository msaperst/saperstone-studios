<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

/**
 * Defines application features from the specific context.
 */
class BaseFeatureContext implements Context {

    protected $driver;
    protected $baseUrl;
    protected $user;

    public function getDriver() {
        return $this->driver;
    }

    public function getUser() {
        return $this->user;
    }

    public function getBaseUrl() {
        return $this->baseUrl;
    }

    public function setUser(User $user) {
        $this->user = $user;
    }

    /**
     * @BeforeScenario
     */
    public function setupUser(BeforeScenarioScope $scope) {
        $host = 'http://127.0.0.1:4444/wd/hub';
        if (getenv('BROWSER') == 'firefox') {
            $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox());
        } else {
            $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());
        }
        $this->baseUrl = 'http://' . getenv('APP_URL') . ':90/';
        $this->user = new User();

        $this->driver->get($this->baseUrl);
        $cookie = new Cookie('CookiePreferences', '["preferences","analytics"]');
        $this->driver->manage()->addCookie($cookie);
        $cookie = new Cookie('CookieShow', 'true');
        $this->driver->manage()->addCookie($cookie);
        $this->driver->navigate()->refresh();

        $params = [
            'username' => 'testUser',
            'email' => 'msaperst+sstest@gmail.com',
            'password' => '12345'
        ];
        $this->user = User::withParams($params);
    }

    /**
     * @AfterScenario
     */
    public function cleanup(AfterScenarioScope $scope) {
        $scenarioName = $scope->getFeature()->getTitle() . ' : ' . $scope->getScenario()->getTitle() . ' : ' . $scope->getScenario()->getLine();
        $this->driver->takeScreenshot(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . 'behat' . DIRECTORY_SEPARATOR . $scenarioName . '.png');
        $this->driver->quit();
        // if we created a new user
        if ($this->user->getId() != '') {
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
