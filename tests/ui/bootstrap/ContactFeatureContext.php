<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Exception;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use Sql;

class ContactFeatureContext implements Context {
    /**
     * @var RemoteWebDriver
     */
    private $driver;
    /**
     * @var WebDriverWait
     */
    private $wait;
    private $baseUrl;

    /** @BeforeScenario
     * @param BeforeScenarioScope $scope
     * @throws Exception
     */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 10);
        $this->baseUrl = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getBaseUrl();
    }

    /**
     * @When /^I provide "([^"]*)" for the contact "([^"]*)"$/
     */
    public function iProvideForTheContact($value, $field) {
        $this->driver->findElement(WebDriverBy::id($field))->clear()->sendKeys($value);
    }

    /**
     * @Given /^I submit the contact form$/
     */
    public function iSubmitTheContactForm() {
        $this->driver->findElement(WebDriverBy::id('submit-contact-form'))->click();
    }

    /**
     * @Then /^I see an error indicating contact name is required$/
     */
    public function iSeeAnErrorIndicatingContactNameIsRequired() {
        Assert::assertEquals('Please enter your name.', $this->driver->findElement(WebDriverBy::cssSelector('#name + p li'))->getText(), $this->driver->findElement(WebDriverBy::cssSelector('#name + p li'))->getText());
    }

    /**
     * @Then /^I see an error indicating contact number is required$/
     */
    public function iSeeAnErrorIndicatingContactNumberIsRequired() {
        Assert::assertEquals('Please enter your phone number.', $this->driver->findElement(WebDriverBy::cssSelector('#phone + div li'))->getText(), $this->driver->findElement(WebDriverBy::cssSelector('#phone + div li'))->getText());
    }

    /**
     * @Then /^I see an error indicating contact email is required$/
     */
    public function iSeeAnErrorIndicatingContactEmailIsRequired() {
        Assert::assertEquals('Please enter your email address.', $this->driver->findElement(WebDriverBy::cssSelector('#email + div li'))->getText(), $this->driver->findElement(WebDriverBy::cssSelector('#email + div li'))->getText());
    }

    /**
     * @Then /^I see an error indicating contact email is invalid$/
     */
    public function iSeeAnErrorIndicatingContactEmailIsInvalid() {
        Assert::assertEquals('Not a valid email address', $this->driver->findElement(WebDriverBy::cssSelector('#email + div li'))->getText(), $this->driver->findElement(WebDriverBy::cssSelector('#email + div li'))->getText());
    }

    /**
     * @Then /^I see an error indicating contact message is required$/
     */
    public function iSeeAnErrorIndicatingContactMessageIsRequired() {
        Assert::assertEquals('Please enter your message', $this->driver->findElement(WebDriverBy::cssSelector('#message + div li'))->getText(), $this->driver->findElement(WebDriverBy::cssSelector('#message + div li'))->getText());
    }

    /**
     * @Then /^I see a warning message indicating my message is being sent$/
     */
    public function iSeeAWarningMessageIndicatingMyMessageIsBeingSent() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::classname('alert-warning')));
        Assert::assertEquals('×
Sending your message.', $this->driver->findElement(WebDriverBy::className('alert-warning'))->getText(), '"' . $this->driver->findElement(WebDriverBy::className('alert-warning'))->getText() . '"');
    }

    /**
     * @Given /^the submit contact button is disabled$/
     */
    public function theSubmitContactButtonIsDisabled() {
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('submit-contact-form'))));
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('submit-contact-form'))->isEnabled());
    }

    /**
     * @Then /^I see a success message indicating my message was sent$/
     */
    public function iSeeASuccessMessageIndicatingMyMessageWasSent() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::classname('alert-success')));
        Assert::assertEquals('×
Your message has been sent.', $this->driver->findElement(WebDriverBy::className('alert-success'))->getText(), $this->driver->findElement(WebDriverBy::className('alert-success'))->getText());
    }
}