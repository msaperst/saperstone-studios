<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Environment\Environment;
use CustomAsserts;
use Exception;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Exception\NoSuchCookieException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Exception\UnexpectedTagNameException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use Sql;
use ui\models\Album;
use User;
use ZipArchive;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Album.php';

class AlbumFeatureContext implements Context {

    /**
     * @var Environment
     */
    private $environment;
    /**
     * @var RemoteWebDriver
     */
    private $driver;
    /**
     * @var WebDriverWait
     */
    private $wait;
    /**
     * @var User
     */
    private $user;
    /**
     * The image we're interacting with
     * @var RemoteWebElement
     */
    private $image;
    private $albumIds = [];

    /** @BeforeScenario
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $this->environment = $scope->getEnvironment();
        $this->driver = $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 10);
        $this->user = $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->getUser();
    }

    /**
     * @AfterScenario
     * @throws Exception
     */
    public function cleanup() {
        $sql = new Sql();
        foreach ($this->albumIds as $albumId) {
            $albumLocation = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'albums' . DIRECTORY_SEPARATOR . $sql->getRow("SELECT * FROM albums WHERE albums.id = $albumId")['location'];
            $sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = $albumId;");
            $sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = $albumId;");
            $sql->executeStatement("DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = $albumId;");
            $sql->executeStatement("DELETE FROM `favorites` WHERE `favorites`.`album` = $albumId;");
            $sql->executeStatement("DELETE FROM `download_rights` WHERE `download_rights`.`album` = $albumId;");
            $sql->executeStatement("DELETE FROM `share_rights` WHERE `share_rights`.`album` = $albumId;");
            $sql->executeStatement("DELETE FROM `cart` WHERE `cart`.`album` = $albumId;");
            $sql->executeStatement("DELETE FROM `user_logs` WHERE `user_logs`.`album` = $albumId;");
            if (is_dir($albumLocation)) {
                system("rm -rf " . escapeshellarg($albumLocation));
            }
        }
        $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        $sql->disconnect();
    }

    /**
     * @Given /^album (\d+) exists$/
     * @param $albumId
     * @throws Exception
     */
    public function albumExists($albumId) {
        $this->albumIds[] = $albumId;
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ($albumId, 'Album $albumId', 'sample album for testing', 'sample', 1);");
        $sql->disconnect();
    }

