<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use ui\models\Registration;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseFeatureContext.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Registration.php';

class RegistrationFeatureContext implements Context {

    private $environment;

    private $driver;
    private $user;
    private $wait;
    private $baseUrl;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $this->environment = $scope->getEnvironment();
        $this->driver = $this->environment->getContext('BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 10);
        $this->baseUrl = $this->environment->getContext('BaseFeatureContext')->getBaseUrl();
        $this->user = $this->environment->getContext('BaseFeatureContext')->getUser();
    }

    /**
     * @Given /^I am on the registration page$/
     */
    public function iAmOnTheRegistrationPage() {
        $this->driver->get($this->baseUrl . 'register.php');
   }

    /**
     * @When /^I register my user$/
     */
    public function iRegisterMyUser() {
        $register = new Registration($this->driver, $this->wait);
        try {
            $user = $register->registerMyUser($this->user);
            $this->environment->getContext('BaseFeatureContext')->setUser($user);
        } catch(Exception $e) {
            $this->environment->getContext('BaseFeatureContext')->dontDeleteUser();
        }
    }

    /**
     * @When /^I register a user with "([^"]*)", "([^"]*)", "([^"]*)", "([^"]*)", "([^"]*)", "([^"]*)"$/
     */
    public function iRegisterAUserWith($username, $password, $confirmPassword, $firstName, $lastName, $email) {
        $register = new Registration($this->driver, $this->wait);
        try {
            $user = $register->registerAUser($username, $password, $confirmPassword, $firstName, $lastName, $email);
            $this->environment->getContext('BaseFeatureContext')->setUser($user);
        } catch(Exception $e) {
            $this->environment->getContext('BaseFeatureContext')->dontDeleteUser();
        }
    }

