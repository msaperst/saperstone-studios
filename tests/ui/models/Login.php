<?php

namespace ui\models;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverWait;

class Login {
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

    public function openLogin() {
        $this->driver->findElement(WebDriverBy::id('login-menu-item'))->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('login-modal'))));
    }

    public function loginKeyboard($username, $password, $rememberMe) {
        $this->openLogin();
        if( $rememberMe ) {
            $this->driver->findElement(WebDriverBy::id('login-remember'))->click();
        }
        $this->driver->findElement(WebDriverBy::id('login-user'))->sendKeys($username);
        $this->driver->findElement(WebDriverBy::id('login-pass'))->sendKeys($password)->sendKeys(WebDriverKeys::ENTER);
    }

    public function login($username, $password, $rememberMe) {
        $this->openLogin();
        $this->driver->findElement(WebDriverBy::id('login-user'))->sendKeys($username);
        $this->driver->findElement(WebDriverBy::id('login-pass'))->sendKeys($password);
        if( $rememberMe ) {
            $this->driver->findElement(WebDriverBy::id('login-remember'))->click();
        }
        $this->driver->findElement(WebDriverBy::id('login-submit'))->click();
    }

    public function logout($username) {
        $this->driver->findElement(WebDriverBy::linkText($username))->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('logout-button'))));
        $this->driver->findElement(WebDriverBy::id('logout-button'))->click();
    }

    public function openResetPassword() {
        $this->openLogin();
        $this->driver->findElement(WebDriverBy::id('login-forgot-password'))->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('forgot-password-submit'))));
    }

    public function requestResetKey($email) {
        $this->openResetPassword();
        $this->driver->findElement(WebDriverBy::id('forgot-password-email'))->sendKeys($email);
        $this->driver->findElement(WebDriverBy::id('forgot-password-submit'))->click();
    }

    public function requestResetPassword($email, $code, $password, $confirm) {
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('forgot-password-reset-password'))));
        $this->driver->findElement(WebDriverBy::id('forgot-password-email'))->clear();
        $this->driver->findElement(WebDriverBy::id('forgot-password-email'))->sendKeys($email);
        $this->driver->findElement(WebDriverBy::id('forgot-password-code'))->sendKeys($code);
        $this->driver->findElement(WebDriverBy::id('forgot-password-new-password'))->sendKeys($password);
        $this->driver->findElement(WebDriverBy::id('forgot-password-new-password-confirm'))->sendKeys($confirm);
        $this->driver->findElement(WebDriverBy::id('forgot-password-reset-password'))->click();
    }
}