    /**
     * @Given /^I have created album (\d+)$/
     * @param $albumId
     * @throws Exception
     */
    public function iHaveCreatedAlbum($albumId) {
        $this->albumIds[] = $albumId;
        $this->user = $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->getUser();
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`) VALUES ($albumId, 'Album $albumId', 'sample album for testing', 'sample', {$this->user->getId()});");
        $sql->disconnect();
    }

    /**
     * @Given /^album (\d+) exists with code "([^"]*)"$/
     * @param $albumId
     * @param $albumCode
     * @throws Exception
     */
    public function albumExistsWithCode($albumId, $albumCode) {
        $this->albumIds[] = $albumId;
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ($albumId, 'Album $albumId', 'sample album for testing', 'sample', 1, '$albumCode');");
        $sql->disconnect();
    }

    /**
     * @Given /^album (\d+) has code "([^"]*)"$/
     * @param $albumId
     * @param $albumCode
     * @throws Exception
     */
    public function albumHasCode($albumId, $albumCode) {
        $sql = new Sql();
        $sql->executeStatement("UPDATE `albums` SET `code` = '$albumCode' WHERE `id` = $albumId;");
        $sql->disconnect();
    }

    /**
     * @Given /^album (\d+) exists with (\d+) images$/
     * @param $albumId
     * @param $images
     * @throws Exception
     */
    public function albumExistsWithImages($albumId, $images) {
        $this->albumIds[] = $albumId;
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `images`) VALUES ($albumId, 'Album $albumId', 'sample album for testing', 'sample-album', 1, '$images');");
        $oldMask = umask(0);
        if (!is_dir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/albums/sample-album')) {
            mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/albums/sample-album');
        }
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/albums/sample-album', 0777);
        for ($i = 0; $i < $images; $i++) {
            $sql->executeStatement("INSERT INTO `album_images` (`album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES ('$albumId', 'Image $i', $i, '', '/albums/sample-album/sample$i.jpg', '400', '300', '1');");
            copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "content/albums/sample-album/sample$i.jpg");
            chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "content/albums/sample-album/sample$i.jpg", 0777);
        }
        umask($oldMask);
        $sql->disconnect();
    }

    /**
     * @Given /^I have created album (\d+) with (\d+) images$/
     * @param $albumId
     * @throws Exception
     */
    public function iHaveCreatedAlbumWithImages($albumId, $images) {
        $this->albumIds[] = $albumId;
        $this->user = $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->getUser();
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `images`) VALUES ($albumId, 'Album $albumId', 'sample album for testing', 'sample-album', {$this->user->getId()}, '$images');");
        $oldMask = umask(0);
        if (!is_dir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/albums/sample-album')) {
            mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/albums/sample-album');
        }
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/albums/sample-album', 0777);
        for ($i = 0; $i < $images; $i++) {
            $sql->executeStatement("INSERT INTO `album_images` (`album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES ('$albumId', 'Image $i', $i, '', '/albums/sample-album/sample$i.jpg', '400', '300', '1');");
            copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "content/albums/sample-album/sample$i.jpg");
            chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . "content/albums/sample-album/sample$i.jpg", 0777);
        }
        umask($oldMask);
        $sql->disconnect();
    }

    /**
     * @Given /^I have access to album (\d+)$/
     * @param $albumId
     * @throws Exception
     */
    public function iHaveAccessToAlbum($albumId) {
        $this->user = $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->getUser();
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES ({$this->user->getId()}, $albumId);");
        $sql->disconnect();
    }

    /**
     * @Given /^album (\d+) image (\d+) has captain "([^"]*)"$/
     * @param $album
     * @param $image
     * @param $caption
     * @throws Exception
     */
    public function albumImageHasCaptain($album, $image, $caption) {
        $sql = new Sql();
        $sql->executeStatement("UPDATE `album_images` SET caption = '$caption' WHERE `album` = $album AND sequence = " . ($image - 1));
        $sql->disconnect();
    }

    /**
     * @Given /^album (\d+) image (\d+) is a favorite$/
     * @param $album
     * @param $image
     * @throws Exception
     */
    public function albumImageIsAFavorite($album, $image) {
        $sql = new Sql();
        $img = $sql->getRow("SELECT * FROM `album_images` WHERE `album` = $album AND `sequence` = " . ($image - 1))['id'];
        $sql->executeStatement("INSERT INTO `favorites` VALUES( '{$this->user->getId()}', $album, $img);");
        $sql->disconnect();
    }

    /**
     * @Given /^I have download rights for album (\d+) image (\d+)$/
     * @param $album
     * @param $image
     * @throws Exception
     */
    public function iHaveDownloadRightsForAlbumImage($album, $image) {
        $sql = new Sql();
        $img = $sql->getRow("SELECT * FROM `album_images` WHERE `album` = $album AND `sequence` = " . ($image - 1))['id'];
        $sql->executeStatement("INSERT INTO `download_rights` VALUES( {$this->user->getId()}, $album, $img);");
        $sql->disconnect();
    }

    /**
     * @Given /^I have share rights for album (\d+) image (\d+)$/
     * @param $album
     * @param $image
     * @throws Exception
     */
    public function iHaveShareRightsForAlbumImage($album, $image) {
        $sql = new Sql();
        $img = $sql->getRow("SELECT * FROM `album_images` WHERE `album` = $album AND `sequence` = " . ($image - 1))['id'];
        $sql->executeStatement("INSERT INTO `share_rights` VALUES( {$this->user->getId()}, $album, $img);");
        $sql->disconnect();
    }

    /**
     * @Given /^user (\d+) has access to album (\d+)$/
     * @param $userId
     * @param $albumId
     * @throws Exception
     */
    public function userHasAccessToAlbum($userId, $albumId) {
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums_for_users` VALUES( $userId, $albumId);");
        $sql->disconnect();
    }

    /**
     * @Given /^user (\d+) has download access to album (\d+)$/
     * @param $userId
     * @param $albumId
     * @throws Exception
     */
    public function userHasDownloadAccessToAlbum($userId, $albumId) {
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `download_rights` VALUES( $userId, $albumId, '*');");
        $sql->disconnect();
    }

    /**
     * @Given /^user (\d+) has share access to album (\d+)$/
     * @param $userId
     * @param $albumId
     * @throws Exception
     */
    public function userHasShareAccessToAlbum($userId, $albumId) {
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `share_rights` VALUES( $userId, $albumId, '*');");
        $sql->disconnect();
    }

    /**
     * @Given /^album (\d+) image (\d+) has (\d+) "([^"]*)" "([^"]*)" "([^"]*)" in the cart$/
     * @param $album
     * @param $image
     * @param $howMany
     * @param $productCategory
     * @param $productSize
     * @param $productName
     * @throws Exception
     */
    public function albumImageHasInTheCart($album, $image, $howMany, $productCategory, $productSize, $productName) {
        $sql = new Sql();
        $img = $sql->getRow("SELECT * FROM `album_images` WHERE `album` = $album AND `sequence` = " . ($image - 1))['id'];
        $productType = $sql->getRow("SELECT * FROM product_types WHERE category = '" . strtolower($productCategory) . "' AND name = '$productName';")['id'];
        $product = $sql->getRow("SELECT * FROM products WHERE product_type = '$productType' AND size = '$productSize';")['id'];
        $sql->executeStatement("INSERT INTO cart VALUES( {$this->user->getId()}, $album, $img, $product, $howMany)");
        $sql->disconnect();
    }

    /**
     * @Given /^I have searched for album "([^"]*)"$/
     * @param $albumCode
     */
    public function iHaveSearchedForAlbum($albumCode) {
        $sql = new Sql();
        $albumId = $sql->getRow("SELECT * FROM albums WHERE code = '$albumCode'")['id'];
        $sql->disconnect();
        $searched [$albumId] = md5('album' . $albumCode);
        $cookie = new Cookie('searched', json_encode($searched));
        $this->driver->manage()->addCookie($cookie);
    }

    /**
     * @When /^I add album "([^"]*)" to my albums$/
     * @param $albumCode
     */
    public function iAddAlbumToMyAlbums($albumCode) {
        $album = new Album($this->driver, $this->wait);
        $album->add($albumCode);
    }

    /**
     * @When /^I add album "([^"]*)" to my albums with keyboard$/
     * @param $albumCode
     */
    public function iAddAlbumToMyAlbumsWithKeyboard($albumCode) {
        $album = new Album($this->driver, $this->wait);
        $album->addKeyboard($albumCode);
    }

    /**
     * @When /^I hover over album image (\d+)$/
     * @param $imgNum
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iHoverOverImage($imgNum) {
        $album = new Album($this->driver, $this->wait);
        $this->image = $album->hoverOverImage($imgNum);
    }

    /**
     * @When /^I view album image (\d+)$/
     * @param $imgNum
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iViewImage($imgNum) {
        $album = new Album($this->driver, $this->wait);
        $album->openSlideShow($imgNum);
    }

    /**
     * @When /^I advance to the next album image$/
     */
    public function iAdvanceToTheNextImage() {
        $album = new Album($this->driver, $this->wait);
        $album->advanceToNextImage();
    }

    /**
     * @When /^I advance to the previous album image$/
     */
    public function iAdvanceToThePreviousImage() {
        $album = new Album($this->driver, $this->wait);
        $album->advanceToPreviousImage();
    }

    /**
     * @When /^I skip to album image (\d+)$/
     * @param $img
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSkipToImage($img) {
        $album = new Album($this->driver, $this->wait);
        $album->advanceToImage($img);
    }

    /**
     * @When /^I favorite the image$/
     * @throws Exception
     */
    public function iFavoriteTheImage() {
        $album = new Album($this->driver, $this->wait);
        $album->favoriteImage();
    }

    /**
     * @When /^I view my favorites$/
     */
    public function iViewMyFavorites() {
        $album = new Album($this->driver, $this->wait);
        $album->viewFavorites();
    }

    /**
     * @When /^I defavorite the image$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iDefavoriteTheImage() {
        $album = new Album($this->driver, $this->wait);
        $album->unFavoriteImage();
    }

    /**
     * @When /^I remove favorite image (\d+)$/
     * @param $image
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iRemoveFavoriteImage($image) {
        $album = new Album($this->driver, $this->wait);
        $album->removeFavorite($image);
    }

    /**
     * @When /^I add the image to my cart$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iAddTheImageToMyCart() {
        $album = new Album($this->driver, $this->wait);
        $album->addToCart();
    }

    /**
     * @When /^I purchase the image$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iPurchaseTheImage() {
        $album = new Album($this->driver, $this->wait);
        $album->purchaseImage();
    }

    /**
     * @When /^I download the image$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iDownloadTheImage() {
        $album = new Album($this->driver, $this->wait);
        $album->downloadImage();
    }

    /**
     * @When /^I share the image$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iShareTheImage() {
        $album = new Album($this->driver, $this->wait);
        $album->shareImage();
    }

    /**
     * @When /^I submit the image$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSubmitTheImage() {
        $album = new Album($this->driver, $this->wait);
        $album->submitImage();
    }

    /**
     * @When /^I close the album image modal$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iCloseTheModal() {
        $this->driver->findElement(WebDriverBy::cssSelector('#album button[data-dismiss="modal"]'))->click();
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('album')))));
    }

    /**
     * @When /^I add (\d+) "([^"]*)" "([^"]*)" "([^"]*)"$/
     * @param $howMany
     * @param $productCategory
     * @param $productSize
     * @param $productName
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iAdd($howMany, $productCategory, $productSize, $productName) {
        $album = new Album($this->driver, $this->wait);
        $album->addSelectionToCart($howMany, $productCategory, $productName, $productSize);
    }

    /**
     * @When /^I increase "([^"]*)" "([^"]*)" "([^"]*)" count$/
     * @param $productCategory
     * @param $productSize
     * @param $productName
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iIncreaseCount($productCategory, $productSize, $productName) {
        $album = new Album($this->driver, $this->wait);
        $album->increaseSelectionToCart($productCategory, $productName, $productSize);
    }

    /**
     * @When /^I confirm my download$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iConfirmMyDownload() {
        $album = new Album($this->driver, $this->wait);
        $album->confirmDownload();
    }

    /**
     * @When /^I confirm my submission$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iConfirmMySubmission() {
        $album = new Album($this->driver, $this->wait);
        $album->confirmSubmission();
    }

    /**
     * @When /^I download my favorites$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iDownloadMyFavorites() {
        $album = new Album($this->driver, $this->wait);
        $album->downloadFavorites();
    }

    /**
     * @When /^I download all my images$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iDownloadAllMyImages() {
        $album = new Album($this->driver, $this->wait);
        $album->downloadAll();
    }

    /**
     * @When /^I share my favorites$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iShareMyFavorites() {
        $album = new Album($this->driver, $this->wait);
        $album->shareFavorites();
    }

    /**
     * @When /^I share all my images$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iShareAllMyImages() {
        $album = new Album($this->driver, $this->wait);
        $album->shareAll();
    }

    /**
     * @When /^I submit my favorites$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSubmitMyFavorites() {
        $album = new Album($this->driver, $this->wait);
        $album->submitFavorites();
    }

    /**
     * @When /^I view my cart$/
     */
    public function iViewMyCart() {
        $album = new Album($this->driver, $this->wait);
        $album->viewCart();
    }

    /**
     * @When /^I remove album (\d+) image (\d+) "([^"]*)" "([^"]*)" "([^"]*)" from the cart$/
     * @param $albumId
     * @param $image
     * @param $productCategory
     * @param $productSize
     * @param $productName
     */
    public function iRemoveAlbumImageFromTheCart($albumId, $image, $productCategory, $productSize, $productName) {
        $album = new Album($this->driver, $this->wait);
        $productRows = $album->getCartRows($albumId, $image, $productCategory, $productSize, $productName);
        $this->driver->wait(WebDriverExpectedCondition::visibilityOf($productRows[0]));
        $productRows[0]->findElement(WebDriverBy::cssSelector('td > i'))->click();
    }

    /**
     * @When /^I provide "([^"]*)" for the shipping "([^"]*)"$/
     * @param $value
     * @param $field
     */
    public function iProvideForTheContact($value, $field) {
        $this->driver->findElement(WebDriverBy::id('cart-' . $field))->clear()->sendKeys($value);
    }

    /**
     * @When /^I select option "([^"]*)" in cart for album (\d+) image (\d+) "([^"]*)" "([^"]*)" "([^"]*)"$/
     * @param $option
     * @param $albumId
     * @param $image
     * @param $productCategory
     * @param $productSize
     * @param $productName
     * @throws NoSuchElementException
     * @throws UnexpectedTagNameException
     */
    public function iSelectOptionInCartForAlbumImage($option, $albumId, $image, $productCategory, $productSize, $productName) {
        $album = new Album($this->driver, $this->wait);
        $album->selectOption($option, $albumId, $image, $productCategory, $productSize, $productName);
    }

    /**
     * @When /^I submit my cart$/
     */
    public function iSubmitMyCart() {
        $this->driver->findElement(WebDriverBy:: id('cart-submit'))->click();
    }

    /**
     * @When /^I add user (\d+) for album access$/
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iAddUserForAlbumAccess($user) {
        $album = new Album($this->driver, $this->wait);
        $album->giveUserAlbumAccess($user);
    }

    /**
     * @When /^I add user (\d+) for download access$/
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iAddUserForDownloadAccess($user) {
        $album = new Album($this->driver, $this->wait);
        $album->giveUserDownloadAccess($user);
    }

    /**
     * @When /^I add user (\d+) for share access$/
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iAddUserForShareAccess($user) {
        $album = new Album($this->driver, $this->wait);
        $album->giveUserShareAccess($user);
    }

    /**
     * @When /^I remove user (\d+) for album access$/
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iRemoveUserForAlbumAccess($user) {
        $album = new Album($this->driver, $this->wait);
        $album->removeUserAlbumAccess($user);
    }

    /**
     * @When /^I remove user (\d+) for download access$/
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iRemoveUserForDownloadAccess($user) {
        $album = new Album($this->driver, $this->wait);
        $album->removeUserDownloadAccess($user);
    }

    /**
     * @When /^I remove user (\d+) for share access$/
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iRemoveUserForShareAccess($user) {
        $album = new Album($this->driver, $this->wait);
        $album->removeUserShareAccess($user);
    }

    /**
     * @When /^I add a new album$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iAddANewAlbum() {
        $this->driver->findElement(WebDriverBy::id('add-album-btn'))->click();
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('glyphicon-folder-close')));
    }

    /**
     * @When /^I provide "([^"]*)" for the album "([^"]*)"$/
     * @param $value
     * @param $field
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iProvideForTheAlbum($value, $field) {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('new-album-' . $field)));
        $this->driver->findElement(WebDriverBy::id('new-album-' . $field))->clear()->sendKeys($value);
    }

    /**
     * @When /^I create my album$/
     * @throws Exception
     */
    public function iCreateMyAlbum() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('glyphicon-folder-close')));
        $this->driver->findElement(WebDriverBy::className('glyphicon-folder-close'))->click();
        //if this is a success, we need to add the new album to the cleanup list
        try {
            $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('album')));
            //this means we added an album, grab the id, so we can later delete it
            $this->albumIds[] = $this->driver->findElement(WebDriverBy::id('album'))->getAttribute('album-id');
        } catch (TimeoutException | NoSuchElementException $e) {
            // do nothing, we're in an error condition, which is fine
        }
    }

    /**
     * @When /^I edit album (\d+)$/
     * @param $albumId
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iEditAlbum($albumId) {
        $album = new Album($this->driver, $this->wait);
        $albumRow = $album->getAlbumRow($albumId);
        $albumRow->findElement(WebDriverBy::className('edit-album-btn'))->click();
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('glyphicon-save')));
    }

    /**
     * @When /^I view album (\d+) logs$/
     * @param $albumId
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iViewAlbumLogs($albumId) {
        $album = new Album($this->driver, $this->wait);
        $albumRow = $album->getAlbumRow($albumId);
        $albumRow->findElement(WebDriverBy::className('view-album-log-btn'))->click();
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('album-logs')));
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::className('album-logs'))));
    }

    /**
     * @When /^I update my album$/
     */
    public function iUpdateMyAlbum() {
        $this->driver->findElement(WebDriverBy::className('glyphicon-save'))->click();
        try {
            $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('glyphicon-save'))));
        } catch (Exception | TimeoutException | NoSuchElementException $e) {
            // do nothing, we're in an error condition, which is fine
        }
    }

    /**
     * @When /^I delete my album$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iDeleteMyAlbum() {
        $this->driver->findElement(WebDriverBy::className('glyphicon-trash'))->click();
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('div.bootstrap-dialog-footer-buttons > .btn-danger:first-child')));
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::cssSelector('div.bootstrap-dialog-footer-buttons > .btn-danger:first-child'))));
    }

    /**
     * @When /^I confirm my deletion of my album$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iConfirmMyDeletionOfMyAlbum() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector('div.bootstrap-dialog-footer-buttons > .btn-danger:first-child')));
        $this->driver->findElement(WebDriverBy::cssSelector('div.bootstrap-dialog-footer-buttons > .btn-danger:first-child'))->click();
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('glyphicon-save')));
    }

    /**
     * @When /^I set access to my album$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSetAccessToMyAlbum() {
        $this->driver->findElement(WebDriverBy::className('glyphicon-picture'))->click();
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('user-search')));
    }

    /**
     * @When /^I make thumbnails for my album$/
     */
    public function iMakeThumbnailsForMyAlbum() {
        $this->driver->findElement(WebDriverBy::className('glyphicon-refresh'))->click();
    }

    /**
     * @When /^I create "([^"]*)" thumbnails$/
     * @param $thumbType
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iCreateThumbnails($thumbType) {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::className('glyphicon-eye-close')));
        $buttons = $this->driver->findElements(WebDriverBy::className('bootstrap-dialog-footer-buttons'))[1]->findElements(WebDriverBy::tagName('button'));
        foreach ($buttons as $button) {
            if (strtolower($button->getText()) == $thumbType) {
                $button->click();
            }
        }
    }

    /**
     * @Then /^I see the "([^"]*)" album images load$/
     * @param $ord
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeTheAlbumImagesLoad($ord) {
        $album = new Album($this->driver, $this->wait);
        $row = intval($ord);
        $album->waitForImagesToLoad($row);
        $s = ($row - 1) * 4;
        for ($i = 0; $i < 4; $i++) {
            $image = $this->driver->findElement((WebDriverBy::cssSelector("#col-$i > div.gallery:nth-child($row)")));
            Assert::assertEquals('Image ' . ($s + $i), $image->findElement(WebDriverBy::tagName('img'))->getAttribute('alt'), $image->findElement(WebDriverBy::tagName('img'))->getAttribute('alt'));
        }
    }

    /**
     * @Then /^I see the info icon on album image (\d+)$/
     * @param $imgNum
     */
    public function iSeeTheInfoIconOnImage($imgNum) {
        Assert::assertTrue($this->image->findElement(WebDriverBy::className('info'))->isDisplayed());
    }

    /**
     * @Then /^I see album image (\d+) in the preview modal$/
     * @param $imgNum
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeImageInThePreviewModal($imgNum) {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('album'))->isDisplayed());
        $album = new Album($this->driver, $this->wait);
        $img = $album->getSlideShowImage();
        Assert::assertEquals('Image ' . ($imgNum - 1), $img->findElement(WebDriverBy::tagName('div'))->getAttribute('alt'), $img->findElement(WebDriverBy::tagName('div'))->getAttribute('alt'));
    }

    /**
     * @Then /^I see the album caption "([^"]*)" displayed$/
     * @param $caption
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeTheCaptionDisplayed($caption) {
        $album = new Album($this->driver, $this->wait);
        $img = $album->getSlideShowImage();
        Assert::assertEquals($caption, $img->findElement(WebDriverBy::tagName('h2'))->getText());
    }

    /**
     * @Then /^I do not see any album captions$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iDoNotSeeAnyCaptions() {
        $album = new Album($this->driver, $this->wait);
        $img = $album->getSlideShowImage();
        Assert::assertEquals('', $img->findElement(WebDriverBy::tagName('h2'))->getText());
    }

    /**
     * @Then /^I see the image as a favorite$/
     */
    public function iSeeTheImageAsAFavorite() {
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('set-favorite-image-btn'))->isDisplayed());
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('unset-favorite-image-btn'))->isDisplayed());
    }

    /**
     * @Then /^I see the favorite count is "([^"]*)"$/
     * @param $favoriteCount
     */
    public function iSeeTheFavoriteCountIs($favoriteCount) {
        Assert::assertEquals($favoriteCount, $this->driver->findElement(WebDriverBy::id('favorite-count'))->getText());
    }

    /**
     * @Then /^I do not see the image as a favorite$/
     */
    public function iDoNotSeeTheImageAsAFavorite() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('set-favorite-image-btn'))->isDisplayed());
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('unset-favorite-image-btn'))->isDisplayed());
    }

    /**
     * @Then /^I see (\d+) favorite[s]?$/
     * @param $favorites
     */
    public function iSeeFavorites($favorites) {
        Assert::assertEquals($favorites, sizeof($this->driver->findElements(WebDriverBy::className('img-favorite'))));
    }

    /**
     * @Then /^I see album image (\d+) as a favorite$/
     * @param $image
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeAlbumImageAsAFavorite($image) {
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy:: cssSelector("li[image-id='" . ($image - 1) . "']"))));
        Assert::assertTrue($this->driver->findElement(WebDriverBy:: cssSelector("li[image-id='" . ($image - 1) . "']"))->isDisplayed());
    }

    /**
     * @Then /^the download favorites button is disabled$/
     */
    public function theDownloadFavoritesButtonIsDisabled() {
        Assert::assertFalse($this->driver->findElement(WebDriverBy:: id('downloadable-favorites-btn'))->isEnabled());
    }

    /**
     * @Then /^the share favorites button is disabled$/
     */
    public function theShareFavoritesButtonIsDisabled() {
        Assert::assertFalse($this->driver->findElement(WebDriverBy:: id('shareable-favorites-btn'))->isEnabled());
    }

    /**
     * @Then /^the submit favorites button is disabled$/
     */
    public function theSubmitFavoritesButtonIsDisabled() {
        Assert::assertFalse($this->driver->findElement(WebDriverBy:: id('submit-favorites-btn'))->isEnabled());
    }

    /**
     * @Then /^I see the download terms of service$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeTheDownloadTermsOfService() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('bootstrap-dialog-message')));
        Assert::assertEquals('By downloading the selected files, you are agreeing to the right to copy, display, reproduce, enlarge and distribute said photographs taken by the Photographer in connection with the Services and in connection with the publication known as Saperstone Studios for personal use, and any reprints or reproductions, or excerpts thereof; all other rights are expressly reserved by and to Photographer.

While usage in accordance with above policies of selected files on public social media sites and personal websites for non-profit purposes is acceptable, any use of selected files in any publication, display, exhibit or paid medium are not permitted without express consent from Photographer.

Please note that only images you have expressly purchased rights to will be downloaded, even if additional images were selected for this download.',
            $this->driver->findElement(WebDriverBy::className('bootstrap-dialog-message'))->getText(), $this->driver->findElement(WebDriverBy::className('bootstrap-dialog-message'))->getText());
    }

    /**
     * @Then /^I see that sharing isn't available$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeThatSharingIsnTAvailable() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('bootstrap-dialog-message')));
        Assert::assertEquals('This functionality isn\'t available yet. Please check back soon.',
            $this->driver->findElement(WebDriverBy::className('bootstrap-dialog-message'))->getText(), $this->driver->findElement(WebDriverBy::className('bootstrap-dialog-message'))->getText());
    }

    /**
     * @Then /^I see the form to submit my favorites$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeThePrefilledFormToSubmitMyFavorites() {
        self::iSeeTheFormToSubmitMyFavorites($this->user->getName(), $this->user->getEmail());
    }

    /**
     * @param $name
     * @param $email
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    private function iSeeTheFormToSubmitMyFavorites($name, $email) {
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('submit'))));
        Assert::assertEquals('Have you finished making your selections?
Submit your selections to us, along with any comments you may have. We will receive your request and start processing your order as soon as possible.
Name
Email
Comment',
            $this->driver->findElement(WebDriverBy::cssSelector('#submit .modal-body'))->getText(), $this->driver->findElement(WebDriverBy::cssSelector('#submit .modal-body'))->getText());
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('submit-name'))->isDisplayed());
        Assert::assertEquals($name, $this->driver->findElement(WebDriverBy::id('submit-name'))->getAttribute('value'), $this->driver->findElement(WebDriverBy::id('submit-name'))->getAttribute('value'));
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('submit-email'))->isDisplayed());
        Assert::assertEquals($email, $this->driver->findElement(WebDriverBy::id('submit-email'))->getAttribute('value'), $this->driver->findElement(WebDriverBy::id('submit-email'))->getAttribute('value'));
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('submit-comment'))->isDisplayed());
        Assert::assertEquals('', $this->driver->findElement(WebDriverBy::id('submit-comment'))->getAttribute('value'), $this->driver->findElement(WebDriverBy::id('submit-comment'))->getAttribute('value'));
    }

    /**
     * @Then /^I see the empty form to submit my favorites$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeTheEmptyFormToSubmitMyFavorites() {
        self::iSeeTheFormToSubmitMyFavorites('', '');
    }

    /**
     * @Then /^I see an error message indicating no files are available to download$/
     */
    public function iSeeAnErrorMessageIndicatingNoFilesAreAvailableToDownload() {
        CustomAsserts::errorMessage($this->driver, 'There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.');
    }

    /**
     * @Then /^I see an info message indicating download will start shortly$/
     */
    public function iSeeAnInfoMessageIndicatingDownloadWillStartShortly() {
        CustomAsserts::infoMessage($this->driver, 'We are compressing your images for download. They should automatically start downloading shortly.');
    }

    /**
     * @Then /^I see a cookie with album (\d+)$/
     * @param $albumId
     * @throws NoSuchCookieException
     */
    public function iSeeACookieWithMyAlbum($albumId) {
        $sql = new Sql();
        $code = $sql->getRow("SELECT * FROM `albums` WHERE `id` = $albumId;")['code'];
        $sql->disconnect();
        $cookie = $this->driver->manage()->getCookieNamed('searched');
        Assert::assertEquals(md5('album' . $code), json_decode(urldecode($cookie->getValue()), true)[$albumId]);
    }

    /**
     * @Then /^I see album (\d+) listed$/
     * @param $albumId
     */
    public function iSeeAlbumListed($albumId) {
        Assert::assertStringEndsWith("album.php?album=$albumId", $this->driver->findElement(WebDriverBy::linkText("Album $albumId"))->getAttribute('href'), $this->driver->findElement(WebDriverBy::linkText("Album $albumId"))->getAttribute('href'));
    }

    /**
     * @Then /^I see (\d+) album(s?) listed$/
     * @param $count
     * @throws Exception
     */
    public function iSeeAlbumsListed($count) {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector("tbody tr:nth-child($count)")));
        Assert::assertEquals($count, sizeof($this->driver->findElements(WebDriverBy::cssSelector('tbody tr[role="row"]'))));
    }

    /**
     * @Then /^I see an error message indicating no album exists$/
     */
    public function iSeeAnErrorMessageIndicatingNoAlbumExists() {
        CustomAsserts::errorMessage($this->driver, 'That code does not match any albums');
    }

    /**
     * @Then /^I see an error message indicating album code required$/
     */
    public function iSeeAnErrorMessageIndicatingAlbumCodeRequired() {
        CustomAsserts::errorMessage($this->driver, 'Album code can not be blank');
    }

    /**
     * @Then /^I see an info message indicating album successfully added$/
     */
    public function iSeeAnInfoMessageIndicatingAlbumSuccessfullyAdded() {
        CustomAsserts::infoMessage($this->driver, 'Added album to your list');
    }

    /**
     * @Then /^I see album (\d+) download with my favorites$/
     * @param $album
     */
    public function iSeeAlbumDownloadWithMyFavorites($album) {
        date_default_timezone_set('America/New_York');
        $now = date("Y-m-d H-i-s");
        $count = 0;
        $filename = getenv('HOME') . DIRECTORY_SEPARATOR . 'Downloads' . DIRECTORY_SEPARATOR . "Album $album $now.zip";
        while (!file_exists($filename)) {
            sleep(1);
            $count++;
            if ($count > 30) {
                break;
            }
        }
        Assert::assertTrue(file_exists($filename));
        $za = new ZipArchive();
        $za->open($filename);
        $sql = new Sql();
        $favs = array_column($sql->getRows("SELECT * FROM `download_rights` INNER JOIN `favorites` ON download_rights.user = favorites.user AND download_rights.album = favorites.album AND download_rights.image = favorites.image WHERE favorites.album = $album AND favorites.user = {$this->user->getId()}"), 'image');
        Assert::assertEquals(sizeof($favs), $za->numFiles);
        for ($i = 0; $i < sizeof($favs); $i++) {
            $imgLoc = $sql->getRow("SELECT * FROM album_images WHERE album = $album AND id = {$favs[$i]};")['location'];
            $parts = explode('/', $imgLoc);
            $img = $parts[sizeof($parts) - 1];
            Assert::assertEquals($img, $za->statIndex($i)['name'], $za->statIndex($i)['name']);
        }
        $sql->disconnect();
        // cleanup
        unlink($filename);
    }

    /**
     * @Then /^I see album (\d+) download with images "([^"]*)"$/
     * @param $album
     * @param $images
     */
    public function iSeeAlbumDownloadWithImages($album, $images) {
        date_default_timezone_set('America/New_York');
        $now = date("Y-m-d H-i-s");
        $count = 0;
        $filename = getenv('HOME') . DIRECTORY_SEPARATOR . 'Downloads' . DIRECTORY_SEPARATOR . "Album $album $now.zip";
        while (!file_exists($filename)) {
            sleep(1);
            $count++;
            if ($count > 120) {
                break;
            }
        }
        Assert::assertTrue(file_exists($filename));
        $images = explode(", ", $images);
        $za = new ZipArchive();
        $za->open($filename);
        $sql = new Sql();
        Assert::assertEquals(sizeof($images), $za->numFiles);
        for ($i = 0; $i < sizeof($images); $i++) {
            $imgLoc = $sql->getRow("SELECT * FROM album_images WHERE album = $album AND sequence = " . ($images[$i] - 1))['location'];
            $parts = explode('/', $imgLoc);
            $img = $parts[sizeof($parts) - 1];
            Assert::assertEquals($img, $za->statIndex($i)['name'], $za->statIndex($i)['name']);
        }
        $sql->disconnect();
        // cleanup
        unlink($filename);
    }

    /**
     * @Then /^the submit submission button is disabled$/
     */
    public function theSubmitSubmissionButtonIsDisabled() {
        Assert::assertFalse($this->driver->findElement(WebDriverBy:: id('submit-send'))->isEnabled());
    }

    /**
     * @Then /^the confirm submission dialog is no longer present$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function theConfirmSubmissionDialogIsNoLongerPresent() {
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('submit')))));
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('submit'))->isDisplayed());
    }

    /**
     * @Then /^I see (\d+) "([^"]*)" "([^"]*)" "([^"]*)" price calculated$/
     * @param $howMany
     * @param $productCategory
     * @param $productName
     * @param $productSize
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeePriceCalculated($howMany, $productCategory, $productSize, $productName) {
        $album = new Album($this->driver, $this->wait);
        $productRow = $album->getProductRow($productCategory, $productName, $productSize);
        $sql = new Sql();
        $productType = $sql->getRow("SELECT * FROM product_types WHERE category = '" . strtolower($productCategory) . "' AND name = '$productName';")['id'];
        $product = $sql->getRow("SELECT * FROM products WHERE product_type = '$productType' AND size = '$productSize';")['id'];
        $productPrice = $sql->getRow("SELECT * FROM products WHERE product_type = '$productType' AND size = '$productSize';")['price'];
        $sql->disconnect();
        $expected = "$" . number_format((float)$howMany * $productPrice, 2, '.', '');
        if ($howMany == 0) {
            $expected = "--";
        }
        $this->wait->until(WebDriverExpectedCondition::elementTextIs(WebDriverBy::cssSelector("tr[product-id='$product'] .product-total"), $expected));
        Assert::assertEquals($expected, $productRow->findElement(WebDriverBy::className('product-total'))->getText());
    }

    /**
     * @Then /^I see the cart count is "([^"]*)"$/
     * @param $cartCount
     */
    public function iSeeTheCartCountIs($cartCount) {
        Assert::assertEquals($cartCount, $this->driver->findElement(WebDriverBy::id('cart-count'))->getText());
    }

    /**
     * @Then /^I see (\d+) cart item(s?)$/
     * @param $count
     */
    public function iSeeCartItems($count) {
        $this->driver->wait(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('cart-items'))));
        Assert::assertEquals($count, sizeof($this->driver->findElements(WebDriverBy::cssSelector('#cart-items tr'))));
    }

    /**
     * @Then /^I see album (\d+) image (\d+) has (\d+) "([^"]*)" "([^"]*)" "([^"]*)" listed$/
     * @param $albumId
     * @param $image
     * @param $howMany
     * @param $productCategory
     * @param $productSize
     * @param $productName
     */
    public function iSeeAlbumImageHasListed($albumId, $image, $howMany, $productCategory, $productSize, $productName) {
        $album = new Album($this->driver, $this->wait);
        $productRows = $album->getCartRows($albumId, $image, $productCategory, $productSize, $productName);
        $productPrice = $album->getProductPrice($productCategory, $productSize, $productName);
        $productOptions = $album->getProductOptions($productCategory, $productName);
        Assert::assertEquals($howMany, sizeof($productRows));
        foreach ($productRows as $imageRow) {
            $this->driver->wait(WebDriverExpectedCondition::visibilityOf($imageRow));
            Assert::assertTrue($imageRow->isDisplayed());
            Assert::assertEquals($productName, $imageRow->findElements(WebDriverBy::tagName('td'))[2]->getText());
            Assert::assertEquals($productSize, $imageRow->findElements(WebDriverBy::tagName('td'))[3]->getText());
            Assert::assertEquals("$" . number_format((float)$productPrice, 2, '.', ''), $imageRow->findElements(WebDriverBy::tagName('td'))[4]->getText());
            //are there options?
            if (sizeof($productOptions) > 0) {
                Assert::assertTrue($imageRow->findElement(WebDriverBy::cssSelector('td:nth-child(6) > select'))->isDisplayed());
            } else {
                Assert::assertEquals('', $imageRow->findElements(WebDriverBy::tagName('td'))[5]->getText());
            }
        }
    }

    /**
     * @Then /^the place order button is disabled$/
     */
    public function thePlaceOrderButtonIsDisabled() {
        Assert::assertFalse($this->driver->findElement(WebDriverBy:: id('cart-submit'))->isEnabled());
    }

    /**
     * @Then /^the place order button is enabled$/
     */
    public function thePlaceOrderButtonIsEnabled() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy:: id('cart-submit'))->isEnabled());
    }

    /**
     * @Then /^I see option is invalid for "([^"]*)" album (\d+) image (\d+) "([^"]*)" "([^"]*)" "([^"]*)"$/
     * @param $ord
     * @param $albumId
     * @param $image
     * @param $productCategory
     * @param $productSize
     * @param $productName
     */
    public function iSeeOptionIsInvalidForAlbumImage($ord, $albumId, $image, $productCategory, $productSize, $productName) {
        $album = new Album($this->driver, $this->wait);
        $productRows = $album->getCartRows($albumId, $image, $productCategory, $productSize, $productName);
        Assert::assertStringContainsString('has-error', $productRows[(intval($ord) - 1)]->findElements(WebDriverBy::tagName('td'))[5]->getAttribute('class'));
    }

    /**
     * @Then /^I see option is valid for "([^"]*)" album (\d+) image (\d+) "([^"]*)" "([^"]*)" "([^"]*)"$/
     * @param $ord
     * @param $albumId
     * @param $image
     * @param $productCategory
     * @param $productSize
     * @param $productName
     */
    public function iSeeOptionIsValidForAlbumImage($ord, $albumId, $image, $productCategory, $productSize, $productName) {
        $album = new Album($this->driver, $this->wait);
        $productRows = $album->getCartRows($albumId, $image, $productCategory, $productSize, $productName);
        Assert::assertStringNotContainsString('has-error', $productRows[(intval($ord) - 1)]->findElements(WebDriverBy::tagName('td'))[5]->getAttribute('class'));
    }

    /**
     * @Then /^cart input "([^"]*)" shows as invalid$/
     * @param $field
     */
    public function cartInputShowsAsInvalid($field) {
        $input = $this->driver->findElement(WebDriverBy::id('cart-' . $field));
        Assert::assertStringContainsString('has-error', $input->findElement(WebDriverBy::xpath('..'))->getAttribute('class'));
    }

    /**
     * @Then /^I see an info message indicating forwarding to paypal$/
     */
    public function iSeeAnInfoMessageIndicatingForwardingToPaypal() {
        CustomAsserts::infoMessage($this->driver, 'Thank you for submitting your request. Your request is being processed, and you should be forwarded to paypal\'s payment screen within a few seconds. If you are not, please contact us and we\'ll try to resolve your issue as soon as we can.');
    }

    /**
     * @Then /^I am forwarded to the paypal page$/
     */
    public function iAmForwardedToThePaypalPage() {
        //TODO - This isn't currently working, need to circle back to this
        throw new PendingException('Need to fix this functionality');
    }

    /**
     * @Then /^I see the tax calculated as \$([0-9]+\.[0-9]{2})$/
     * @param $tax
     */
    public function iSeeTheTaxCalculatedAs($tax) {
        Assert::assertEquals('$' . $tax, $this->driver->findElement(WebDriverBy::id('cart-tax'))->getText(), $this->driver->findElement(WebDriverBy::id('cart-tax'))->getText());
    }

    /**
     * @Then /^I see the total calculated as \$([0-9]+\.[0-9]{2})$/
     * @param $total
     */
    public function iSeeTheTotalCalculatedAs($total) {
        Assert::assertEquals('$' . $total, $this->driver->findElement(WebDriverBy::id('cart-total'))->getText(), $this->driver->findElement(WebDriverBy::id('cart-total'))->getText());
    }

    /**
     * @Then /^I see album (\d+) album (.*)/
     * @param $albumId
     * @param $albumAttribute
     */
    public function iSeeAlbumAlbum($albumId, $albumAttribute) {
        $album = new Album($this->driver, $this->wait);
        $albumRow = $album->getAlbumRow($albumId);
        Assert::assertTrue($albumRow->findElement(WebDriverBy::className('album-' . str_replace(' ', '-', $albumAttribute)))->isDisplayed());
        $sql = new Sql();
        $albumInfo = $sql->getRow("SELECT * FROM albums WHERE id = $albumId");
        $sql->disconnect();
        $expected = $albumInfo[$this->toCamelCase($albumAttribute)];
        if ($albumAttribute == 'date') {
            $expected = explode(' ', $expected)[0];
        }
        Assert::assertEquals($expected, $albumRow->findElement(WebDriverBy::className('album-' . str_replace(' ', '-', $albumAttribute)))->getText());
    }

    /**
     * @param $string
     * @param false $capitalizeFirstCharacter
     * @return string
     */
    private function toCamelCase($string, $capitalizeFirstCharacter = false): string {
        $str = str_replace(' ', '', ucwords($string));
        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }
        return $str;
    }

    /**
     * @Then /^I don't see album (\d+) album (.*)/
     * @param $albumId
     * @param $albumAttribute
     */
    public function iSeeDontAlbumAlbum($albumId, $albumAttribute) {
        $album = new Album($this->driver, $this->wait);
        $albumRow = $album->getAlbumRow($albumId);
        Assert::assertEquals(0, sizeof($albumRow->findElements(WebDriverBy::className('album-' . str_replace(' ', '-', $albumAttribute)))));
    }

    /**
     * @Then /^I don't see ability to add an album$/
     */
    public function iDonTSeeAbilityToAddAnAlbum() {
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('add-album-btn'))));
    }

    /**
     * @Then /^I see ability to add an album$/
     */
    public function iSeeAbilityToAddAnAlbum() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('add-album-btn'))->isDisplayed());
    }

    /**
     * @Then /^I don't see album (\d+) edit icon$/
     * @param $albumId
     */
    public function iDonTSeeAlbumEditIcon($albumId) {
        $album = new Album($this->driver, $this->wait);
        $albumRow = $album->getAlbumRow($albumId);
        Assert::assertEquals(0, sizeof($albumRow->findElements(WebDriverBy::className('edit-album-btn'))));
    }

    /**
     * @Then /^I don't see album (\d+) log icon$/
     * @param $albumId
     */
    public function iDonTSeeAlbumLogIcon($albumId) {
        $album = new Album($this->driver, $this->wait);
        $albumRow = $album->getAlbumRow($albumId);
        Assert::assertEquals(0, sizeof($albumRow->findElements(WebDriverBy::className('view-album-log-btn'))));
    }

    /**
     * @Then /^I see album (\d+) edit icon$/
     * @param $albumId
     */
    public function iSeeAlbumEditIcon($albumId) {
        $album = new Album($this->driver, $this->wait);
        $albumRow = $album->getAlbumRow($albumId);
        Assert::assertTrue($albumRow->findElement(WebDriverBy::className('edit-album-btn'))->isDisplayed());
    }

    /**
     * @Then /^I see album (\d+) log icon$/
     * @param $albumId
     */
    public function iSeeAlbumLogIcon($albumId) {
        $album = new Album($this->driver, $this->wait);
        $albumRow = $album->getAlbumRow($albumId);
        Assert::assertTrue($albumRow->findElement(WebDriverBy::className('view-album-log-btn'))->isDisplayed());
    }

    /**
     * @Then /^I see an error message indicating album name is required$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeAnErrorMessageIndicatingAlbumNameIsRequired() {
        CustomAsserts::errorMessage($this->driver, 'Album name can not be blank');
    }

    /**
     * @Then /^I see the album details modal for album (\d+)$/
     * @param $albumId
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeTheAlbumDetailsModalForAlbum($albumId) {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('new-album-name')));
        $sql = new Sql();
        $album = $sql->getRow("SELECT * FROM `albums` WHERE `albums`.`id` = $albumId;");
        $sql->disconnect();
        Assert::assertEquals($albumId, $this->driver->findElement(WebDriverBy::id('album'))->getAttribute('album-id'));
        Assert::assertEquals($album['name'], $this->driver->findElement(WebDriverBy::id('new-album-name'))->getAttribute('value'));
        Assert::assertEquals($album['description'], $this->driver->findElement(WebDriverBy::id('new-album-description'))->getAttribute('value'));
        Assert::assertEquals(substr($album['date'], 0, 10), $this->driver->findElement(WebDriverBy::id('new-album-date'))->getAttribute('value'));
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('new-album-code'))));
    }

        /**
     * @Then /^I see the edit album details modal for album (\d+)$/
     * @param $albumId
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeTheEditAlbumDetailsModalForAlbum($albumId) {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('new-album-name')));
        $sql = new Sql();
        $album = $sql->getRow("SELECT * FROM `albums` WHERE `albums`.`id` = $albumId;");
        $sql->disconnect();
        Assert::assertEquals($albumId, $this->driver->findElement(WebDriverBy::id('album'))->getAttribute('album-id'));
        Assert::assertEquals($album['name'], $this->driver->findElement(WebDriverBy::id('new-album-name'))->getAttribute('value'));
        Assert::assertEquals($album['description'], $this->driver->findElement(WebDriverBy::id('new-album-description'))->getAttribute('value'));
        Assert::assertEquals(substr($album['date'], 0, 10), $this->driver->findElement(WebDriverBy::id('new-album-date'))->getAttribute('value'));
        Assert::assertEquals($album['code'], $this->driver->findElement(WebDriverBy::id('new-album-code'))->getAttribute('value'));
    }

    /**
     * @Then /^I don't see album (\d+) listed$/
     * @param $albumId
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iDonTSeeAlbumListed($albumId) {
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector("tr[album-id='$albumId']"))));
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::cssSelector("tr[album-id='$albumId']"))));
        //remove this album from our deletion list
        for ($i = 0; $i < sizeof($this->albumIds); $i++) {
            if ($this->albumIds[$i] == $albumId) {
                unset($this->albumIds[$i]);
                $this->albumIds = array_values($this->albumIds);
            }
        }
    }

    /**
     * @Then /^I see the ability to set access$/
     */
    public function iSeeTheAbilityToSetAccess() {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('albumDiv'))->isDisplayed());
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('downloadDiv'))->isDisplayed());
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('shareDiv'))->isDisplayed());
    }

    /**
     * @Then /^I see users "([\d,]*)" with album access$/
     * @param $users
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeUserWithAlbumAccess($users) {
        $album = new Album($this->driver, $this->wait);
        if ($users == "") {
            $users = [];
        } else {
            $users = explode(",", $users);
        }
        $accessors = $album->getAlbumAccessors();
        Assert::assertEquals(sizeof($users), sizeof($accessors));
        for ($i = 0; $i < sizeof($accessors); $i++) {
            Assert::assertEquals($users[$i], $accessors[$i]->getAttribute('user-id'));
        }
    }

    /**
     * @Then /^I see users "([\d,]*)" with download access$/
     * @param $users
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeUsersWithDownloadAccess($users) {
        $album = new Album($this->driver, $this->wait);
        if ($users == "") {
            $users = [];
        } else {
            $users = explode(",", $users);
        }
        $downloaders = $album->getAlbumDownloaders();
        Assert::assertEquals(sizeof($users), sizeof($downloaders));
        for ($i = 0; $i < sizeof($downloaders); $i++) {
            Assert::assertEquals($users[$i], $downloaders[$i]->getAttribute('user-id'));
        }
    }

    /**
     * @Then /^I see users "([\d,]*)" with share access$/
     * @param $users
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeUsersWithShareAccess($users) {
        $album = new Album($this->driver, $this->wait);
        if ($users == "") {
            $users = [];
        } else {
            $users = explode(",", $users);
        }
        $sharers = $album->getAlbumSharers();
        Assert::assertEquals(sizeof($users), sizeof($sharers));
        for ($i = 0; $i < sizeof($sharers); $i++) {
            Assert::assertEquals($users[$i], $sharers[$i]->getAttribute('user-id'));
        }
    }

    /**
     * @Then /^users "([^"]*)" have access to album (\d+)$/
     * @param $users
     * @param $albumId
     */
    public function usersHaveAlbumAccess($users, $albumId) {
        if ($users == "") {
            $users = [];
        } else {
            $users = explode(",", $users);
        }
        $sql = new Sql();
        $accessors = $sql->getRows("SELECT * FROM albums_for_users WHERE album = $albumId");
        $sql->disconnect();
        Assert::assertEquals(sizeof($users), sizeof($accessors));
        for ($i = 0; $i < sizeof($accessors); $i++) {
            Assert::assertEquals($users[$i], $accessors[$i]['user']);
        }
    }

    /**
     * @Then /^users "([^"]*)" can download album (\d+)$/
     * @param $users
     * @param $albumId
     */
    public function usersCanDownloadAlbum($users, $albumId) {
        if ($users == "") {
            $users = [];
        } else {
            $users = explode(",", $users);
        }
        $sql = new Sql();
        $downloaders = $sql->getRows("SELECT * FROM download_rights WHERE album = $albumId");
        $sql->disconnect();
        Assert::assertEquals(sizeof($users), sizeof($downloaders));
        for ($i = 0; $i < sizeof($downloaders); $i++) {
            Assert::assertEquals($users[$i], $downloaders[$i]['user']);
        }
    }

    /**
     * @Then /^users "([^"]*)" can share album (\d+)$/
     * @param $users
     * @param $albumId
     */
    public function usersCanShareAlbum($users, $albumId) {
        if ($users == "") {
            $users = [];
        } else {
            $users = explode(",", $users);
        }
        $sql = new Sql();
        $sharers = $sql->getRows("SELECT * FROM share_rights WHERE album = $albumId");
        $sql->disconnect();
        Assert::assertEquals(sizeof($users), sizeof($sharers));
        for ($i = 0; $i < sizeof($sharers); $i++) {
            Assert::assertEquals($users[$i], $sharers[$i]['user']);
        }
    }

    /**
     * @Then /^I see thumbnails being created$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeThumbnailsBeingCreated() {
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('resize-progress'))));
        $this->wait->until(function () {
            return $this->driver->findElement(WebDriverBy::id('resize-progress'))->getText() == 'Done';
        });
    }

    /**
     * @Then /^I have created "([^"]*)" thumbnail images for album (\d+)$/
     */
    public function iHaveCreatedThumbnailImages($thumbType, $albumId) {
        $sql = new Sql();
        $albumLocation = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'albums' . DIRECTORY_SEPARATOR . $sql->getRow("SELECT * FROM albums WHERE albums.id = $albumId")['location'];
        $images = $sql->getRows("SELECT * FROM album_images WHERE album = $albumId");
        $sql->disconnect();
        Assert::assertTrue(is_dir($albumLocation . DIRECTORY_SEPARATOR . 'full'));
        foreach ($images as $image) {
            //ensure original files are in 'full' directory
            $parts = explode(DIRECTORY_SEPARATOR, $image['location']);
            array_splice($parts, 3, 0, "full");
            CustomAsserts::filesAreEqual(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content' . implode(DIRECTORY_SEPARATOR, $parts));
            switch ($thumbType) {
                case 'proof':
                    $file = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower-proof.jpeg';
                    break;
                case 'watermark':
                    $file = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower-watermark.jpeg';
                    break;
                case 'nothing':
                    $file = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower-thumbed.jpeg';
                    break;
            }
            CustomAsserts::filesAreEqual($file, dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content' . $image['location']);
        }
    }

    /**
     * @Then /^I see album logs:$/
     * @param TableNode $table
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeAlbumLogs(TableNode $table) {
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::className('album-logs'))));
        $logRows = $this->driver->findElements(WebDriverBy::cssSelector('.album-logs > .row'));
        Assert::assertEquals(count($table->getRows()) - 1, sizeof($logRows));
        for ($i = 0; $i < sizeof($logRows); $i++) {
            $logRowDivs = $logRows[$i]->findElements(WebDriverBy::tagName('div'));
            $x = $table->getRow($i + 1);
            Assert::assertEquals($x[0], $logRowDivs[0]->getText(), $logRowDivs[0]->getText());
            Assert::assertEquals($x[1], $logRowDivs[1]->getText(), $logRowDivs[1]->getText());
        }
    }

    /**
     * @Then /^I don't see the ability to set access$/
     */
    public function iDonTSeeTheAbilityToSetAccess() {
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::className('glyphicon-picture'))));
    }
}