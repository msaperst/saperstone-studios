<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use CustomAsserts;
use Exception;
use Facebook\WebDriver\Exception\NoSuchCookieException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use Sql;
use ui\models\Album;
use User;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Album.php';

class AlbumFeatureContext implements Context {

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
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 10);
        $this->user = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getUser();
    }

    /**
     * @AfterScenario
     * @throws Exception
     */
    public function cleanup() {
        $sql = new Sql();
        foreach( $this->albumIds as $albumId) {
            $sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = $albumId;");
            $sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = $albumId;");
            $sql->executeStatement("DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = $albumId;");
            $sql->executeStatement("DELETE FROM `favorites` WHERE `favorites`.`album` = $albumId;");
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
     * @Given /^album (\d+) exists with (\d+) images$/
     * @param $albumId
     * @param $images
     * @throws Exception
     */
    public function albumExistsWithImages($albumId, $images) {
        $this->albumIds[] = $albumId;
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `images`) VALUES ($albumId, 'Album $albumId', 'sample album for testing', 'sample', 1, '$images');");
        for ($i = 0; $i < $images; $i++) {
            $sql->executeStatement("INSERT INTO `album_images` (`album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES ('$albumId', 'Image $i', $i, '', '/albums/sample-album/sample.jpg', '400', '300', '1');");
        }
        $oldMask = umask(0);
        if (!is_dir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/albums/sample-album')) {
            mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/albums/sample-album');
        }
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/albums/sample-album', 0777);
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/albums/sample-album/sample.jpg');
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/albums/sample-album/sample.jpg', 0777);
        umask($oldMask);
        $sql->disconnect();
    }

    /**
     * @Given /^I have access to album (\d+)$/
     * @param $albumId
     * @throws Exception
     */
    public function iHaveAccessToAlbum($albumId) {
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
        $sql->executeStatement("UPDATE `album_images` SET caption = '$caption' WHERE `album` = $album AND sequence = " . ($image-1));
        $sql->disconnect();
    }

    /**
     * @Given /^album (\d+) image (\d+) is a favorite$/
     * @param $album
     * @param $image
     */
    public function albumImageIsAFavorite($album, $image) {
        $sql = new Sql();
        $img = $sql->getRow("SELECT * FROM `album_images` WHERE `album` = $album AND `sequence` = " . ($image-1))['id'];
        $sql->executeStatement("INSERT INTO `favorites` VALUES( {$this->user->getId()}, $album, $img);");
        $sql->disconnect();
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
     * @When /^I hover over album image (\d+)$/
     * @param $imgNum
     */
    public function iHoverOverImage($imgNum) {
        $album = new Album($this->driver, $this->wait);
        $this->image = $album->hoverOverImage($imgNum);
    }

    /**
     * @When /^I view album image (\d+)$/
     * @param $imgNum
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
     * @Given /^I defavorite the image$/
     */
    public function iDefavoriteTheImage() {
        $album = new Album($this->driver, $this->wait);
        $album->unFavoriteImage();
    }

    /**
     * @Then /^I see the "([^"]*)" album images load$/
     * @param $ord
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
     */
    public function iSeeTheCaptionDisplayed($caption) {
        $album = new Album($this->driver, $this->wait);
        $img = $album->getSlideShowImage();
        Assert::assertEquals($caption, $img->findElement(WebDriverBy::tagName('h2'))->getText());
    }

    /**
     * @Then /^I do not see any album captions$/
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
     * @throws Exception
     */
    public function iSeeAlbumImageAsAFavorite($image) {
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy:: cssSelector("li[image-id='" . ($image-1) . "']"))));
        Assert::assertTrue($this->driver->findElement(WebDriverBy:: cssSelector("li[image-id='" . ($image-1) . "']"))->isDisplayed());
    }
}