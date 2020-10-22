<?php

namespace ui\models;

use Facebook\WebDriver\WebDriverBy;
use User;

class Registration {
    private $driver;
    private $wait;

    public function __construct($driver, $wait) {
        $this->driver = $driver;
        $this->wait = $wait;
    }

    public function enterUserNameInfo($username) {
        $this->driver->findElement(WebDriverBy::id('profile-username'))->clear();
        $this->driver->findElement(WebDriverBy::id('profile-username'))->sendKeys($username);
    }

    public function enterPasswordInfo($password) {
        $this->driver->findElement(WebDriverBy::id('profile-password'))->clear();
        $this->driver->findElement(WebDriverBy::id('profile-password'))->sendKeys($password);
    }

    public function enterConfirmInfo($password) {
        $this->driver->findElement(WebDriverBy::id('profile-confirm-password'))->clear();
        $this->driver->findElement(WebDriverBy::id('profile-confirm-password'))->sendKeys($password);
    }

    public function enterFirstNameInfo($firstName) {
        $this->driver->findElement(WebDriverBy::id('profile-firstname'))->clear();
        $this->driver->findElement(WebDriverBy::id('profile-firstname'))->sendKeys($firstName);
    }

    public function enterLastNameInfo($lastName) {
        $this->driver->findElement(WebDriverBy::id('profile-lastname'))->clear();
        $this->driver->findElement(WebDriverBy::id('profile-lastname'))->sendKeys($lastName);
    }

    public function enterEmailInfo($email) {
        $this->driver->findElement(WebDriverBy::id('profile-email'))->clear();
        $this->driver->findElement(WebDriverBy::id('profile-email'))->sendKeys($email);
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
}