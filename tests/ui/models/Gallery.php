<?php

namespace ui\models;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;

class Gallery {
    private RemoteWebDriver $driver;
    private WebDriverWait $wait;

    public function __construct($driver, $wait) {
        $this->driver = $driver;
        $this->wait = $wait;
    }

    public function waitForImagesToLoad($rows) {
        $imageCount = (int) $rows * 4;
        $this->wait->until(function () use ($imageCount) {
            if (count($this->driver->findElements(WebDriverBy::cssSelector('.image-grid .gallery'))) >= $imageCount) {
                return true;
            }

            $this->driver->executeScript("window.scrollTo(0, document.body.scrollHeight); window.dispatchEvent(new Event('scroll'));");
            return false;
        });
    }

    public function hoverOverImage($imgNum): RemoteWebElement {
        $col = ($imgNum - 1) % 4;
        $row = intdiv($imgNum - 1, 4) + 1;
        $this->waitForImagesToLoad($row);
        $image = $this->driver->findElement(WebDriverBy::cssSelector("#col-$col > div.gallery:nth-child($row)"));
        $this->driver->executeScript("arguments[0].scrollIntoView({block: 'center'});", [$image]);
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($image));
        (new WebDriverActions($this->driver))->moveToElement($image)->perform();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($image->findElement(WebDriverBy::className('info'))));
        return $image;
    }

    public function justOpenSlideShow($imgNum) {
        $this->hoverOverImage($imgNum)->findElement(WebDriverBy::className('info'))->click();
    }

    public function openSlideShow($imgNum) {
        $this->justOpenSlideShow($imgNum);
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id($this->getSlideShowId())));
        $this->waitForActiveImage($imgNum - 1);
    }

    public function getSlideShowImage(): RemoteWebElement {
        $selector = WebDriverBy::cssSelector('#' . $this->getSlideShowId() . ' .carousel-inner .item.active');
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($selector));
        return $this->driver->findElement($selector);
    }

    public function advanceToNextImage() {
        $current = $this->getActiveSequence();
        $this->driver->findElement(WebDriverBy::cssSelector('#' . $this->getSlideShowId() . ' .gallery-next'))->click();
        $this->waitForActiveImage(($current + 1) % $this->getImageCount());
    }

    public function advanceToPreviousImage() {
        $current = $this->getActiveSequence();
        $count = $this->getImageCount();
        $this->driver->findElement(WebDriverBy::cssSelector('#' . $this->getSlideShowId() . ' .gallery-prev'))->click();
        $this->waitForActiveImage(($current - 1 + $count) % $count);
    }

    public function advanceToImage($imgNum) {
        $sequence = $imgNum - 1;
        $this->driver->findElement(WebDriverBy::cssSelector('#' . $this->getSlideShowId() . ' .carousel-indicators > li:nth-child(' . $imgNum . ')'))->click();
        $this->waitForActiveImage($sequence);
    }

    public function closeSlideShow() {
        $slideShowId = $this->getSlideShowId();
        $modal = $this->driver->findElement(WebDriverBy::id($slideShowId));
        $close = WebDriverBy::cssSelector("#$slideShowId [data-dismiss='modal']");
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($close));
        $this->driver->findElement($close)->click();
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::visibilityOf($modal)));
    }

    private function getSlideShowId(): string {
        return str_replace("'", "-", str_replace(" ", "-", substr($this->driver->findElement(WebDriverBy::tagName('h1'))->getText(), 0, -8)));
    }

    private function getActiveSequence(): int {
        return (int) $this->getSlideShowImage()->findElement(WebDriverBy::className('contain'))->getAttribute('sequence');
    }

    private function getImageCount(): int {
        return count($this->driver->findElements(WebDriverBy::cssSelector('#' . $this->getSlideShowId() . ' .carousel-inner .item')));
    }

    private function waitForActiveImage(int $sequence): void {
        $this->wait->until(function () use ($sequence) {
            $active = $this->driver->findElements(WebDriverBy::cssSelector('#' . $this->getSlideShowId() . ' .carousel-inner .item.active .contain'));
            return count($active) === 1 && $active[0]->getAttribute('sequence') === (string) $sequence;
        });
    }
}
