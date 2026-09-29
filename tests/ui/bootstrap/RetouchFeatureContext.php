<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Assert;

class RetouchFeatureContext implements Context {

    /**
     * @var RemoteWebDriver
     */
    private $driver;
    private $baseUrl;

    /** @BeforeScenario
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getDriver();
        $this->baseUrl = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getBaseUrl();
    }

    /**
     * @Given /^I am on the wedding retouch page$/
     */
    public function iAmOnTheWeddingRetouchPage() {
        $this->driver->get($this->baseUrl . 'wedding/retouch.php');
    }

    /**
     * @When /^I select the "([^"]*)" retouched thumbnail$/
     * @param $ord
     */
    public function iSelectTheRetouchedThumbnail($ord) {
        $thumbs = $this->driver->findElements(WebDriverBy::className('col-lg-1'));
        $thumbs[intval($ord) - 1]->click();
    }

    /**
     * @When /^I move the slider to (\d+)%$/
     * @param $width
     */
    public function iMoveTheSliderTo($width) {
        $slider = $this->driver->findElement(WebDriverBy::name('slider'));
        $sliderWidth = $slider->getSize()->getWidth();
        $move = new WebDriverActions($this->driver);
        $move->moveToElement($slider, intval(($sliderWidth * $width / 100) - ($sliderWidth * .5)))->click()->perform();
        $this->waitForRetouchWidth((int) $width);
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
        Assert::assertCount(
            count($this->getConfiguredImages()),
            $this->driver->findElements(WebDriverBy::className('col-lg-1'))
        );
    }

    /**
     * @Then /^I see the "([^"]*)" original image$/
     * @param $ord
     */
    public function iSeeTheOriginalImage($ord) {
        $origImg = $this->getConfiguredImages()[intval($ord) - 1]['orig'];
        $img = $this->driver->findElement(WebDriverBy::id('original'));
        Assert::assertTrue($img->isDisplayed());
        Assert::assertStringEndsWith($origImg, $img->findElement(WebDriverBy::tagName('img'))->getAttribute('src'));
    }

    /**
     * @Then /^I see (\d+)% of the "([^"]*)" retouched image$/
     * @param $width
     * @param $ord
     */
    public function iSeeOfTheRetouchedImage($width, $ord) {
        $editImg = $this->getConfiguredImages()[intval($ord) - 1]['edit'];
        $img = $this->driver->findElement(WebDriverBy::id('edit'));
        if ($width > 0) {
            Assert::assertTrue($img->isDisplayed());
        }
        Assert::assertStringContainsString("width: $width%", $img->getAttribute('style'));
        Assert::assertStringEndsWith($editImg, $img->findElement(WebDriverBy::tagName('img'))->getAttribute('src'));
    }

    /**
     * @Then /^I see the "([^"]*)" image comment$/
     * @param $ord
     */
    public function iSeeTheImageComment($ord) {
        $comment = $this->getConfiguredImages()[intval($ord) - 1]['text'];
        Assert::assertEquals(str_replace("  ", " ", $comment), $this->driver->findElement(WebDriverBy::className('comment'))->getText());
    }

    private function waitForRetouchWidth(int $width): void {
        $this->wait->until(function () use ($width) {
            $style = $this->driver->findElement(WebDriverBy::id('edit'))->getAttribute('style');
            return str_contains($style, "width: $width%");
        });
    }

    private function getConfiguredImages(): array {
        $json = $this->driver->findElement(WebDriverBy::id('retouch-config'))->getAttribute('data-images');
        $images = json_decode($json, true);
        Assert::assertIsArray($images, 'Retouch image configuration should contain valid JSON.');
        return $images;
    }
}