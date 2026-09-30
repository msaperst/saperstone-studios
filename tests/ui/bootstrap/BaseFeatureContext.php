<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Testwork\Tester\Result\TestResult;
use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Firefox\FirefoxOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Sql;
use User;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

/**
 * Defines application behat from the specific context.
 */
class BaseFeatureContext implements Context {

    const string reportDir = __DIR__ . '/../../../reports/behat/';
    const string downloadDir = __DIR__ . '/../../../tmp/behat-downloads/';
    /**
     * @var RemoteWebDriver
     */
    private RemoteWebDriver $driver;
    private string $baseUrl;
    /**
     * @var User
     */
    private User $user;
    private bool $deleteUser = true;

    public function getDriver(): RemoteWebDriver {
        return $this->driver;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getBaseUrl(): string {
        return $this->baseUrl;
    }

    public function getDownloadDirectory(): string {
        return realpath(self::downloadDir) ?: self::downloadDir;
    }

    public function setUser(User $user): void {
        $this->user = $user;
    }

    public function dontDeleteUser(): void {
        $this->deleteUser = false;
    }

    /**
     * @BeforeSuite
     */
    public static function setupTestReport(): void {
        $directories = [
            BaseFeatureContext::reportDir . 'screenshots',
            BaseFeatureContext::downloadDir,
        ];
        foreach ($directories as $directory) {
            if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new \RuntimeException("Unable to create Behat directory: $directory");
            }
        }
    }

    /**
     * @BeforeScenario
     * @throws Exception
     */
    public function setupUser(): void {
        // Each scenario must start with an empty mailbox so email assertions
        // cannot match messages left behind by earlier scenarios.
        \CustomAsserts::clearAllEmails();

        // setup our webdriver instance
        $host = 'http://127.0.0.1:4444';
        $headless = getenv('HEADLESS');
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
            $chromeOptions->setExperimentalOption('prefs', [
                'download.default_directory' => $this->getDownloadDirectory(),
                'download.prompt_for_download' => false,
                'download.directory_upgrade' => true,
            ]);
            $desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);
        }
        if (getenv('PROXY') != NULL) {
            $desiredCapabilities->setCapability(WebDriverCapabilityType::PROXY, ['proxyType' => 'MANUAL', 'httpProxy' => getenv('PROXY'), 'ftpProxy' => NULL, 'sslProxy' => NULL, 'noProxy' => NULL]);
        }

        $this->driver = RemoteWebDriver::create($host, $desiredCapabilities);
        $this->baseUrl = 'http://' . getenv('APP_URL') . ':' . getenv('HTTP_PORT') . '/';
        $this->user = new User();

        // setup some basic cookies in the browser
        $this->driver->get($this->baseUrl);
        $cookie = new Cookie('CookiePreferences', '["preferences","analytics","social"]');
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
    public function cleanup(AfterScenarioScope $scope): void {
        if ($scope->getTestResult()->getResultCode() !== TestResult::PASSED) {
            $scenarioName = $scope->getFeature()->getTitle() . ' : ' . $scope->getScenario()->getTitle() . ' : ' . $scope->getScenario()->getLine();
            $safeScenarioName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $scenarioName);
            $this->driver->takeScreenshot(BaseFeatureContext::reportDir . 'screenshots' . DIRECTORY_SEPARATOR . $safeScenarioName . '.png');
        }
        $this->driver->quit();
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

}
