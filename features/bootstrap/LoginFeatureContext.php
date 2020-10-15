<?php

use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Assert;
use ui\models\Login;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseFeatureContext.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'ui' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Login.php';

class LoginFeatureContext extends BaseFeatureContext {

    /**
     * @BeforeScenario
     */
    public function setupUser() {
        $params = [
            'username' => 'testUser',
            'email' => 'test@example.org',
            'password' => '12345'
        ];
        $this->user = User::withParams($params);
    }

    /**
     * @Given /^I have cookies enabled$/
     */
    public function iHaveCookiesEnabled() {
        $cookie = new Cookie('CookiePreferences', '["preferences","analytics"]');
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
            'email' => 'test@example.org',
            'password' => '12345',
            'active' => '0'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $this->user = User::withParams($params);
        unset($_SESSION['hash']);
        $this->user->create();
    }

    /**
     * @Given /^I am logged in with saved credentials$/
     */
    public function iAmLoggedInWithSavedCredentials() {
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
     * @Then I see my user name displayed
     */
    public function iSeeMyUserNameDisplayed() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::linkText($this->user->getUsername()))->isDisplayed());
    }

    /**
     * @Then /^I don't my user name displayed$/
     */
    public function iDonTMyUserNameDisplayed() {
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::linkText($this->user->getUsername()))));
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
     * @Then /^I see that there is no option to remember me$/
     */
    public function iSeeThatThereIsNoOptionToRememberMe() {
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('login-remember'))->isDisplayed());
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
     * @Given /^I don't see a cookie with my credentials$/
     */
    public function iDonTSeeACookieWithMyCredentials() {
        $cookies = $this->driver->manage()->getCookies();
        foreach( $cookies as $cookie) {
            Assert::assertNotEquals('hash', $cookie->getName());
            Assert::assertNotEquals('usr', $cookie->getName());
        }
    }
}