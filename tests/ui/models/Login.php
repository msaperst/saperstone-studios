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

    public function amILoggedIn() {
        if (sizeof($this->driver->findElements(WebDriverBy::id('login-menu-item')))) {
            return false;
        } else {
            return true;
        }
    }

    public function openLogin() {
        $this->driver->findElement(WebDriverBy::id('login-menu-item'))->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('login-modal'))));
    }

    public function loginKeyboard($username, $password, $rememberMe) {
        $this->openLogin();
        if ($rememberMe) {
            $this->driver->findElement(WebDriverBy::id('login-remember'))->click();
        }
        $this->driver->findElement(WebDriverBy::id('login-user'))->sendKeys($username);
        $this->driver->findElement(WebDriverBy::id('login-pass'))->sendKeys($password)->sendKeys(WebDriverKeys::ENTER);
    }

    public function login($username, $password, $rememberMe) {
        $this->openLogin();
        $this->driver->findElement(WebDriverBy::id('login-user'))->sendKeys($username);
        $this->driver->findElement(WebDriverBy::id('login-pass'))->sendKeys($password);
        if ($rememberMe) {
            $this->driver->findElement(WebDriverBy::id('login-remember'))->click();
        }
        $this->driver->findElement(WebDriverBy::id('login-submit'))->click();
    }

    public function logout() {
        $accountMenu = WebDriverBy::xpath(
            "//li[contains(@class,'dropdown')][.//*[@id='logout-button']]/a[contains(@class,'dropdown-toggle')]"
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($accountMenu));
        $this->driver->findElement($accountMenu)->click();

        $logout = WebDriverBy::id('logout-button');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($logout));
        $this->driver->findElement($logout)->click();
        $this->wait->until(
            WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('login-menu-item'))
        );
    }

    public function openResetPassword() {
        $this->openLogin();
        $this->driver->findElement(WebDriverBy::id('login-forgot-password'))->click();
        $this->wait->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('#forgot-password-modal.in #forgot-password-submit')
            )
        );
    }

    public function requestResetKey($email, bool $expectSuccess = true) {
        $this->openResetPassword();

        $emailSelector = WebDriverBy::cssSelector('#forgot-password-modal.in #forgot-password-email');
        $this->setInputValue($emailSelector, (string) $email);

        $submitSelector = WebDriverBy::cssSelector('#forgot-password-modal.in #forgot-password-submit');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($submitSelector));
        $this->driver->findElement($submitSelector)->click();

        if ($expectSuccess) {
            $this->wait->until(
                WebDriverExpectedCondition::visibilityOfElementLocated(
                    WebDriverBy::cssSelector('#forgot-password-modal.in #forgot-password-reset-password')
                )
            );
            return;
        }

        $this->wait->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#forgot-password-modal.in .alert-danger')
            )
        );
    }

    public function requestResetPassword($email, $code, $password, $confirm) {
        $resetSelector = WebDriverBy::cssSelector('#forgot-password-modal.in #forgot-password-reset-password');
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($resetSelector));

        $this->setInputValue(
            WebDriverBy::cssSelector('#forgot-password-modal.in #forgot-password-email'),
            (string) $email
        );
        $this->setInputValue(
            WebDriverBy::cssSelector('#forgot-password-modal.in #forgot-password-code'),
            (string) $code
        );
        $this->setInputValue(
            WebDriverBy::cssSelector('#forgot-password-modal.in #forgot-password-new-password'),
            (string) $password
        );
        $this->setInputValue(
            WebDriverBy::cssSelector('#forgot-password-modal.in #forgot-password-new-password-confirm'),
            (string) $confirm
        );

        $this->driver->findElement($resetSelector)->click();
    }

    private function setInputValue(WebDriverBy $selector, string $value): void {
        $this->wait->until(function () use ($selector, $value) {
            $inputs = $this->driver->findElements($selector);
            if (count($inputs) !== 1 || !$inputs[0]->isDisplayed() || !$inputs[0]->isEnabled()) {
                return false;
            }

            $input = $inputs[0];
            if ($input->getAttribute('value') !== $value) {
                $input->clear();
                if ($value !== '') {
                    $input->sendKeys($value);
                }
            }

            return $input->getAttribute('value') === $value;
        });
    }
}