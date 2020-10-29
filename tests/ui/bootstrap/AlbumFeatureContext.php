<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use Sql;
use User;

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
    private $baseUrl;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 10);
        $this->baseUrl = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getBaseUrl();
        $this->user = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getUser();
    }

    /**
     * @AfterScenario
     */
    public function cleanup() {
        $sql = new Sql();
        $sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 99999;");
        $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $sql->disconnect();
    }

    /**
     * @Given /^an album exists with code "([^"]*)"$/
     */
    public function anAlbumExistsWithCode($code) {
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('99999', 'sample-album', 'sample album for testing', 'sample', 1, '$code');");
        $sql->disconnect();
    }

    /**
     * @Given /^I see a cookie with my album$/
     */
    public function iSeeACookieWithMyAlbum() {
        $sql = new Sql();
        $code = $sql->getRow("SELECT * FROM `albums` WHERE `id` = 99999;")['code'];
        $sql->disconnect();
        $cookie = $this->driver->manage()->getCookieNamed('searched');
        Assert::assertEquals(md5('album' . $code), json_decode(urldecode($cookie->getValue()), true)[99999]);
    }
}