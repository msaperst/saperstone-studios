<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Exception;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverWait;
use PHPUnit\Framework\Assert;
use Sql;
use ui\models\Album;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Registration.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Album.php';

class NavigationFeatureContext implements Context {

    /**
     * @var RemoteWebDriver
     */
    private $driver;
    /**
     * @var WebDriverWait
     */
    private $wait;
    private $baseUrl;

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope) {
        $environment = $scope->getEnvironment();
        $this->driver = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getDriver();
        $this->wait = new WebDriverWait($this->driver, 10);
        $this->baseUrl = $environment->getContext('ui\bootstrap\BaseFeatureContext')->getBaseUrl();
    }

    /**
     * @AfterScenario
     * @throws Exception
     */
    public function cleanup() {
        $sql = new Sql();
        $sql->executeStatement("DELETE FROM `announcements` WHERE `announcements`.`id` = 999;");
        $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `announcements`;")['count'];
        $count++;
        $sql->executeStatement("ALTER TABLE `announcements` AUTO_INCREMENT = $count;");
        $sql->disconnect();
    }

    /**
     * @Given /^I haven't reviewed the cookie policy$/
     */
    public function iHavenTReviewedTheCookiePolicy() {
        $this->driver->manage()->deleteCookieNamed('CookieShow');
        $this->driver->navigate()->refresh();
    }

    /**
     * @Given /^I have reviewed the cookie policy$/
     */
    public function iHaveReviewedTheCookiePolicy() {
        $this->driver->manage()->deleteCookieNamed('CookieShow');
        $cookie = new Cookie('CookieShow', 'true');
        $this->driver->manage()->addCookie($cookie);
        $this->driver->navigate()->refresh();
    }

    /**
     * @Given /^I am on the "([^"]*)" page$/
     * @param $page
     */
    public function iAmOnThePage($page) {
        $this->driver->get($this->baseUrl . $page);
    }

    /**
     * @Given /^there is an announcement$/
     * @throws Exception
     */
    public function thereIsAnAnnouncement() {
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `announcements` (`id`, `message`, `path`, `start`, `end`, `dismissible`) VALUES (999, '<a href=\'/blog/post.php?p=458\'>Information Regarding Covid-19</a>', '/', '2000-07-23 00:00:00', '3000-12-31 00:00:00', 1);");
        $sql->disconnect();
        $this->driver->navigate()->refresh();
    }

    /**
     * @When /^I reload the page$/
     */
    public function iReloadThePage() {
        $this->driver->navigate()->refresh();
    }

    /**
     * @When /^I scroll to the bottom of the page$/
     */
    public function iScrollToTheBottomOfThePage() {
        $this->driver->executeScript("window.scrollTo(0, document.body.scrollHeight)");
    }

    /**
     * @When /^I edit the cookie options$/
     */
    public function iEditTheCookieOptions() {
        $this->driver->findElement(WebDriverBy::id('edit-cookies'))->click();
    }

    /**
     * @When /^I search for "([^"]*)" blog posts by typing$/
     * @param $search
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSearchForBlogPostsByTyping($search) {
        $this->driver->findElement(WebDriverBy::linkText('Blog'))->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('nav-search-input'))));
        $this->driver->findElement(WebDriverBy::id('nav-search-input'))->sendKeys($search)->sendKeys(WebDriverKeys::ENTER);
    }

    /**
     * @When /^I search for "([^"]*)" blog posts$/
     * @param $search
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSearchForBlogPosts($search) {
        $this->driver->findElement(WebDriverBy::linkText('Blog'))->click();
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('nav-search-input'))));
        $this->driver->findElement(WebDriverBy::id('nav-search-input'))->sendKeys($search);
        $this->driver->findElement(WebDriverBy::id('nav-search-icon'))->click();
    }

    /**
     * @When /^I append "([^"]*)" to my url$/
     * @param $hash
     */
    public function iAppendToMyUrl($hash) {
        $currentURL = $this->driver->getCurrentURL();
        $this->driver->get($currentURL . $hash);
    }

    /**
     * @When /^I dismiss the announcement$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iDismissTheAnnouncement() {
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('announcement-999'))));
        $this->driver->findElement(WebDriverBy::id('announcement-999'))->click();
    }

    /**
     * @When /^I try to search for an album$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iTryToSearchForAnAlbum() {
        $album = new Album($this->driver, $this->wait);
        $album->openFinder();
    }

    /**
     * @When /^I search for album "([^"]*)"$/
     * @param $code
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSearchForAlbum($code) {
        $album = new Album($this->driver, $this->wait);
        $album->search($code, FALSE);
    }

    /**
     * @When /^I search for and save album "([^"]*)"$/
     * @param $code
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSearchForAndSaveAlbum($code) {
        $album = new Album($this->driver, $this->wait);
        $album->search($code, true);
    }

    /**
     * @When /^I search for album "([^"]*)" with keyboard$/
     * @param $code
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSearchForAlbumWithKeyboard($code) {
        $album = new Album($this->driver, $this->wait);
        $album->searchKeyboard($code, FALSE);
    }

    /**
     * @When /^I wait for (\d+) seconds$/
     * @param $seconds
     */
    public function iWaitForSeconds($seconds) {
        sleep($seconds);
    }

    /**
     * @When /^I click the "([^"]*)" content header$/
     * @param $ord
     */
    public function iClickTheContentHeader($ord) {
        $headers = $this->driver->findElements(WebDriverBy::className('collapse-header'));
        $headers[intval($ord) - 1]->click();
        $this->iWaitForSeconds(1);
    }

    /**
     * @Then /^I am not prompted to review the privacy policy$/
     */
    public function iAmNotPromptedToReviewThePrivacyPolicy() {
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('bs-gdpr-cookies-modal'))));
    }

    /**
     * @Then /^I am prompted to review the privacy policy$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iAmPromptedToReviewThePrivacyPolicy() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('bs-gdpr-cookies-modal')));
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::id('bs-gdpr-cookies-modal'))));
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('bs-gdpr-cookies-modal'))->isDisplayed());
    }

    /**
     * @Then /^my resolution is logged$/
     */
    public function myResolutionIsLogged() {
        sleep(1);
        $this->driver->navigate()->refresh();
        $sql = new Sql();
        $userLogs = $sql->getRow("SELECT * FROM `usage` ORDER BY time DESC LIMIT 1;");
        $sql->disconnect();
        Assert::assertEquals('Chrome', $userLogs['browser']);
        Assert::assertEquals('Linux', $userLogs['os']);
        Assert::assertNotNull($userLogs['width']);
        Assert::assertNotNull(0, $userLogs['height']);
    }

    /**
     * @Then /^my resolution is not logged$/
     */
    public function myResolutionIsNotLogged() {
        sleep(1);
        $this->driver->navigate()->refresh();
        $sql = new Sql();
        $userLogs = $sql->getRow("SELECT * FROM `usage` ORDER BY time DESC LIMIT 1;");
        $sql->disconnect();
        Assert::assertEquals('Chrome', $userLogs['browser']);
        Assert::assertEquals('Linux', $userLogs['os']);
        Assert::assertNull($userLogs['width']);
        Assert::assertNull($userLogs['height']);
    }

    /**
     * @Then /^I see "([^"]*)" blog posts$/
     * @param $search
     */
    public function iSeeBlogPosts($search) {
        Assert::assertEquals("Home Blog Search $search", $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
    }

    /**
     * @Then /^I see the find album modal$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeTheFindAlbumModal() {
        $album = new Album($this->driver, $this->wait);
        $album->waitForFinder();
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('find-album-code'))->isDisplayed());
    }

    /**
     * @Then /^I see that there is no option to save album$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeThatThereIsNoOptionToSaveAlbum() {
        $album = new Album($this->driver, $this->wait);
        $album->waitForFinder();
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('find-album-add'))));
    }

    /**
     * @Then /^I see that there is an option to save album$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeThatThereIsAnOptionToSaveAlbum() {
        $album = new Album($this->driver, $this->wait);
        $album->waitForFinder();
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('find-album-add'))->isDisplayed());
    }

    /**
     * @Then /^I no longer see the announcement$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iNoLongerSeeTheAnnouncement() {
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('announcement-999'))));
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('announcement-999'))));
    }

    /**
     * @Then /^I am taken to the "([^"]*)" page$/
     * @param $page
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iAmTakenToThePage($page) {
        $this->wait->until(WebDriverExpectedCondition::urlIs($this->baseUrl . $page));
        Assert::assertEquals($this->baseUrl . $page, $this->driver->getCurrentUrl());
    }

    /**
     * @Then /^I see the "([^"]*)" content collapsed$/
     * @param $ord
     */
    public function iSeeTheContentCollapsed($ord) {
        $contents = $this->driver->findElements(WebDriverBy::className('collapse-content'));
        Assert::assertFalse($contents[intval($ord) - 1]->isDisplayed());
    }

    /**
     * @Then /^I see the "([^"]*)" content expanded$/
     * @param $ord
     */
    public function iSeeTheContentExpanded($ord) {
        $contents = $this->driver->findElements(WebDriverBy::className('collapse-content'));
        Assert::assertTrue($contents[intval($ord) - 1]->isDisplayed());
    }

    /**
     * @Given /^logs exist:$/
     * @param TableNode $table
     * @throws Exception
     */
    public function logsExist(TableNode $table) {
        $sql = new Sql();
        foreach ($table as $row) {
            $what = "'{$row['what']}'";
            if ($row['what'] == 'NULL') {
                $what = 'NULL';
            }
            $sql->executeStatement("INSERT INTO user_logs VALUE( {$row['user']}, '{$row['time']}', '{$row['action']}', $what, '{$row['album']}');");
        }
        $sql->disconnect();
    }
}