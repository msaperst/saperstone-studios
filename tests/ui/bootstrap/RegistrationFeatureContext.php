<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use CustomAsserts;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Google\Exception as ExceptionAlias;
use PHPUnit\Framework\Assert;
use Sql;
use ui\models\Registration;
use User;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Registration.php';

class RegistrationFeatureContext implements Context {

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
    private $baseUrl;

    // for test comparisons
    private $password;
    private $firstName;
    private $lastName;
    private $email;
    private $username;

    /** @BeforeScenario
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $this->environment = $scope->getEnvironment();
        $this->driver = $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 10);
        $this->baseUrl = $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->getBaseUrl();
        $this->user = $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->getUser();
    }

    /**
     * @Given /^I am on the registration page$/
     */
    public function iAmOnTheRegistrationPage() {
        $this->driver->get($this->baseUrl . 'register.php');
    }

    /**
     * @Given /^I am on the profile page$/
     */
    public function iAmOnTheProfilePage() {
        $this->driver->get($this->baseUrl . 'user/profile.php');
    }

    /**
     * @When /^I register my user$/
     */
    public function iRegisterMyUser() {
        $register = new Registration($this->driver, $this->wait);
        try {
            $user = $register->registerMyUser($this->user);
            $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->setUser($user);
        } catch (Exception $e) {
            $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->dontDeleteUser();
        }
    }

    /**
     * @When /^I register a user with "([^"]*)", "([^"]*)", "([^"]*)", "([^"]*)", "([^"]*)", "([^"]*)"$/
     * @param $username
     * @param $password
     * @param $confirmPassword
     * @param $firstName
     * @param $lastName
     * @param $email
     */
    public function iRegisterAUserWith($username, $password, $confirmPassword, $firstName, $lastName, $email) {
        $register = new Registration($this->driver, $this->wait);
        try {
            $user = $register->registerAUser($username, $password, $confirmPassword, $firstName, $lastName, $email);
            $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->setUser($user);
        } catch (Exception $e) {
            $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->dontDeleteUser();
        }
    }

