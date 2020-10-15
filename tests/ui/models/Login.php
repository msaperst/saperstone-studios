<?php


namespace ui\models;


use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class Login {
    private $driver;
    private $wait;

    public function __construct($driver, $wait) {
        $this->driver = $driver;
        $this->wait = $wait;
    }

    public function openLogin() {
        $this->driver->findElement(WebDriverBy::id('login-menu-item'))->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('login-modal'))));
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
}