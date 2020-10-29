<?php

namespace ui\models;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverWait;
use User;

class Registration {
    /**
     * @var RemoteWebDriver
     */
    private $driver;
    /**
     * @var WebDriverWait
     */
    private $wait;

    public function __construct($driver, $wait) {
        $this->driver = $driver;
        $this->wait = $wait;
    }

    public function enterUserNameInfo($username) {
        $this->driver->findElement(WebDriverBy::id('profile-username'))
            ->clear()
            ->sendKeys($username);
    }

    public function enterCurrentPasswordInfo($password) {
        $this->driver->findElement(WebDriverBy::id('profile-current-password'))
            ->clear()
            ->sendKeys($password);
    }

    public function enterPasswordInfo($password) {
        $this->driver->findElement(WebDriverBy::id('profile-password'))
            ->clear()
            ->sendKeys($password);
    }

    public function enterConfirmInfo($password) {
        $this->driver->findElement(WebDriverBy::id('profile-confirm-password'))
            ->clear()
            ->sendKeys($password);
    }

    public function enterFirstNameInfo($firstName) {
        $this->driver->findElement(WebDriverBy::id('profile-firstname'))
            ->clear()
            ->sendKeys($firstName)
            ->sendKeys(WebDriverKeys::TAB);
    }

    public function enterLastNameInfo($lastName) {
        $this->driver->findElement(WebDriverBy::id('profile-lastname'))
            ->clear()
            ->sendKeys($lastName)
            ->sendKeys(WebDriverKeys::TAB);
    }

    public function enterEmailInfo($email) {
        $this->driver->findElement(WebDriverBy::id('profile-email'))
            ->clear()
            ->sendKeys(WebDriverKeys::BACKSPACE)
            ->sendKeys($email);
    }

    public function enterMyUserInfo(User $user) {
        $this->enterUserNameInfo($user->getUsername());
        $this->enterPasswordInfo($user->getPassword());
        $this->enterConfirmInfo($user->getPassword());
        $this->enterFirstNameInfo($user->getFirstName());
        $this->enterLastNameInfo($user->getLastName());
        $this->enterEmailInfo($user->getEmail());
    }

    public function registerMyUser(User $user) {
        $this->enterMyUserInfo($user);
        $this->driver->findElement(WebDriverBy::id('update-profile'))->click();
        // waiting for the next page to load, so we can ensure the user is created
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::linkText($user->getUsername())));
        //need to save off this user key
        return User::fromEmail($user->getEmail());
    }

    public function enterAUserInfo($username, $password, $confirmPassword, $firstName, $lastName, $email) {
        $this->enterUserNameInfo($username);
        $this->enterPasswordInfo($password);
        $this->enterConfirmInfo($confirmPassword);
        $this->enterFirstNameInfo($firstName);
        $this->enterLastNameInfo($lastName);
        $this->enterEmailInfo($email);
    }

    public function registerAUser($username, $password, $confirmPassword, $firstName, $lastName, $email) {
        $this->enterAUserInfo($username, $password, $confirmPassword, $firstName, $lastName, $email);
        $this->driver->findElement(WebDriverBy::id('update-profile'))->click();
        //need to save off this user key
        return User::fromLogin($username, $password);
    }

    public function updateUserInfo($currentPassword, $password, $confirmPassword, $firstName, $lastName, $email) {
        $this->enterCurrentPasswordInfo($currentPassword);
        $this->enterPasswordInfo($password);
        $this->enterConfirmInfo($confirmPassword);
        $this->enterFirstNameInfo($firstName);
        $this->enterLastNameInfo($lastName);
        $this->enterEmailInfo($email);
    }

    public function updateMyUser() {
        $this->driver->findElement(WebDriverBy::id('update-profile'))->click();
    }
}