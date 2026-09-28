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
     * Uploads a file through the real album upload control.
     *
     * WebDriver sends the absolute path directly to the file input, avoiding
     * the native operating-system file chooser while still exercising the
     * browser upload workflow.
     *
     * @param string $filePath
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function uploadImage(string $filePath): void {
        $this->wait->until(
            WebDriverExpectedCondition::presenceOfElementLocated(
                WebDriverBy::cssSelector('#add-images-button input[type="file"]')
            )
        );
        $this->driver
            ->findElement(WebDriverBy::cssSelector('#add-images-button input[type="file"]'))
            ->sendKeys($filePath);

        // File selection starts an asynchronous upload. The production upload
        // workflow disables the dialog while the queue is active and only
        // re-enables it from afterUploadAll, so synchronize on that lifecycle
        // instead of racing the next browser step against the request.
        $closeButton = WebDriverBy::xpath(
            "//div[contains(@class, 'bootstrap-dialog')]//button[normalize-space(.)='Close']"
        );
        $this->wait->until(function () use ($closeButton) {
            $buttons = $this->driver->findElements($closeButton);
            return !empty($buttons) && !$buttons[count($buttons) - 1]->isEnabled();
        });
        $this->wait->until(function () use ($closeButton) {
            $buttons = $this->driver->findElements($closeButton);
            return !empty($buttons) && $buttons[count($buttons) - 1]->isEnabled();
        });
    }

    /**
     * @param $imgNum
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function openSlideShow($imgNum) {
        $card = $this->getImageCard($imgNum);
        $media = $card->findElement(WebDriverBy::className('album-card-media'));
        $this->driver->executeScript("arguments[0].scrollIntoView({block: 'center'});", [$card]);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($media));
        $media->click();
        $this->wait->until(
            WebDriverExpectedCondition::visibilityOf(
                $this->driver->findElement(WebDriverBy::id('album-viewer-overlay'))
            )
        );
        $this->waitForActiveImage($imgNum);
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
        $imageCount = (int) $rows * 4;
        $this->wait->until(function () use ($imageCount) {
            if (count($this->driver->findElements(WebDriverBy::cssSelector('#album-grid .album-card'))) >= $imageCount) {
                return true;
            }

            $this->driver->executeScript("window.scrollTo(0, document.body.scrollHeight); window.dispatchEvent(new Event('scroll'));");
            return false;
        });
    }

    /**
     * @param $imgNum
     * @return WebDriverElement
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function hoverOverImage($imgNum): WebDriverElement {
        $card = $this->getImageCard($imgNum);
        $this->driver->executeScript("arguments[0].scrollIntoView({block: 'center'});", [$card]);
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($card));
        (new WebDriverActions($this->driver))->moveToElement($card)->perform();
        $this->wait->until(
            WebDriverExpectedCondition::visibilityOf(
                $card->findElement(WebDriverBy::className('album-card-actions'))
            )
        );
        return $card;
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function advanceToNextImage() {
        $current = $this->getActiveImageId();
        $this->driver->findElement(WebDriverBy::id('album-next-btn'))->click();
        $this->waitForActiveImage($this->getAdjacentImageId($current, 1));
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function advanceToPreviousImage() {
        $current = $this->getActiveImageId();
        $this->driver->findElement(WebDriverBy::id('album-prev-btn'))->click();
        $this->waitForActiveImage($this->getAdjacentImageId($current, -1));
    }

    /**
     * @param $img
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function advanceToImage($img) {
        $this->openSlideShow($img);
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
        $selector = "#album-grid .album-card[data-image-id='" . ($image - 1) . "']";
        $card = $this->driver->findElement(WebDriverBy::cssSelector($selector));
        (new WebDriverActions($this->driver))->moveToElement($card)->perform();
        $buttonSelector = WebDriverBy::cssSelector(
            $selector . " .album-card-action[data-action='favorite']"
        );
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($buttonSelector));
        $this->driver->findElement($buttonSelector)->click();
        $this->wait->until(function () use ($selector) {
            $cards = $this->driver->findElements(WebDriverBy::cssSelector($selector));
            return empty($cards) || !$cards[0]->isDisplayed() || $cards[0]->getAttribute('data-favorite') === '0';
        });
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
        $this->wait->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('album-viewer-overlay'))
        );
        $imageId = $this->getActiveImageId();
        return $this->driver->findElement(
            WebDriverBy::cssSelector("#album-grid .album-card[data-image-id='$imageId']")
        );
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
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('.search-results')));
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
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('.search-results')));
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
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('.search-results')));
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
        $overlay = $this->driver->findElement(WebDriverBy::id('album-viewer-overlay'));
        $this->driver->findElement(WebDriverBy::id('album-viewer-close'))->click();
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::visibilityOf($overlay)));
    }

    private function getImageCardSelector($imgNum): string {
        return "#album-grid .album-card[data-image-id='" . ((int) $imgNum - 1) . "']";
    }

    private function getImageCard($imgNum): WebDriverElement {
        $this->waitForImagesToLoad((int) ceil(((int) $imgNum) / 4));
        $selector = WebDriverBy::cssSelector($this->getImageCardSelector($imgNum));
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated($selector));
        return $this->driver->findElement($selector);
    }

    private function getActiveImageId(): int {
        $overlay = $this->driver->findElement(WebDriverBy::id('album-viewer-overlay'));
        return (int) $overlay->getAttribute('image-id');
    }

    private function getAdjacentImageId(int $current, int $offset): int {
        $cards = $this->driver->findElements(WebDriverBy::cssSelector('#album-grid .album-card'));
        $count = count($cards);
        if ($count === 0) {
            return $current;
        }
        return ($current + $offset + $count) % $count;
    }

    private function waitForActiveImage($imgNum): void {
        $expectedImageId = (int) $imgNum - 1;
        $this->wait->until(function () use ($expectedImageId) {
            $overlay = $this->driver->findElements(WebDriverBy::id('album-viewer-overlay'));
            return count($overlay) === 1
                && $overlay[0]->isDisplayed()
                && (int) $overlay[0]->getAttribute('image-id') === $expectedImageId;
        });
    }
}