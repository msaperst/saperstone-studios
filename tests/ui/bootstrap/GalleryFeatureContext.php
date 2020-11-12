<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
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
        for( $i = 0; $i <= 15; $i++) {
            $this->sql->executeStatement("INSERT INTO `gallery_images` (`id`, `gallery`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES ((9999 + $i), '999', 'Image $i', $i, '', '/portrait/img/sample/sample.jpg', '400', '300', '1');");
        }
        $oldmask = umask(0);
        mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'content/portrait/sample');
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
     * @Then /^I see the "([^"]*)" gallery images load$/
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


}