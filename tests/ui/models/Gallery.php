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
        // using times two due to the extra row for sharing
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.image-grid > div > div.gallery:nth-child(' . $rows . ')')));
    }

    /**
     * @param $imgNum
     * @return WebDriverElement
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function hoverOverImage($imgNum): WebDriverElement {
        $col = ($imgNum - 1) % 4;
        $row = intval(($imgNum - 1) / 4);
        $this->waitForImagesToLoad($row + 1);
        $ourGalleryImage = $this->driver->findElement(WebDriverBy::cssSelector("#col-$col > div.gallery:nth-child(" . ($row + 1) . ")"));
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($ourGalleryImage));
        $actions = new WebDriverActions($this->driver);
        $actions->moveToElement($ourGalleryImage)->perform();
        $ourGalleryImageInfo = $ourGalleryImage->findElement(WebDriverBy::className('info'));
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($ourGalleryImageInfo));
        return $ourGalleryImage;
    }

    /**
     * @param $imgNum
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function justOpenSlideShow($imgNum) {
        $ourGalleryImage = $this->hoverOverImage($imgNum);
        $ourGalleryImage->findElement(WebDriverBy::className('info'))->click();
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
        sleep(1);
        $img = $this->driver->findElement(WebDriverBy::cssSelector('div.active'));
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($img));
        return $img;
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function advanceToNextImage() {
        $img = $this->getSlideShowImage();
        $this->driver->findElement(WebDriverBy::cssSelector('.right'))->click();
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::visibilityOf($img)));
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function advanceToPreviousImage() {
        $img = $this->getSlideShowImage();
        $this->driver->findElement(WebDriverBy::cssSelector('.left'))->click();
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::visibilityOf($img)));
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
}