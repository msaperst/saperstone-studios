<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use coverage\integration\EmailIntegrationTest;
use CustomAsserts;
use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Google\Exception as ExceptionAlias;
use PHPUnit\Framework\Assert;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';

class ContactFeatureContext implements Context {
    /**
     * @var RemoteWebDriver
     */
    private $driver;
    /**
     * @var WebDriverWait
     */
    private $wait;

    /** @BeforeScenario
     * @param BeforeScenarioScope $scope
     * @throws Exception
     */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 10);
    }

    /**
     * @When /^I provide "([^"]*)" for the contact "([^"]*)"$/
     * @param $value
     * @param $field
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
     * @throws Exception
     */
    public function iSeeAWarningMessageIndicatingMyMessageIsBeingSent() {
        CustomAsserts::warningMessage($this->driver, 'Sending your message.');
    }

    /**
     * @Given /^the submit contact button is disabled$/
     * @throws Exception
     */
    public function theSubmitContactButtonIsDisabled() {
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('submit-contact-form'))));
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('submit-contact-form'))->isEnabled());
    }

    /**
     * @Then /^I see a success message indicating my message was sent$/
     * @throws Exception
     */
    public function iSeeASuccessMessageIndicatingMyMessageWasSent() {
        CustomAsserts::successMessage($this->driver, 'Your message has been sent.');
    }

    /**
     * @Then /^I see a contact email sent to the user$/
     * @throws ExceptionAlias
     */
    public function iSeeAContactEmailSentToTheUser() {
        CustomAsserts::assertEmailEquals('Thank you for contacting Saperstone Studios', 'Thank you for contacting Saperstone Studios. We will respond to your request as soon as we are able to. We are typically able to get back to you within 24 hours.', '<html><body>Thank you for contacting Saperstone Studios. We will respond to your request as soon as we are able to. We are typically able to get back to you within 24 hours.</body></html>');
    }

    /**
     * @Then /^I see a contact email send to the admin with:$/
     * @param TableNode $table
     * @throws ExceptionAlias
     */
    public function iSeeAContactEmailSendToTheAdminWith(TableNode $table) {
        $data = $table->getRow(1);
        CustomAsserts::assertEmailMatches('Saperstone Studios Contact Form: Max', "This is an automatically generated message from Saperstone Studios
Name: {$data[0]}
Phone: {$data[1]}
Email: {$data[2]}
Location: %s (use %d.%d.%d.%d to manually lookup)
Browser: %s %s
Resolution: %dx%d
OS: %s
Full UA: %s

\t\t{$data[3]}", "<html><body>This is an automatically generated message from Saperstone Studios<br/><strong>Name</strong>: {$data[0]}<br/><strong>Phone</strong>: {$data[1]}<br/><strong>Email</strong>: <a href='mailto:{$data[2]}'>{$data[2]}</a><br/><strong>Location</strong>: %s (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$data[3]}<br/><br/></body></html>");
    }
}