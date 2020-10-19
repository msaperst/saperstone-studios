<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;

class RetouchFeatureContext implements Context {

    private $driver;
    private $wait;
    private $baseUrl;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 10);
        $this->baseUrl = $environment->getContext('BaseFeatureContext')->getBaseUrl();
    }

    /**
     * @Given /^I am on the wedding retouch page$/
     */
    public function iAmOnTheWeddingRetouchPage() {
        $this->driver->get($this->baseUrl . 'wedding/retouch.php');
    }

    /**
     * @When /^I select the "([^"]*)" retouched thumbnail$/
     */
    public function iSelectTheRetouchedThumbnail($ord) {
        $thumbs = $this->driver->findElements(WebDriverBy::className('col-lg-1'));
        $thumbs[intval($ord) - 1]->click();
    }

    /**
     * @When /^I move the slider to (\d+)%$/
     */
    public function iMoveTheSliderTo($width) {
        $slider = $this->driver->findElement(WebDriverBy::name('slider'));
        $sliderWidth = $slider->getSize()->getWidth();
        $move = new WebDriverActions($this->driver);
        $move->moveToElement($slider, intval(($sliderWidth * $width / 100) - ($sliderWidth * .5)))->click()->perform();
//        $slider->click();
//        $move->dragAndDropBy($slider, , 0)->perform();
    }

    /**
     * @Then /^I see initial retouch instructions$/
     */
    public function iSeeInitialRetouchInstructions() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('instructions'))->isDisplayed());
    }

    /**
     * @Then /^I see thumbnails of each retouched image$/
     */
    public function iSeeThumbnailsOfEachRetouchedImage() {
        $source = $this->driver->getPageSource();
        $count = preg_match_all("/images\[\d+\]/", $source);
        Assert::assertEquals($count, sizeof($this->driver->findElements(WebDriverBy::className('col-lg-1'))));
    }

    /**
     * @Then /^I see the "([^"]*)" original image$/
     */
    public function iSeeTheOriginalImage($ord) {
        $thumbs = $this->driver->findElements(WebDriverBy::className('col-lg-1'));
        $origImg = $thumbs[intval($ord) - 1]->findElement(WebDriverBy::tagName('img'))->getAttribute('imgorig');
        $img = $this->driver->findElement(WebDriverBy::id('original'));
        Assert::assertTrue($img->isDisplayed());
        Assert::assertStringEndsWith($origImg, $img->findElement(WebDriverBy::tagName('img'))->getAttribute('src'));
    }

    /**
     * @Then /^I see (\d+)% of the "([^"]*)" retouched image$/
     */
    public function iSeeOfTheRetouchedImage($width, $ord) {
        $thumbs = $this->driver->findElements(WebDriverBy::className('col-lg-1'));
        $editImg = $thumbs[intval($ord) - 1]->findElement(WebDriverBy::tagName('img'))->getAttribute('imgedit');
        $img = $this->driver->findElement(WebDriverBy::id('edit'));
        if ($width > 0) {
            Assert::assertTrue($img->isDisplayed());
        }
        Assert::assertContains("width: $width%", $img->getAttribute('style'));
        Assert::assertStringEndsWith($editImg, $img->findElement(WebDriverBy::tagName('img'))->getAttribute('src'));
    }

    /**
     * @Then /^I see the "([^"]*)" image comment$/
     */
    public function iSeeTheImageComment($ord) {
        $thumbs = $this->driver->findElements(WebDriverBy::className('col-lg-1'));
        $comment = $thumbs[intval($ord) - 1]->findElement(WebDriverBy::tagName('img'))->getAttribute('text');
        Assert::assertEquals(str_replace("  ", " ", $comment), $this->driver->findElement(WebDriverBy::className('comment'))->getText());
    }
}