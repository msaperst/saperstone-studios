<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use ui\models\Registration;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseFeatureContext.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Registration.php';

class RegistrationFeatureContext implements Context {

    private $driver;
    private $user;
    private $wait;
    private $baseUrl;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 10);
        $this->baseUrl = $environment->getContext('BaseFeatureContext')->getBaseUrl();
        $this->user = $environment->getContext('BaseFeatureContext')->getUser();
    }

    /**
     * @Given /^I am on the registration page$/
     */
    public function iAmOnTheRegistrationPage() {
        $this->driver->get($this->baseUrl . 'register.php');
   }

    /**
     * @When I register a user
     */
    public function iRegisterAUser() {
        $registration = new Registration($this->driver, $this->wait);
        $registration->register($this->user->getUsername(), $this->user->getPassword(), false);
    }

    /**
     * @Then /^I see that there is no register option to remember me$/
     */
    public function iSeeThatThereIsNoRegisterOptionToRememberMe() {
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('profile-remember-span'))->isDisplayed());
    }
}