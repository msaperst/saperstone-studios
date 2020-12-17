<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Exception;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Sql;
use User;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

/**
 * Defines application behat from the specific context.
 */
class BaseFeatureContext implements Context {

    const reportDir = __DIR__ . '/../../../reports/behat/';
    const reportFile = BaseFeatureContext::reportDir . 'screenshots.html';
    /**
     * @var RemoteWebDriver
     */
    private $driver;
    private $baseUrl;
    /**
     * @var User
     */
    private $user;
    private $deleteUser = true;

    public function getDriver(): RemoteWebDriver {
        return $this->driver;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getBaseUrl() {
        return $this->baseUrl;
    }

    public function setUser(User $user) {
        $this->user = $user;
    }

    public function dontDeleteUser() {
        $this->deleteUser = false;
    }

    /**
     * @BeforeSuite
     */
    public static function setupTestReport() {
        // setup our logging
        if (!file_exists(BaseFeatureContext::reportDir)) {
            mkdir(BaseFeatureContext::reportDir);
        }
        $output = fopen(BaseFeatureContext::reportFile, 'w');
        fwrite($output, str_replace('$PAGE_TITLE', getenv('BROWSER') . ' BDD Tests', file_get_contents('https://gist.githubusercontent.com/msaperst/24d9a7d2e8f3e6ff1df26e5492a1b726/raw/1ede7c7c23ddb97153a464931e6bbf39cc737231/gistfile1.txt')));
        fwrite($output, '<h1 align="center">' . getenv('BROWSER') . ' BDD Tests</h1>');
        fclose($output);
    }

    /**
     * @BeforeScenario
     * @throws Exception
     */
    public function setupUser() {
        // setup our webdriver instance
        $host = 'http://127.0.0.1:4444/wd/hub';
        if (getenv('BROWSER') == 'firefox') {
            $desiredCapabilities = DesiredCapabilities::firefox();
        } else {
            $desiredCapabilities = DesiredCapabilities::chrome();
        }
        if (getenv('PROXY') != NULL) {
            $desiredCapabilities->setCapability(WebDriverCapabilityType::PROXY, ['proxyType' => 'MANUAL', 'httpProxy' => getenv('PROXY'), 'ftpProxy' => NULL, 'sslProxy' => NULL, 'noProxy' => NULL]);
        }

        $this->driver = RemoteWebDriver::create($host, $desiredCapabilities);
        $this->baseUrl = 'http://' . getenv('APP_URL') . ':90/';
        $this->user = new User();

        // setup some basic cookies in the browser
        $this->driver->get($this->baseUrl);
        $cookie = new Cookie('CookiePreferences', '["preferences","analytics"]');
        $this->driver->manage()->addCookie($cookie);
        $cookie = new Cookie('CookieShow', 'true');
        $this->driver->manage()->addCookie($cookie);
        $this->driver->navigate()->refresh();

        // setup a basic user
        $params = [
            'username' => 'testUser',
            'firstName' => 'test',
            'lastName' => 'user',
            'email' => 'msaperst+sstest@gmail.com',
            'password' => '12345'
        ];
        $this->user = User::withParams($params);
    }

    /**
     * @AfterScenario
     * @param AfterScenarioScope $scope
     * @throws Exception
     */
    public function cleanup(AfterScenarioScope $scope) {
        $scenarioName = $scope->getFeature()->getTitle() . ' : ' . $scope->getScenario()->getTitle() . ' : ' . $scope->getScenario()->getLine();
        $screenshot = $this->driver->takeScreenshot();
        $this->driver->takeScreenshot(BaseFeatureContext::reportDir . 'screenshots' . DIRECTORY_SEPARATOR . $scenarioName . '.png');
        $this->driver->quit();
        // log our screenshot
        $output = fopen(BaseFeatureContext::reportFile, 'a');
        fwrite($output, '<p><h2 class="r' . $scope->getTestResult()->getResultCode() . '" style="cursor: pointer;" onclick="toggleImg(this)">' . $scenarioName . '</h2><img alt="screenshot" style="max-width: 100%; display: none;" src="data:image/png;base64,' . base64_encode($screenshot) . '"/></p>');
        fclose($output);
        // if we created a new user
        if ($this->user->getId() != '' && $this->deleteUser) {
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $this->user->delete();
            unset($_SESSION ['hash']);
            $sql = new Sql();
            //$sql->executeStatement("DELETE FROM users WHERE usr = 'testUser';");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `users`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `users` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }

    /**
     * @AfterSuite
     */
    public static function cleanupTestReport() {
        // setup our logging
        $output = fopen(BaseFeatureContext::reportFile, 'a');
        fwrite($output, '</body>');
        fwrite($output, '</html>');
        fclose($output);
    }
}
