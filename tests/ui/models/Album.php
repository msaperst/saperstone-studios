<?php

namespace ui\models;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\UnexpectedTagNameException;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverSelect;
use Facebook\WebDriver\WebDriverWait;
use Sql;

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
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForFinder() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('find-album-code')));
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('find-album-code'))));

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
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function addToCart() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('cart-image-btn')));
        $img = $this->getSlideShowImage();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($img));
        $this->driver->findElement(WebDriverBy::id('cart-image-btn'))->click();
    }

    /**
     * @param $productCategory
     * @param $productName
     * @param $productSize
     * @return WebDriverElement
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function getProductRow($productCategory, $productName, $productSize): WebDriverElement {
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('cart-image'))));
        $this->driver->findElement(WebDriverBy::cssSelector('li > a[href="#' . strtolower($productCategory) . '"]'))->click();
        $sql = new Sql();
        $productType = $sql->getRow("SELECT * FROM product_types WHERE category = '" . strtolower($productCategory) . "' AND name = '$productName';")['id'];
        $product = $sql->getRow("SELECT * FROM products WHERE product_type = '$productType' AND size = '$productSize';")['id'];
        $sql->disconnect();
        return $this->driver->findElement(WebDriverBy::cssSelector("tr[product-id='$product']"));
    }

    /**
     * @param $album
     * @param $image
     * @param $productCategory
     * @param $productSize
     * @param $productName
     * @return WebDriverElement[]
     */
    public function getCartRows($album, $image, $productCategory, $productSize, $productName): array {
        $sql = new Sql();
        $img = $sql->getRow("SELECT * FROM `album_images` WHERE `album` = $album AND `sequence` = " . ($image - 1))['id'];
        $productType = $sql->getRow("SELECT * FROM product_types WHERE category = '" . strtolower($productCategory) . "' AND name = '$productName';")['id'];
        $product = $sql->getRow("SELECT * FROM products WHERE product_type = '$productType' AND size = '$productSize';")['id'];
        $sql->disconnect();
        return $this->driver->findElements(WebDriverBy::cssSelector("tr[product-id='$product'][product-type='$productType'][album-id='$album'][image-id='$img']"));
    }

    /**
     * @param $productCategory
     * @param $productSize
     * @param $productName
     * @return float
     */
    public function getProductPrice($productCategory, $productSize, $productName): float {
        $sql = new Sql();
        $productType = $sql->getRow("SELECT * FROM product_types WHERE category = '" . strtolower($productCategory) . "' AND name = '$productName';")['id'];
        $productPrice = $sql->getRow("SELECT * FROM products WHERE product_type = '$productType' AND size = '$productSize';")['price'];
        $sql->disconnect();
        return $productPrice;
    }

    /**
     * @param $productCategory
     * @param $productName
     * @return array
     */
    public function getProductOptions($productCategory, $productName): array {
        $sql = new Sql();
        $productType = $sql->getRow("SELECT * FROM product_types WHERE category = '" . strtolower($productCategory) . "' AND name = '$productName';")['id'];
        $productOptions = array_column($sql->getRows("SELECT * FROM product_options WHERE product_type = '$productType';"), 'opt');
        $sql->disconnect();
        return $productOptions;
    }

    /**
     * @param $howMany
     * @param $productCategory
     * @param $productName
     * @param $productSize
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function addSelectionToCart($howMany, $productCategory, $productName, $productSize) {
        $productRow = $this->getProductRow($productCategory, $productName, $productSize);
        $productRow->findElement(WebDriverBy::tagName('input'))->clear()->sendKeys($howMany);
    }

    /**
     * @param $productCategory
     * @param $productName
     * @param $productSize
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function increaseSelectionToCart($productCategory, $productName, $productSize) {
        $productRow = $this->getProductRow($productCategory, $productName, $productSize);
        $productRow->findElement(WebDriverBy::tagName('input'))->sendKeys(WebDriverKeys::ARROW_UP);
    }

    public function viewCart() {
        $this->driver->findElement(WebDriverBy::id('cart-btn'))->click();
        //waiting for the cart - TODO need to fix this
        sleep(1.0);
    }

    /**
     * @param $option
     * @param $albumId
     * @param $image
     * @param $productCategory
     * @param $productSize
     * @param $productName
     * @throws UnexpectedTagNameException
     * @throws NoSuchElementException
     */
    public function selectOption($option, $albumId, $image, $productCategory, $productSize, $productName) {
        $productRows = self::getCartRows($albumId, $image, $productCategory, $productSize, $productName);
        $select = new WebDriverSelect($productRows[0]->findElement(WebDriverBy::tagName('select')));
        $options = $select->getOptions();
        for( $i = 0; $i < sizeof($options); $i++ ) {
            if( $options[$i]->getText() == "$option" ) {
                $select->selectByIndex($i);
            }
        }
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function purchaseImage() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('not-downloadable-image-btn')));
        $img = $this->getSlideShowImage();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($img));
        $this->driver->findElement(WebDriverBy::id('not-downloadable-image-btn'))->click();
        //waiting for the cart - TODO need to fix this
        sleep(1.0);
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function downloadImage() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('downloadable-image-btn')));
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
        $this->driver->findElement(WebDriverBy::id('submit-image-btn'))->click();
    }
}