    /**
     * @When /^I try to register a user with "([^"]*)", "([^"]*)", "([^"]*)", "([^"]*)", "([^"]*)", "([^"]*)"$/
     * @param $username
     * @param $password
     * @param $confirmPassword
     * @param $firstName
     * @param $lastName
     * @param $email
     */
    public function iTryToRegisterAUserWith($username, $password, $confirmPassword, $firstName, $lastName, $email) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterAUserInfo($username, $password, $confirmPassword, $firstName, $lastName, $email);
    }

    /**
     * @When /^I try to register a username of "([^"]*)"$/
     * @param $username
     */
    public function iTryToRegisterAUsernameOf($username) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterUserNameInfo($username);
    }

    /**
     * @When /^I try to set my password of "([^"]*)"$/
     * @param $password
     */
    public function iTryToSetMyPasswordOf($password) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterCurrentPasswordInfo($password);
    }

    /**
     * @When /^I try to (register|update to) a password of "([^"]*)"$/
     * @param $x
     * @param $password
     */
    public function iTryToRegisterAPasswordOf($x, $password) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterPasswordInfo($password);
    }

    /**
     * @When /^I try to (register|update to) a password confirm of "([^"]*)"$/
     * @param $x
     * @param $password
     */
    public function iTryToRegisterAPasswordConfirmOf($x, $password) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterConfirmInfo($password);
    }

    /**
     * @When /^I try to (register|update to) a first name of "([^"]*)"$/
     * @param $x
     * @param $firstName
     */
    public function iTryToRegisterAFirstNameOf($x, $firstName) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterFirstNameInfo($firstName);
    }

    /**
     * @When /^I try to (register|update to) a last name of "([^"]*)"$/
     * @param $x
     * @param $lastName
     */
    public function iTryToRegisterALastNameOf($x, $lastName) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterLastNameInfo($lastName);
    }

    /**
     * @When /^I try to (register|update to) an email of "([^"]*)"$/
     * @param $x
     * @param $email
     */
    public function iTryToRegisterAnEmailOf($x, $email) {
        $register = new Registration($this->driver, $this->wait);
        $register->enterEmailInfo($email);
    }


    /**
     * @When /^I update my user$/
     */
    public function iUpdateMyUser() {
        // store all off all the new user data, it will be needed
        $this->username = $this->driver->findElement(WebDriverBy::id('profile-username'))->getAttribute('value');
        $this->password = $this->driver->findElement(WebDriverBy::id('profile-password'))->getAttribute('value');
        if ($this->password == "") {
            $this->password = $this->user->getPassword();
        }
        $this->firstName = $this->driver->findElement(WebDriverBy::id('profile-firstname'))->getAttribute('value');
        $this->lastName = $this->driver->findElement(WebDriverBy::id('profile-lastname'))->getAttribute('value');
        $this->email = $this->driver->findElement(WebDriverBy::id('profile-email'))->getAttribute('value');
        // actually update the user
        $register = new Registration($this->driver, $this->wait);
        $register->updateMyUser();
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
     * @Then /^I see an error indicating current password is required$/
     */
    public function iSeeAnErrorIndicatingCurrentPasswordIsRequired() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::cssSelector('#profile-current-password + span.glyphicon-remove'))->isDisplayed());
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-current-password-message'));
        Assert::assertTrue($error->isDisplayed());
        Assert::assertEquals('Please confirm old password to set new password', $error->getText());
    }

    /**
     * @Then /^I see a success icon indicating a good current password$/
     */
    public function iSeeASuccessIconIndicatingAGoodCurrentPassword() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::cssSelector('#profile-current-password + span.glyphicon-ok'))->isDisplayed());
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-current-password-message'));
        Assert::assertFalse($error->isDisplayed());
        Assert::assertEquals('', $error->getText());
    }

    /**
     * @Then /^I see a no icon for current password$/
     */
    public function iSeeANoIconForCurrentPassword() {
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::cssSelector('#profile-current-password + span.glyphicon-ok'))));
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::cssSelector('#profile-current-password + span.glyphicon-remove'))));
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-current-password-message'));
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
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('update-profile-password-strength'))));
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
     * @Then /^I see a no icon for password$/
     */
    public function iSeeANoIconForPassword() {
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::cssSelector('#profile-password + span.glyphicon-ok'))));
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::cssSelector('#profile-password + span.glyphicon-remove'))));
        $error = $this->driver->findElement(WebDriverBy::id('update-profile-password-message'));
        Assert::assertFalse($error->isDisplayed());
        Assert::assertEquals('', $error->getText());
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('update-profile-password-strength'))));
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
     * @Then /^I see a no icon for confirm password$/
     */
    public function iSeeANoIconForConfirmPassword() {
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::cssSelector('#profile-confirm-password + span.glyphicon-ok'))));
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::cssSelector('#profile-confirm-password + span.glyphicon-remove'))));
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
     * @Then /^the (register|update) button is disabled$/
     * @throws Exception
     */
    public function theRegisterButtonIsDisabled() {
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('update-profile'))));
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('update-profile'))->isEnabled());
    }

    /**
     * @Then /^I see an error message indicating username already exists$/
     */
    public function iSeeAnErrorMessageIndicatingUsernameAlreadyExists() {
        CustomAsserts::errorMessage($this->driver, 'That username already exists in the system');
    }

    /**
     * @Then /^I see an error message indicating email already exists$/
     */
    public function iSeeAnErrorMessageIndicatingEmailAlreadyExists() {
        CustomAsserts::errorMessage($this->driver, 'That email already exists in the system: try logging in with it');
    }

    /**
     * @Then /^I see an error message indicating wrong password provided$/
     */
    public function iSeeAnErrorMessageIndicatingWrongPasswordProvided() {
        CustomAsserts::errorMessage($this->driver, 'Current password does not match our records');
    }

    /**
     * @Then /^the username field is disabled$/
     */
    public function theUsernameFieldIsDisabled() {
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('profile-username'))->isEnabled());
    }

    /**
     * @Then /^I see a success message indicating my user was updated$/
     */
    public function iSeeASuccessMessageIndicatingMyUserWasUpdated() {
        CustomAsserts::successMessage($this->driver, 'Your profile information was successfully updated.');
    }

    /**
     * @Given /^my user information is updated$/
     */
    public function myUserInformationIsUpdated() {
        $sql = new Sql();
        $userDetails = $sql->getRow("SELECT * FROM `users` WHERE `users`.`id` = {$this->user->getId()};");
        Assert::assertEquals($this->username, $userDetails['usr']);
        Assert::assertEquals(md5($this->password), $userDetails['pass']);
        Assert::assertEquals($this->firstName, $userDetails['firstName']);
        Assert::assertEquals($this->lastName, $userDetails['lastName']);
        Assert::assertEquals($this->email, $userDetails['email']);
        $sql->disconnect();
    }

    /**
     * @Then /^I receive a welcome email$/
     * @throws ExceptionAlias
     */
    public function iReceiveAWelcomeEmail() {
        CustomAsserts::assertEmailEquals('Thank you for Registering with Saperstone Studios',
            'Congratulations for registering an account with Saperstone Studios. You can login and access the site at https://saperstonestudios.com.',
            "<html><body>Congratulations for registering an account with Saperstone Studios. You can login and access the site at <a href='https://saperstonestudios.com'>saperstonestudios.com</a>.</body></html>");

    }
}