    /**
     * @When /^I try to register a user with "([^"]*)", "([^"]*)", "([^"]*)", "([^"]*)", "([^"]*)", "([^"]*)"$/
     */
    public function iTryToRegisterAUserWith($username, $password, $confirmPassword, $firstName, $lastName, $email) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterAUserInfo($username, $password, $confirmPassword, $firstName, $lastName, $email);
    }

    /**
     * @When /^I try to register a username of "([^"]*)"$/
     */
    public function iTryToRegisterAUsernameOf($username) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterUserNameInfo($username);
    }

    /**
     * @When /^I try to register a password of "([^"]*)"$/
     */
    public function iTryToRegisterAPasswordOf($password) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterPasswordInfo($password);
    }

    /**
     * @When /^I try to register a password confirm of "([^"]*)"$/
     */
    public function iTryToRegisterAPasswordConfirmOf($password) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterConfirmInfo($password);
    }

    /**
     * @When /^I try to register a first name of "([^"]*)"$/
     */
    public function iTryToRegisterAFirstNameOf($firstName) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterFirstNameInfo($firstName);
    }

    /**
     * @When /^I try to register a last name of "([^"]*)"$/
     */
    public function iTryToRegisterALastNameOf($lastName) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterLastNameInfo($lastName);
    }

    /**
     * @When /^I try to register an email of "([^"]*)"$/
     */
    public function iTryToRegisterAnEmailOf($email) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterEmailInfo($email);
    }

    /**
     * @Then /^I see that there is no register option to remember me$/
     */
    public function iSeeThatThereIsNoRegisterOptionToRememberMe() {
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('profile-remember-span'))->isDisplayed());
    }

    /**
     * @Then /^I see an error indicating a bad username$/
     */
    public function iSeeAnErrorIndicatingABadUsername() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::cssSelector('#profile-username + span.glyphicon-remove'))->isDisplayed());
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-username-message'));
        Assert::assertTrue($error->isDisplayed());
        Assert::assertEquals('Your username must be at least 5 characters, and contain only letters numbers and underscores', $error->getText());
    }

    /**
     * @Then /^I see a success icon indicating a good username$/
     */
    public function iSeeASuccessIconIndicatingAGoodUsername() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::cssSelector('#profile-username + span.glyphicon-ok'))->isDisplayed());
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-username-message'));
        Assert::assertFalse($error->isDisplayed());
        Assert::assertEquals('', $error->getText());
    }

    /**
     * @Then /^I see an error indicating a bad password$/
     */
    public function iSeeAnErrorIndicatingABadPassword() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::cssSelector('#profile-password + span.glyphicon-remove'))->isDisplayed());
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-password-message'));
        Assert::assertTrue($error->isDisplayed());
        Assert::assertEquals('A password is required', $error->getText());
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('update-profile-password-strength'))->isDisplayed());
    }

    /**
     * @Then /^I see a success icon indicating a good password$/
     */
    public function iSeeASuccessIconIndicatingAGoodPassword() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::cssSelector('#profile-password + span.glyphicon-ok'))->isDisplayed());
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('update-profile-password-message'))->isDisplayed());
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('update-profile-password-strength'))->isDisplayed());
    }

    /**
     * @Then /^I see an error indicating a bad confirm password$/
     */
    public function iSeeAnErrorIndicatingABadConfirmPassword() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::cssSelector('#profile-confirm-password + span.glyphicon-remove'))->isDisplayed());
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-confirm-password-message'));
        Assert::assertTrue($error->isDisplayed());
        Assert::assertEquals('Your passwords do not match', $error->getText());
    }

    /**
     * @Then /^I see a success icon indicating a good confirm password$/
     */
    public function iSeeASuccessIconIndicatingAGoodConfirmPassword() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::cssSelector('#profile-confirm-password + span.glyphicon-ok'))->isDisplayed());
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-confirm-password-message'));
        Assert::assertFalse($error->isDisplayed());
        Assert::assertEquals('', $error->getText());
    }

    /**
     * @Then /^I see an error indicating a bad first name$/
     */
    public function iSeeAnErrorIndicatingABadFirstName() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::cssSelector('#profile-firstname + span.glyphicon-remove'))->isDisplayed());
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-firstname-message'));
        Assert::assertTrue($error->isDisplayed());
        Assert::assertEquals('A first name is required', $error->getText());
    }

    /**
     * @Then /^I see a success icon indicating a good first name$/
     */
    public function iSeeASuccessIconIndicatingAGoodFirstName() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::cssSelector('#profile-firstname + span.glyphicon-ok'))->isDisplayed());
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-firstname-message'));
        Assert::assertFalse($error->isDisplayed());
        Assert::assertEquals('', $error->getText());
    }

    /**
     * @Then /^I see an error indicating a bad last name$/
     */
    public function iSeeAnErrorIndicatingABadLastName() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::cssSelector('#profile-lastname + span.glyphicon-remove'))->isDisplayed());
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-lastname-message'));
        Assert::assertTrue($error->isDisplayed());
        Assert::assertEquals('A last name is required', $error->getText());
    }

    /**
     * @Then /^I see a success icon indicating a good last name$/
     */
    public function iSeeASuccessIconIndicatingAGoodLastName() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::cssSelector('#profile-lastname + span.glyphicon-ok'))->isDisplayed());
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-lastname-message'));
        Assert::assertFalse($error->isDisplayed());
        Assert::assertEquals('', $error->getText());
    }

    /**
     * @Then /^I see an error indicating a bad email$/
     */
    public function iSeeAnErrorIndicatingABadEmail() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::cssSelector('#profile-email + span.glyphicon-remove'))->isDisplayed());
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-email-message'));
        Assert::assertTrue($error->isDisplayed());
        Assert::assertEquals('A valid email is required', $error->getText());
    }

    /**
     * @Then /^I see a success icon indicating a good email$/
     */
    public function iSeeASuccessIconIndicatingABadEmail() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::cssSelector('#profile-email + span.glyphicon-ok'))->isDisplayed());
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-email-message'));
        Assert::assertFalse($error->isDisplayed());
        Assert::assertEquals('', $error->getText());
    }

    /**
     * @Then /^the register button is disabled$/
     */
    public function theRegisterButtonIsDisabled() {
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('update-profile'))));
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('update-profile'))->isEnabled());
    }

    /**
     * @Then /^I see an error message indicating username already exists$/
     */
    public function iSeeAnErrorMessageIndicatingUsernameAlreadyExists() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::classname('alert-danger')));
        Assert::assertEquals('×
That username already exists in the system', $this->driver->findElement(WebDriverBy::className('alert-danger'))->getText());
    }

    /**
     * @Then /^I see an error message indicating email already exists$/
     */
    public function iSeeAnErrorMessageIndicatingEmailAlreadyExists() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::classname('alert-danger')));
        Assert::assertEquals('×
That email already exists in the system: try logging in with it', $this->driver->findElement(WebDriverBy::className('alert-danger'))->getText());
    }
}