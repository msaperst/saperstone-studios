<?php

namespace ui\models;

use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverWait;

class Album {
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

    public function openSlideShow($imgNum) {
        $galleryClass = WebDriverBy::className('gallery');
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated($galleryClass));
        $ourGalleryImage = $this->driver->findElements($galleryClass)[$imgNum];
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($ourGalleryImage));
        $actions = new WebDriverActions($this->driver);
        $actions->moveToElement($ourGalleryImage)->perform();
        $ourGalleryImageInfo = $this->driver->findElements(WebDriverBy::className('info'))[$imgNum];
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($ourGalleryImageInfo));
        $ourGalleryImageInfo->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('album'))));
    }

    public function openFavorites() {
        $this->driver->findElement(WebDriverBy::id('favorite-btn'))->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('favorites'))));
    }
}