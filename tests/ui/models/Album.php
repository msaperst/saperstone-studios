<?php

namespace ui\models;

use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverWait;
use Sql;
use User;

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

    /**
     * @param $code
     * @param $save
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function search($code, $save) {
        $this->openFinder();
        if ($save) {
            $this->driver->findElement(WebDriverBy::id('find-album-add'))->click();
        }
        $this->driver->findElement(WebDriverBy::id('find-album-code'))->sendKeys($code);
        $this->driver->findElement(WebDriverBy::className('btn-success'))->click();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function openFinder() {
        $this->driver->findElement(WebDriverBy::linkText('Information'))->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::linkText('Find Album'))));
        $this->driver->findElement(WebDriverBy::linkText('Find Album'))->click();
        $this->waitForFinder();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForFinder() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('find-album-code')));
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('find-album-code'))));

    }

    /**
     * @param $code
     * @param $save
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function searchKeyboard($code, $save) {
        $this->openFinder();
        if ($save) {
            $this->driver->findElement(WebDriverBy::id('find-album-add'))->click();
        }
        $this->driver->findElement(WebDriverBy::id('find-album-code'))->sendKeys($code)->sendKeys(WebDriverKeys::ENTER);
    }

    /**
     * @param $code
     */
    public function add($code) {
        $this->driver->findElement(WebDriverBy::id('album-code'))->sendKeys($code);
        $this->driver->findElement(WebDriverBy::id('album-code-add'))->click();
    }

    /**
     * @param $code
     */
    public function addKeyboard($code) {
        $this->driver->findElement(WebDriverBy::id('album-code'))->sendKeys($code)->sendKeys(WebDriverKeys::ENTER);
    }

    /**
     * @param $imgNum
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function openSlideShow($imgNum) {
        $this->gallery->justOpenSlideShow($imgNum);
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('album'))));
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function openFavorites() {
        $this->driver->findElement(WebDriverBy::id('favorite-btn'))->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('favorites'))));
    }

    /**
     * @param $rows
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForImagesToLoad($rows) {
        $this->gallery->waitForImagesToLoad($rows);
    }

    /**
     * @param $imgNum
     * @return WebDriverElement
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function hoverOverImage($imgNum): WebDriverElement {
        return $this->gallery->hoverOverImage($imgNum);
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function advanceToNextImage() {
        $this->gallery->advanceToNextImage();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function advanceToPreviousImage() {
        $this->gallery->advanceToPreviousImage();
    }

    /**
     * @param $img
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function advanceToImage($img) {
        $this->gallery->advanceToImage($img);
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function favoriteImage() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('set-favorite-image-btn')));
        $this->driver->findElement(WebDriverBy::id('set-favorite-image-btn'))->click();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function unFavoriteImage() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('unset-favorite-image-btn')));
        $this->driver->findElement(WebDriverBy::id('unset-favorite-image-btn'))->click();
    }

    public function viewFavorites() {
        $this->driver->findElement(WebDriverBy::id('favorite-btn'))->click();
    }

    /**
     * @param $image
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function removeFavorite($image) {
        $favorite = $this->driver->findElement(WebDriverBy:: cssSelector("li[image-id='" . ($image - 1) . "']"));
        sleep(1);
        $action = new WebDriverActions($this->driver);
        $action->moveToElement($favorite, intval($favorite->getSize()->getWidth() * 0.5 - 10), intval($favorite->getSize()->getHeight() * -0.5 + 10))->click()->perform();
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy:: cssSelector("li[image-id='" . ($image - 1) . "']"))));
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function downloadFavorites() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('downloadable-favorites-btn')));
        $this->driver->findElement(WebDriverBy::id('downloadable-favorites-btn'))->click();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function shareFavorites() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('shareable-favorites-btn')));
        $this->driver->findElement(WebDriverBy::id('shareable-favorites-btn'))->click();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function submitFavorites() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('submit-favorites-btn')));
        $this->driver->findElement(WebDriverBy::id('submit-favorites-btn'))->click();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function confirmDownload() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('.btn-success > .glyphicon-download-alt')));
        $this->driver->findElement(WebDriverBy::cssSelector('.btn-success > .glyphicon-download-alt'))->click();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function confirmSubmission() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('submit-send')));
        $this->driver->findElement(WebDriverBy::id('submit-send'))->click();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function shareAll() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('shareable-all-btn')));
        $this->driver->findElement(WebDriverBy::id('shareable-all-btn'))->click();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function downloadAll() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('downloadable-all-btn')));
        $this->driver->findElement(WebDriverBy::id('downloadable-all-btn'))->click();
    }

        /**
     * @return WebDriverElement
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function getSlideShowImage(): WebDriverElement {
        return $this->gallery->getSlideShowImage();
    }

                                        /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function downloadImage() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('downloadable-image-btn')));
        $img = $this->getSlideShowImage();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($img));
        $this->driver->findElement(WebDriverBy::id('downloadable-image-btn'))->click();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function shareImage() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('shareable-image-btn')));
        $this->driver->findElement(WebDriverBy::id('shareable-image-btn'))->click();
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function submitImage() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('submit-image-btn')));
        $img = $this->getSlideShowImage();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($img));
        $this->driver->findElement(WebDriverBy::id('submit-image-btn'))->click();
    }

    /**
     * @param $albumId
     * @return RemoteWebElement
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function getAlbumRow($albumId): RemoteWebElement {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector("tr[album-id='$albumId']")));
        return $this->driver->findElement(WebDriverBy::cssSelector("tr[album-id='$albumId']"));
    }

    /**
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     * @throws Exception
     */
    public function giveUserAlbumAccess($user) {
        $user = User::withId($user);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('#albumDiv #user-search')));
        $this->driver->findElement(WebDriverBy::cssSelector('#albumDiv #user-search'))->clear()->sendKeys($user->getUsername());
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className('search-results')));
        $results = $this->driver->findElements(WebDriverBy::cssSelector('.search-results a'));
        foreach ($results as $result) {
            if ($result->getAttribute('user-id') == $user->getId()) {
                $result->click();
            }
        }
    }

    /**
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     * @throws Exception
     */
    public function giveUserDownloadAccess($user) {
        $user = User::withId($user);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('#downloadDiv #user-search')));
        $this->driver->findElement(WebDriverBy::cssSelector('#downloadDiv #user-search'))->clear()->sendKeys($user->getUsername());
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className('search-results')));
        $results = $this->driver->findElements(WebDriverBy::cssSelector('.search-results a'));
        foreach ($results as $result) {
            if ($result->getAttribute('user-id') == $user->getId()) {
                $result->click();
            }
        }
    }

    /**
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     * @throws Exception
     */
    public function giveUserShareAccess($user) {
        $user = User::withId($user);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('#shareDiv #user-search')));
        $this->driver->findElement(WebDriverBy::cssSelector('#shareDiv #user-search'))->clear()->sendKeys($user->getUsername());
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className('search-results')));
        $results = $this->driver->findElements(WebDriverBy::cssSelector('.search-results a'));
        foreach ($results as $result) {
            if ($result->getAttribute('user-id') == $user->getId()) {
                $result->click();
            }
        }
    }

    /**
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     * @throws Exception
     */
    public function removeUserAlbumAccess($user) {
        $user = User::withId($user);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('user-search')));
        $accessors = $this->getAlbumAccessors();
        foreach ($accessors as $accessor) {
            if ($accessor->getAttribute('user-id') == $user->getId()) {
                $action = new WebDriverActions($this->driver);
                $action->moveToElement($accessor, intval($accessor->getSize()->getWidth() * 0.5 - 5))->click()->perform();
            }
        }
    }

    /**
     * @return RemoteWebElement[]
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function getAlbumAccessors(): array {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('album-users')));
        $albumUsers = $this->driver->findElement(WebDriverBy::id('album-users'));
        return $albumUsers->findElements(WebDriverBy::tagName('span'));
    }

    /**
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     * @throws Exception
     */
    public function removeUserDownloadAccess($user) {
        $user = User::withId($user);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('user-search')));
        $downloaders = $this->getAlbumDownloaders();
        foreach ($downloaders as $downloader) {
            if ($downloader->getAttribute('user-id') == $user->getId()) {
                $action = new WebDriverActions($this->driver);
                $action->moveToElement($downloader, intval($downloader->getSize()->getWidth() * 0.5 - 5))->click()->perform();
            }
        }
    }

    /**
     * @return RemoteWebElement[]
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function getAlbumDownloaders(): array {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('album-users')));
        $albumUsers = $this->driver->findElement(WebDriverBy::id('download-users'));
        return $albumUsers->findElements(WebDriverBy::tagName('span'));
    }

    /**
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     * @throws Exception
     */
    public function removeUserShareAccess($user) {
        $user = User::withId($user);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('user-search')));
        $sharers = $this->getAlbumSharers();
        foreach ($sharers as $sharer) {
            if ($sharer->getAttribute('user-id') == $user->getId()) {
                $action = new WebDriverActions($this->driver);
                $action->moveToElement($sharer, intval($sharer->getSize()->getWidth() * 0.5 - 5))->click()->perform();
            }
        }
    }

    /**
     * @return RemoteWebElement[]
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function getAlbumSharers(): array {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('album-users')));
        $albumUsers = $this->driver->findElement(WebDriverBy::id('share-users'));
        return $albumUsers->findElements(WebDriverBy::tagName('span'));
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function closeSlideShow() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector("#album [data-dismiss='modal']")));
        $this->driver->findElement(WebDriverBy::cssSelector("#album [data-dismiss='modal']"))->click();
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('album')))));
    }
}