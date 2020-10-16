<?php

namespace ui\models;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;

class Album {
    private $driver;
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


}