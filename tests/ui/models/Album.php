<?php

namespace ui\models;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverWait;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Gallery.php';

class Album {
    /**
     * @var RemoteWebDriver
     */
    private $driver;
    /**
     * @var WebDriverWait
     */
    private $wait;
    /**
     * @var Gallery
     */
    private $gallery;

    public function __construct($driver, $wait) {
        $this->driver = $driver;
        $this->wait = $wait;
        $this->gallery = new Gallery($this->driver, $this->wait);
    }

    public function waitForFinder() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('find-album-code')));
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('find-album-code'))));

    }

    public function openFinder() {
        $this->driver->findElement(WebDriverBy::linkText('Information'))->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::linkText('Find Album'))));
        $this->driver->findElement(WebDriverBy::linkText('Find Album'))->click();
        $this->waitForFinder();
       }

    public function search($code, $save) {
        $this->openFinder();
        if( $save ) {
            $this->driver->findElement(WebDriverBy::id('find-album-add'))->click();
        }
        $this->driver->findElement(WebDriverBy::id('find-album-code'))->sendKeys($code);
        $this->driver->findElement(WebDriverBy::className('btn-success'))->click();
    }

    public function searchKeyboard($code, $save) {
        $this->openFinder();
        if( $save ) {
            $this->driver->findElement(WebDriverBy::id('find-album-add'))->click();
        }
        $this->driver->findElement(WebDriverBy::id('find-album-code'))->sendKeys($code)->sendKeys(WebDriverKeys::ENTER);
    }

    public function add($code) {
        $this->driver->findElement(WebDriverBy::id('album-code'))->sendKeys($code);
        $this->driver->findElement(WebDriverBy::id('album-code-add'))->click();
    }

    public function addKeyboard($code) {
        $this->driver->findElement(WebDriverBy::id('album-code'))->sendKeys($code)->sendKeys(WebDriverKeys::ENTER);
    }

    public function openSlideShow($imgNum) {
        $this->gallery->justOpenSlideShow($imgNum);
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('album'))));
    }

    public function openFavorites() {
        $this->driver->findElement(WebDriverBy::id('favorite-btn'))->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('favorites'))));
    }

    public function waitForImagesToLoad($rows) {
        $this->gallery->waitForImagesToLoad($rows);
    }

    public function hoverOverImage($imgNum): WebDriverElement {
        return $this->gallery->hoverOverImage($imgNum);
    }

    public function advanceToNextImage() {
        $this->gallery->advanceToNextImage();
    }

    public function advanceToPreviousImage() {
        $this->gallery->advanceToPreviousImage();
    }

    public function advanceToImage($img) {
        $this->gallery->advanceToImage($img);
    }

    public function getSlideShowImage() {
        return $this->gallery->getSlideShowImage();
    }


}