<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use Sql;
use ui\models\Gallery;
use User;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Gallery.php';

class GalleryFeatureContext implements Context {

    /**
     * @var Sql
     */
    private $sql;
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
    private $baseUrl;
    /**
     * The image we're interacting with
     * @var RemoteWebElement
     */
    private $image;

    /** @BeforeScenario
     * @param BeforeScenarioScope $scope
     * @throws Exception
     */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getDriver();
        $this->user = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getUser();
        $this->wait = new WebDriverWait($this->driver, 10);
        $this->baseUrl = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getBaseUrl();
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `galleries` (`id`, `parent`, `image`, `title`, `comment`) VALUES ('999', '1', 'sample.jpg', 'Sample', NULL);");
        for ($i = 0; $i <= 15; $i++) {
            $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES ((9999 + $i), '999', 'Image $i', $i, '', '/portrait/img/sample/sample.jpg', '400', '300', '1');");
        }
        $this->sql->executeStatement("UPDATE `gallery_images` SET caption = 'sample caption' WHERE id = 10000");
        $oldmask = umask(0);
        if (!is_dir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/portrait/sample')) {
            mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/portrait/sample');
        }
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/portrait/sample', 0777);
        copy(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/portrait/sample/sample.jpg');
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/portrait/sample/sample.jpg', 0777);
        umask($oldmask);
    }

    /**
     * @AfterScenario
     * @throws Exception
     */
    public function cleanup() {
        $this->sql->executeStatement("DELETE FROM `galleries` WHERE `galleries`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `gallery_images` WHERE `gallery_images`.`gallery` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `galleries`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `galleries` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `gallery_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `gallery_images` AUTO_INCREMENT = $count;");
        system("rm -rf " . escapeshellarg(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/portrait/sample'));
        $this->sql->disconnect();
    }

    /**
     * @When /^I hover over image (\d+)$/
     * @param $imgNum
     */
    public function iHoverOverImage($imgNum) {
        $gallery = new Gallery($this->driver, $this->wait);
        $this->image = $gallery->hoverOverImage($imgNum);
    }


    /**
     * @When /^I view image (\d+)$/
     * @param $imgNum
     */
    public function iViewImage($imgNum) {
        $gallery = new Gallery($this->driver, $this->wait);
        $gallery->openSlideShow($imgNum);
    }

    /**
     * @When /^I advance to the next image$/
     */
    public function iAdvanceToTheNextImage() {
        $gallery = new Gallery($this->driver, $this->wait);
        $gallery->advanceToNextImage();
    }

    /**
     * @When /^I advance to the previous image$/
     */
    public function iAdvanceToThePreviousImage() {
        $gallery = new Gallery($this->driver, $this->wait);
        $gallery->advanceToPreviousImage();
    }

    /**
     * @When /^I skip to image (\d+)$/
     * @param $img
     */
    public function iSkipToImage($img) {
        $gallery = new Gallery($this->driver, $this->wait);
        $gallery->advanceToImage($img);
    }

    /**
     * @Then /^I see the "([^"]*)" gallery images load$/
     * @param $ord
     */
    public function iSeeTheGalleryImagesLoad($ord) {
        $gallery = new Gallery($this->driver, $this->wait);
        $row = intval($ord);
        $gallery->waitForImagesToLoad($row);
        $s = ($row - 1) * 4;
        for ($i = 0; $i < 4; $i++) {
            $image = $this->driver->findElement((WebDriverBy::cssSelector("#col-$i > div.gallery:nth-child($row)")));
            Assert::assertEquals('Image ' . ($s + $i), $image->findElement(WebDriverBy::tagName('img'))->getAttribute('alt'), $image->findElement(WebDriverBy::tagName('img'))->getAttribute('alt'));
        }
    }

    /**
     * @Then /^I see the info icon on image (\d+)$/
     * @param $imgNum
     */
    public function iSeeTheInfoIconOnImage($imgNum) {
        Assert::assertTrue($this->image->findElement(WebDriverBy::className('info'))->isDisplayed());
    }

    /**
     * @Then /^I see image (\d+) in the preview modal$/
     * @param $imgNum
     */
    public function iSeeImageInThePreviewModal($imgNum) {
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('Sample'))->isDisplayed());
        $activeImage = $this->driver->findElement(WebDriverBy::cssSelector('div.active'));
        Assert::assertEquals('Image ' . ($imgNum - 1), $activeImage->findElement(WebDriverBy::tagName('div'))->getAttribute('alt'), $activeImage->findElement(WebDriverBy::tagName('div'))->getAttribute('alt'));
    }

    /**
     * @Then /^I see the caption "([^"]*)" displayed$/
     */
    public function iSeeTheCaptionDisplayed($caption) {
        $gallery = new Gallery($this->driver, $this->wait);
        $img = $gallery->getSlideShowImage();
        Assert::assertEquals($caption, $img->findElement(WebDriverBy::tagName('h2'))->getText());
    }

    /**
     * @Then /^I do not see any captions$/
     */
    public function iDoNotSeeAnyCaptions() {
        $gallery = new Gallery($this->driver, $this->wait);
        $img = $gallery->getSlideShowImage();
        Assert::assertEquals('', $img->findElement(WebDriverBy::tagName('h2'))->getText());
    }
}