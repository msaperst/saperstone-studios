<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Environment\Environment;
use CustomAsserts;
use Exception;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use ui\models\Login;
use User;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Login.php';

class LoginFeatureContext implements Context {

    /**
     * @var Environment
     */
    private $environment;
    /**
     * @var RemoteWebDriver
     */
    private $driver;
    /**
     * @var WebDriverWait
     */
    private $wait;
    /**
     * @var User
     */
    private $user;

    /** @BeforeScenario
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $this->environment = $scope->getEnvironment();
        $this->driver = $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 20);
        $this->user = $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->getUser();
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
     * @throws Exception
     */
    public function anEnabledUserAccountExists() {
        $this->user->create();
    }

    /**
     * @Given an enabled admin user account exists
     * @throws Exception
     */
    public function anEnabledAdminUserAccountExists() {
        $params = [
            'username' => 'testUser',
            'email' => 'saperstonestudios@mailinator.com',
            'password' => '12345',
            'role' => 'admin'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $this->user = User::withParams($params);
        unset($_SESSION['hash']);
        $this->user->create();
        $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->setUser($this->user);
    }

    /**
     * @Given an enabled uploader user account exists
     * @throws Exception
     */
    public function anEnabledUploaderUserAccountExists() {
        $params = [
            'username' => 'testUser',
            'email' => 'saperstonestudios@mailinator.com',
            'password' => '12345',
            'role' => 'uploader'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $this->user = User::withParams($params);
        unset($_SESSION['hash']);
        $this->user->create();
        $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->setUser($this->user);
    }

    /**
     * @Given /^a disabled user account exists$/
     * @throws Exception
     */
    public function aDisabledUserAccountExists() {
        $params = [
            'username' => 'testUser',
            'email' => 'saperstonestudios@mailinator.com',
            'password' => '12345',
            'active' => '0'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $this->user = User::withParams($params);
        unset($_SESSION['hash']);
        $this->user->create();
        $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->setUser($this->user);
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
     * @Given /^I am logged in with admin credentials$/
     */
    public function iAmLoggedInWithAdminCredentials() {
        $this->driver->manage()->deleteCookieNamed('hash');
        $cookie = new Cookie('hash', '1d7505e7f434a7713e84ba399e937191');
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
     * @param $username
     * @param $password
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
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('alert-info')));
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
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('login-menu-item')));
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::linkText($this->user->getUsername()))));
    }

    /**
     * @Then /^I see an info message indicating I successfully logged in$/
     */
    public function iSeeAnInfoMessageIndicatingISuccessfullyLoggedIn() {
        CustomAsserts::infoMessage($this->driver, 'Successfully Logged In. Please wait as you are redirected.');
    }

    /**
     * @Then /^I see an error message indicating my account has been disabled$/
     */
    public function iSeeAnErrorMessageIndicatingMyAccountHasBeenDisabled() {
        CustomAsserts::errorMessage($this->driver, 'Sorry, your account has been deactivated. Please contact our webmaster to get this resolved.');
    }

    /**
     * @Then /^I see an error message indicating my credentials aren't valid$/
     */
    public function iSeeAnErrorMessageIndicatingMyCredentialsArenTValid() {
        CustomAsserts::errorMessage($this->driver, 'Credentials do not match our records');
    }

    /**
     * @Then /^I see an error message indicating all fields need to be filled in$/
     */
    public function iSeeAnErrorMessageIndicatingAllFieldsNeedToBeFilledIn() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('alert-danger')));
        Assert::assertStringEndsWith('can not be blank', $this->driver->findElement(WebDriverBy::className('alert-danger'))->getText());
    }

    /**
     * @Then /^I see an error message indicating invalid field values$/
     */
    public function iSeeAnErrorMessageIndicatingInvalidFieldValues() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('alert-danger')));
        Assert::assertStringEndsWith('is not valid', $this->driver->findElement(WebDriverBy::className('alert-danger'))->getText());
    }

    /**
     * @Then /^I see an error message indicating passwords do not match$/
     */
    public function iSeeAnErrorMessageIndicatingPasswordsDoNotMatch() {
        CustomAsserts::errorMessage($this->driver, 'Password and confirmation do not match');
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