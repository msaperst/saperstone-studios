<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use ui\models\Login;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseFeatureContext.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Login.php';

class LoginFeatureContext implements Context {

    private $environment;

    private $driver;
    private $user;
    private $wait;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $this->environment = $scope->getEnvironment();
        $this->driver = $this->environment->getContext('BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 20);
        $this->user = $this->environment->getContext('BaseFeatureContext')->getUser();
    }

    /**
     * @Given /^I have cookies disabled$/
     */
    public function iHaveCookiesDisabled() {
        $this->driver->manage()->deleteAllCookies();
        $cookie = new Cookie('CookiePreferences', '[]');
        $this->driver->manage()->addCookie($cookie);
        $this->driver->navigate()->refresh();
    }

    /**
     * @Given an enabled user account exists
     */
    public function anEnabledUserAccountExists() {
        $this->user->create();
    }

    /**
     * @Given /^a disabled user account exists$/
     */
    public function aDisabledUserAccountExists() {
        $params = [
            'username' => 'testUser',
            'email' => 'msaperst+sstest@gmail.com',
            'password' => '12345',
            'active' => '0'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $this->user = User::withParams($params);
        unset($_SESSION['hash']);
        $this->environment->getContext('BaseFeatureContext')->setUser($this->user);
        $this->user->create();
    }

    /**
     * @Given /^I am logged in with saved credentials$/
     */
    public function iAmLoggedInWithSavedCredentials() {
        $this->driver->manage()->deleteCookieNamed('hash');
        $cookie = new Cookie('hash', $this->user->getHash());
        $this->driver->manage()->addCookie($cookie);
        $this->driver->navigate()->refresh();
    }


    /**
     * @When /^I try to login to the site$/
     */
    public function iTryToLoginToTheSite() {
        $login = new Login($this->driver, $this->wait);
        $login->openLogin();
    }

    /**
     * @When I log in to the site
     */
    public function iLogInToTheSite() {
        $login = new Login($this->driver, $this->wait);
        $login->login($this->user->getUsername(), $this->user->getPassword(), false);
    }

    /**
     * @When /^I log in to the site by typing$/
     */
    public function iLogInToTheSiteByTyping() {
        $login = new Login($this->driver, $this->wait);
        $login->loginKeyboard($this->user->getUsername(), $this->user->getPassword(), false);
    }

    /**
     * @When /^I stay logged in to the site$/
     */
    public function iStayLoggedInToTheSite() {
        $login = new Login($this->driver, $this->wait);
        $login->login($this->user->getUsername(), $this->user->getPassword(), true);
    }

    /**
     * @When /^I log in to the site using credentials "([^"]*)" "([^"]*)"$/
     */
    public function iLogInToTheSiteUsingCredentials($username, $password) {
        $login = new Login($this->driver, $this->wait);
        $login->login($username, $password, false);
    }

    /**
     * @When /^I logout$/
     */
    public function iLogout() {
        $login = new Login($this->driver, $this->wait);
        $login->logout($this->user->getUsername());
    }

    /**
     * @When /^I request a reset key$/
     */
    public function iRequestAResetKey() {
        $login = new Login($this->driver, $this->wait);
        $login->requestResetKey($this->user->getEmail());
    }

    /**
     * @When /^I have a reset key$/
     */
    public function iHaveAResetKey() {
        $login = new Login($this->driver, $this->wait);
        $login->openResetPassword();
        $this->driver->findElement(WebDriverBy::id('forgot-password-prev-code'))->click();
    }

    /**
     * @When /^I submit email "([^"]*)" for reset$/
     */
    public function iSubmitEmailForReset($email) {
        $login = new Login($this->driver, $this->wait);
        $login->requestResetKey($email);
    }

    /**
     * @When /^I submit reset credentials$/
     */
    public function iSubmitInNewCredentials() {
        $login = new Login($this->driver, $this->wait);
        $login->requestResetPassword($this->user->getEmail(), User::withId($this->user->getId())->getDataBasic()['resetKey'], $this->user->getPassword(), $this->user->getPassword());
    }

    /**
     * @When /^I submit "([^"]*)" "([^"]*)" "([^"]*)" "([^"]*)" reset credentials$/
     */
    public function iSubmitCredentials($email, $code, $password, $confirm) {
        $login = new Login($this->driver, $this->wait);
        $login->requestResetPassword($email, $code, $password, $confirm);
    }

    /**
     * @Then I see my user name displayed
     */
    public function iSeeMyUserNameDisplayed() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::linkText($this->user->getUsername())));
        Assert::assertTrue($this->driver->findElement(WebDriverBy::linkText($this->user->getUsername()))->isDisplayed());
    }

    /**
     * @Then /^I don't see my user name displayed$/
     */
    public function iDonTSeeMyUserNameDisplayed() {
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::linkText($this->user->getUsername()))));
    }

    /**
     * @Then /^I see an info message indicating I successfully logged in$/
     */
    public function iSeeAnInfoMessageIndicatingISuccessfullyLoggedIn() {
        Assert::assertEquals('×
Successfully Logged In. Please wait as you are redirected.', $this->driver->findElement(WebDriverBy::className('alert-info'))->getText());
    }

    /**
     * @Then /^I see an error message indicating my account has been disabled$/
     */
    public function iSeeAnErrorMessageIndicatingMyAccountHasBeenDisabled() {
        Assert::assertEquals('×
Sorry, your account has been deactivated. Please contact our webmaster to get this resolved.', $this->driver->findElement(WebDriverBy::className('alert-danger'))->getText());
    }

    /**
     * @Then /^I see an error message indicating my credentials aren't valid$/
     */
    public function iSeeAnErrorMessageIndicatingMyCredentialsArenTValid() {
        Assert::assertEquals('×
Credentials do not match our records', $this->driver->findElement(WebDriverBy::className('alert-danger'))->getText());
    }

    /**
     * @Then /^I see an error message indicating all fields need to be filled in$/
     */
    public function iSeeAnErrorMessageIndicatingAllFieldsNeedToBeFilledIn() {
        Assert::assertStringEndsWith('can not be blank', $this->driver->findElement(WebDriverBy::className('alert-danger'))->getText());
    }

    /**
     * @Then /^I see an error message indicating invalid field values$/
     */
    public function iSeeAnErrorMessageIndicatingInvalidFieldValues() {
        Assert::assertStringEndsWith('is not valid', $this->driver->findElement(WebDriverBy::className('alert-danger'))->getText());
    }

    /**
     * @Then /^I see an error message indicating passwords do not match$/
     */
    public function iSeeAnErrorMessageIndicatingPasswordsDoNotMatch() {
        Assert::assertEquals('×
Password and confirmation do not match', $this->driver->findElement(WebDriverBy::className('alert-danger'))->getText());
    }

    /**
     * @Then /^I see that there is no logon option to remember me$/
     */
    public function iSeeThatThereIsNoOptionToRememberMe() {
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('login-remember-span'))->isDisplayed());
    }

    /**
     * @Then /^I see a cookie with my credentials$/
     */
    public function iSeeACookieWithMyCredentials() {
        $hash = $this->driver->manage()->getCookieNamed('hash');
        Assert::assertNotNull($hash);
        Assert::assertEquals($this->user->getHash(), $hash->getValue());
        $usr = $this->driver->manage()->getCookieNamed('usr');
        Assert::assertNotNull($usr);
        Assert::assertEquals($this->user->getUsername(), $usr->getValue());
    }

    /**
     * @Then /^I don't see a cookie with my credentials$/
     */
    public function iDonTSeeACookieWithMyCredentials() {
        $cookies = $this->driver->manage()->getCookies();
        foreach ($cookies as $cookie) {
            Assert::assertNotEquals('hash', $cookie->getName());
            Assert::assertNotEquals('usr', $cookie->getName());
        }
    }

    /**
     * @Then /^I can enter in new credentials$/
     */
    public function iCanEnterInNewCredentials() {
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('forgot-password-reset-password'))));
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('forgot-password-code'))->isDisplayed());
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('forgot-password-new-password'))->isDisplayed());
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('forgot-password-new-password-confirm'))->isDisplayed());
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('forgot-password-remember-span'))->isDisplayed());
    }

    /**
     * @Then /^I see that there is no reset option to remember me$/
     */
    public function iSeeThatThereIsNoResetOptionToRememberMe() {
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('forgot-password-remember-span'))->isDisplayed());
    }
}