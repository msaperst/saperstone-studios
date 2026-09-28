<?php

namespace ui\models;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;

class Gallery {
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

    /**
     * @param $rows
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForImagesToLoad($rows) {
        $imageCount = (int) $rows * 4;
        $this->wait->until(function () use ($imageCount) {
            return count($this->driver->findElements(WebDriverBy::cssSelector('#album-grid .album-card'))) >= $imageCount;
        });
    }

    /**
     * @param $imgNum
     * @return WebDriverElement
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function hoverOverImage($imgNum): WebDriverElement {
        $this->waitForImagesToLoad((int) ceil($imgNum / 4));
        $image = $this->driver->findElement(
            WebDriverBy::cssSelector("#album-grid .album-card[data-image-id='" . ($imgNum - 1) . "']")
        );
        $this->driver->executeScript("arguments[0].scrollIntoView({block: 'center'});", [$image]);
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($image));

        $actions = new WebDriverActions($this->driver);
        $actions->moveToElement($image)->perform();

        return $image;
    }

    /**
     * @param $imgNum
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function justOpenSlideShow($imgNum) {
        $image = $this->hoverOverImage($imgNum);
        // The card overlay intentionally sits above the protected image media
        // and is the user-facing click target that opens the viewer.
        $image->findElement(WebDriverBy::className('album-card-overlay'))->click();
        $this->wait->until(
            WebDriverExpectedCondition::visibilityOf(
                $this->driver->findElement(WebDriverBy::id('album-viewer-overlay'))
            )
        );
        $this->wait->until(function () use ($imgNum) {
            $active = $this->driver->findElements(WebDriverBy::cssSelector('#album-grid .album-card.is-active'));
            return count($active) === 1
                && $active[0]->getAttribute('data-image-id') === (string) ($imgNum - 1);
        });
    }

    /**
     * @param $imgNum
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function openSlideShow($imgNum) {
        $slideShowId = str_replace(" ", "-", substr($this->driver->findElement(WebDriverBy::tagName('h1'))->getText(), 0, -8));
        $this->justOpenSlideShow($imgNum);
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id($slideShowId))));
    }

    /**
     * @return WebDriverElement
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function getSlideShowImage(): WebDriverElement {
        $this->wait->until(function () {
            $images = $this->driver->findElements(WebDriverBy::cssSelector('#album-grid .album-card.is-active'));
            return count($images) === 1;
        });
        return $this->driver->findElement(WebDriverBy::cssSelector('#album-grid .album-card.is-active'));
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function advanceToNextImage() {
        $current = $this->getSlideShowImage()->getAttribute('data-image-id');
        $this->driver->findElement(WebDriverBy::id('album-next-btn'))->click();
        $this->wait->until(function () use ($current) {
            $active = $this->driver->findElements(WebDriverBy::cssSelector('#album-grid .album-card.is-active'));
            return count($active) === 1 && $active[0]->getAttribute('data-image-id') !== $current;
        });
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function advanceToPreviousImage() {
        $current = $this->getSlideShowImage()->getAttribute('data-image-id');
        $this->driver->findElement(WebDriverBy::id('album-prev-btn'))->click();
        $this->wait->until(function () use ($current) {
            $active = $this->driver->findElements(WebDriverBy::cssSelector('#album-grid .album-card.is-active'));
            return count($active) === 1 && $active[0]->getAttribute('data-image-id') !== $current;
        });
    }

    /**
     * @param $imgNum
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function advanceToImage($imgNum) {
        $img = $this->getSlideShowImage();
        $this->driver->findElement(WebDriverBy::cssSelector('.carousel-indicators > li:nth-child(' . $imgNum . ')'))->click();
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::visibilityOf($img)));
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function closeSlideShow() {
        $overlay = $this->driver->findElement(WebDriverBy::id('album-viewer-overlay'));
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('album-viewer-close')));
        $this->driver->findElement(WebDriverBy::id('album-viewer-close'))->click();
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::visibilityOf($overlay)));
    }
}