<?php

namespace ui\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Environment\Environment;
use CustomAsserts;
use Exception;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Google\Exception as ExceptionAlias;
use PHPUnit\Framework\Assert;
use Sql;
use ui\models\Album;
use User;
use ZipArchive;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Album.php';

class AlbumFeatureContext implements Context {

    private const FIXTURE_USERS = [
        '0' => ['id' => '0', 'username' => '<i>All Users</i>'],
        'msaperst' => ['id' => '1', 'username' => 'msaperst'],
        'lsaperst' => ['id' => '2', 'username' => 'lsaperst'],
        'downloader' => ['id' => '3', 'username' => 'downloader'],
        'uploader' => ['id' => '4', 'username' => 'uploader'],
    ];

    private function resolveTestUser(string $user): array {
        if (isset(self::FIXTURE_USERS[$user])) {
            return self::FIXTURE_USERS[$user];
        }

        foreach (self::FIXTURE_USERS as $fixture) {
            if ($fixture['id'] === $user) {
                return $fixture;
            }
        }

        throw new Exception("Unable to resolve test user '$user'");
    }

    private function resolveUserId(string $user): string {
        return $this->resolveTestUser($user)['id'];
    }

    private function resolveUserIds(string $users): array {
        if ($users === '') {
            return [];
        }
        return array_map(fn(string $user): string => $this->resolveUserId($user), explode(',', $users));
    }

    /**
     * Removes stale database state for a fixed-id album fixture.
     *
     * This keeps scenarios deterministic after interrupted or failed test runs
     * without silently reusing an album created by a previous scenario.
     */
    private function resetAlbumFixture(int $albumId): void {
        $sql = new Sql();
        foreach (['favorites', 'download_rights', 'share_rights', 'user_logs', 'notification_emails', 'albums_for_users', 'album_images'] as $table) {
            $sql->executeStatement("DELETE FROM `$table` WHERE `album` = ?", [$albumId]);
        }
        $sql->executeStatement("DELETE FROM `albums` WHERE `id` = ?", [$albumId]);
        $sql->disconnect();
    }

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
    private $albumFixtures = [];
    private $editingAlbumId = null;
    private $pendingAlbumFields = [];

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
            $album = $sql->getRow("SELECT * FROM albums WHERE albums.id = $albumId");
            $albumLocation = $album === null ? null : dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'albums' . DIRECTORY_SEPARATOR . $album['location'];
            $sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = $albumId;");
            $sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = $albumId;");
            $sql->executeStatement("DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = $albumId;");
            $sql->executeStatement("DELETE FROM `favorites` WHERE `favorites`.`album` = $albumId;");
            $sql->executeStatement("DELETE FROM `download_rights` WHERE `download_rights`.`album` = $albumId;");
            $sql->executeStatement("DELETE FROM `share_rights` WHERE `share_rights`.`album` = $albumId;");
            $sql->executeStatement("DELETE FROM `user_logs` WHERE `user_logs`.`album` = $albumId;");
            $sql->executeStatement("DELETE FROM `notification_emails` WHERE `notification_emails`.`album` = $albumId;");
            if ($albumLocation !== null && is_dir($albumLocation)) {
                system("rm -rf " . escapeshellarg($albumLocation));
            }
            if ($album !== null) {
                $tmpDirectory = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'tmp';
                foreach (glob($tmpDirectory . DIRECTORY_SEPARATOR . $album['name'] . ' *.zip') ?: [] as $download) {
                    @unlink($download);
                }
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

    private function rememberAlbumFixture(int $albumId, int $images = 0, string $code = ''): void {
        $imageFiles = [];
        $imageTitles = [];
        for ($i = 0; $i < $images; $i++) {
            $imageFiles[$i + 1] = "sample$i.jpg";
            $imageTitles[$i + 1] = "Image $i";
        }

        $this->albumFixtures[$albumId] = [
            'name' => "Album $albumId",
            'description' => 'sample album for testing',
            'date' => '2020-01-01',
            'lastAccessed' => '',
            'code' => $code,
            'images' => (string) $images,
            'imageFiles' => $imageFiles,
            'imageTitles' => $imageTitles,
        ];
    }

    private function fixtureImageFileName(int $albumId, int $imageNumber): string {
        Assert::assertArrayHasKey($albumId, $this->albumFixtures, "No fixture state exists for album $albumId");
        Assert::assertArrayHasKey(
            $imageNumber,
            $this->albumFixtures[$albumId]['imageFiles'],
            "No fixture image $imageNumber exists for album $albumId"
        );
        return $this->albumFixtures[$albumId]['imageFiles'][$imageNumber];
    }

    private function fixtureImageTitle(int $albumId, int $imageNumber): string {
        Assert::assertArrayHasKey($albumId, $this->albumFixtures, "No fixture state exists for album $albumId");
        Assert::assertArrayHasKey(
            $imageNumber,
            $this->albumFixtures[$albumId]['imageTitles'],
            "No fixture image $imageNumber exists for album $albumId"
        );
        return $this->albumFixtures[$albumId]['imageTitles'][$imageNumber];
    }

    private function captureAlbumFormValues(): array {
        $values = [];
        foreach (['name', 'description', 'date', 'code'] as $field) {
            $elements = $this->driver->findElements(WebDriverBy::id('new-album-' . $field));
            if ($elements !== []) {
                $values[$field] = (string) $elements[0]->getAttribute('value');
            }
        }
        return $values;
    }

    /**
     * @Given /^album (\d+) exists$/
     * @param $albumId
     * @throws Exception
     */
    public function albumExists($albumId) {
        $this->resetAlbumFixture((int) $albumId);
        $this->albumIds[] = $albumId;
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `date`, `location`, `owner`) VALUES ($albumId, 'Album $albumId', 'sample album for testing', '2020-01-01 00:00:00', 'sample', 1);");
        $sql->disconnect();
        $this->rememberAlbumFixture((int) $albumId);
    }

    /**
     * @Given /^I have created album (\d+)$/
     * @param $albumId
     * @throws Exception
     */
    public function iHaveCreatedAlbum($albumId) {
        $this->resetAlbumFixture((int) $albumId);
        $this->albumIds[] = $albumId;
        $this->user = $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->getUser();
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `date`, `location`, `owner`) VALUES ($albumId, 'Album $albumId', 'sample album for testing', '2020-01-01 00:00:00', 'sample', {$this->user->getId()});");
        $sql->disconnect();
        $this->rememberAlbumFixture((int) $albumId);
    }

    /**
     * @Given /^album (\d+) exists with code "([^"]*)"$/
     * @param $albumId
     * @param $albumCode
     * @throws Exception
     */
    public function albumExistsWithCode($albumId, $albumCode) {
        $this->resetAlbumFixture((int) $albumId);
        $this->albumIds[] = $albumId;
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `date`, `location`, `owner`, `code`) VALUES ($albumId, 'Album $albumId', 'sample album for testing', '2020-01-01 00:00:00', 'sample', 1, '$albumCode');");
        $sql->disconnect();
        $this->rememberAlbumFixture((int) $albumId, 0, (string) $albumCode);
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
        if (isset($this->albumFixtures[(int) $albumId])) {
            $this->albumFixtures[(int) $albumId]['code'] = (string) $albumCode;
        }
    }

    /**
     * @Given /^album (\d+) exists with (\d+) images$/
     * @param $albumId
     * @param $images
     * @throws Exception
     */
    public function albumExistsWithImages($albumId, $images) {
        $this->resetAlbumFixture((int) $albumId);
        $this->albumIds[] = $albumId;
        $this->user = $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->getUser();
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `date`, `location`, `owner`, `images`) VALUES ($albumId, 'Album $albumId', 'sample album for testing', '2020-01-01 00:00:00', 'sample-album', 1, '$images');");
        $this->rememberAlbumFixture((int) $albumId, (int) $images);
        $oldMask = umask(0);
        if (!is_dir(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'content/albums/sample-album')) {
            mkdir(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'content/albums/sample-album');
        }
        chmod(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'content/albums/sample-album', 0777);
        for ($i = 0; $i < $images; $i++) {
            $sql->executeStatement("INSERT INTO `album_images` (`album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES ('$albumId', 'Image $i', $i, '', '/albums/sample-album/sample$i.jpg', '400', '300', '1');");
            system('convert ' . dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "resources/flower.jpeg -gravity Center -density 90 -pointsize 200 -annotate 0 'Image $i' " . dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "content/albums/sample-album/sample$i.jpg");
            chmod(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "content/albums/sample-album/sample$i.jpg", 0777);
        }
        umask($oldMask);
        $sql->disconnect();
    }

    /**
     * @Given /^album (\d+) has (\d+) lightweight images$/
     */
    public function albumHasLightweightImages($albumId, $images): void {
        $albumId = (int) $albumId;
        $images = (int) $images;
        Assert::assertGreaterThan(0, $images);

        $albumDirectory = dirname(__DIR__, 3)
            . DIRECTORY_SEPARATOR . 'content'
            . DIRECTORY_SEPARATOR . 'albums'
            . DIRECTORY_SEPARATOR . 'sample-album';
        if (!is_dir($albumDirectory)) {
            Assert::assertTrue(mkdir($albumDirectory, 0777, true));
        }

        $sourceImage = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'resources'
            . DIRECTORY_SEPARATOR . 'flower.jpeg';

        $sql = new Sql();
        $sql->executeStatement("DELETE FROM album_images WHERE album = ?", [$albumId]);
        $sql->executeStatement(
            "UPDATE albums SET images = ?, location = 'sample-album' WHERE id = ?",
            [$images, $albumId]
        );
        $this->rememberAlbumFixture($albumId, $images);

        for ($i = 0; $i < $images; $i++) {
            $fileName = "lightweight-$i.jpg";
            $target = $albumDirectory . DIRECTORY_SEPARATOR . $fileName;
            @unlink($target);
            if (!@link($sourceImage, $target)) {
                Assert::assertTrue(copy($sourceImage, $target));
            }

            $location = "/albums/sample-album/$fileName";
            $sql->executeStatement(
                "INSERT INTO album_images (album, title, sequence, caption, location, width, height, active) "
                    . "VALUES (?, ?, ?, '', ?, 1, 1, 1)",
                [$albumId, "Image $i", $i, $location]
            );
            $this->albumFixtures[$albumId]['imageFiles'][$i + 1] = $fileName;
            $this->albumFixtures[$albumId]['imageTitles'][$i + 1] = "Image $i";
        }

        $sql->disconnect();
    }

    /**
     * @Given /^I have created album (\d+) with (\d+) images$/
     * @param $albumId
     * @throws Exception
     */
    public function iHaveCreatedAlbumWithImages($albumId, $images) {
        $this->resetAlbumFixture((int) $albumId);
        $this->albumIds[] = $albumId;
        $this->user = $this->environment->getContext('ui\bootstrap\BaseFeatureContext')->getUser();
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `date`, `location`, `owner`, `images`) VALUES ($albumId, 'Album $albumId', 'sample album for testing', '2020-01-01 00:00:00', 'sample-album', {$this->user->getId()}, '$images');");
        $this->rememberAlbumFixture((int) $albumId, (int) $images);
        $oldMask = umask(0);
        if (!is_dir(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'content/albums/sample-album')) {
            mkdir(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'content/albums/sample-album');
        }
        chmod(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'content/albums/sample-album', 0777);
        for ($i = 0; $i < $images; $i++) {
            $sql->executeStatement("INSERT INTO `album_images` (`album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES ('$albumId', 'Image $i', $i, '', '/albums/sample-album/sample$i.jpg', '400', '300', '1');");
            system('convert ' . dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "resources/flower.jpeg -gravity Center -density 90 -pointsize 200 -annotate 0 'Image $i' " . dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "content/albums/sample-album/sample$i.jpg");
            chmod(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "content/albums/sample-album/sample$i.jpg", 0777);
        }
        umask($oldMask);
        $sql->disconnect();
    }

    /**
     * @Given /^album (\d+) images are generic$/
     * @param $albumId
     * @throws Exception
     */
    public function albumImagesAreGeneric($albumId) {
        $oldMask = umask(0);
        $sql = new Sql();
        $images = $sql->getRows("SELECT * FROM `album_images` WHERE `album` = $albumId;");
        $sql->disconnect();
        foreach ($images as $image) {
            copy(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources/flower.jpeg', dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'content' . $image['location']);
            chmod(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'content' . $image['location'], 0777);
        }
        umask($oldMask);
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
     * @Given /^album (\d+) image (\d+) has caption "([^"]*)"$/
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
     * @Given /^I have download access to album (\d+)$/
     */
    public function iHaveDownloadAccessToAlbum($albumId): void {
        $sql = new Sql();
        $sql->executeStatement(
            "INSERT INTO download_rights (user, album, image) VALUES (?, ?, '*')",
            [$this->user->getId(), (int) $albumId]
        );
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
     * @Given /^user ([A-Za-z0-9_-]+) has access to album (\d+)$/
     * @param $userId
     * @param $albumId
     * @throws Exception
     */
    public function userHasAccessToAlbum($userId, $albumId) {
        $userId = $this->resolveUserId((string) $userId);
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `albums_for_users` VALUES( $userId, $albumId);");
        $sql->disconnect();
    }

    /**
     * @Given /^user ([A-Za-z0-9_-]+) has download access to album (\d+)$/
     * @param $userId
     * @param $albumId
     * @throws Exception
     */
    public function userHasDownloadAccessToAlbum($userId, $albumId) {
        $userId = $this->resolveUserId((string) $userId);
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `download_rights` VALUES( $userId, $albumId, '*');");
        $sql->disconnect();
    }

    /**
     * @Given /^user ([A-Za-z0-9_-]+) has share access to album (\d+)$/
     * @param $userId
     * @param $albumId
     * @throws Exception
     */
    public function userHasShareAccessToAlbum($userId, $albumId) {
        $userId = $this->resolveUserId((string) $userId);
        $sql = new Sql();
        $sql->executeStatement("INSERT INTO `share_rights` VALUES( $userId, $albumId, '*');");
        $sql->disconnect();
    }

        /**
     * @Given /^album (\d+) has notifications:$/
     * @param $albumId
     * @param TableNode $table
     * @throws Exception
     */
    public function albumHasNotifications($albumId, TableNode $table) {
        $sql = new Sql();
        foreach ($table as $row) {
            $sql->executeStatement("INSERT INTO notification_emails VALUES( $albumId, NULL, '{$row['email']}', {$row['contacted']})");
        }
        $sql->disconnect();
    }

    /**
     * @When /^I open album "([^"]*)" by its code$/
     * @param $albumCode
     */
    public function iHaveSearchedForAlbum($albumCode) {
        $baseUrl = $this->environment->getContext('ui\\bootstrap\\BaseFeatureContext')->getBaseUrl();
        $this->driver->get($baseUrl . '#album=' . rawurlencode($albumCode));
        $this->wait->until(WebDriverExpectedCondition::urlContains('/user/album.php?album='));
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
     * @When /^I close the image viewer$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iCloseTheModal() {
        $overlay = $this->driver->findElement(WebDriverBy::id('album-viewer-overlay'));
        $this->driver->findElement(WebDriverBy::id('album-viewer-close'))->click();
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::visibilityOf($overlay)));
    }

            /**
     * @When /^I confirm my download$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iConfirmMyDownload() {
        $downloadDirectory = $this->environment->getContext('ui\\bootstrap\\BaseFeatureContext')->getDownloadDirectory();
        foreach (glob($downloadDirectory . DIRECTORY_SEPARATOR . 'Album *.zip') ?: [] as $download) {
            unlink($download);
        }

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
     * @When /^I add user ([A-Za-z0-9_-]+) for album access$/
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iAddUserForAlbumAccess($user) {
        $user = $this->resolveTestUser((string) $user);
        $album = new Album($this->driver, $this->wait);
        $album->giveUserAlbumAccess($user['id'], $user['username']);
    }

    /**
     * @When /^I add user ([A-Za-z0-9_-]+) for download access$/
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iAddUserForDownloadAccess($user) {
        $user = $this->resolveTestUser((string) $user);
        $album = new Album($this->driver, $this->wait);
        $album->giveUserDownloadAccess($user['id'], $user['username']);
    }

    /**
     * @When /^I try to add user ([A-Za-z0-9_-]+) for download access$/
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iTryToAddUserForDownloadAccess($user) {
        $user = $this->resolveTestUser((string) $user);
        $album = new Album($this->driver, $this->wait);
        $album->tryToGiveUserDownloadAccess($user['username']);
    }

    /**
     * @When /^I add user ([A-Za-z0-9_-]+) for share access$/
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iAddUserForShareAccess($user) {
        $user = $this->resolveTestUser((string) $user);
        $album = new Album($this->driver, $this->wait);
        $album->giveUserShareAccess($user['id'], $user['username']);
    }

    /**
     * @When /^I remove user ([A-Za-z0-9_-]+) for album access$/
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iRemoveUserForAlbumAccess($user) {
        $user = $this->resolveTestUser((string) $user);
        $album = new Album($this->driver, $this->wait);
        $album->removeUserAlbumAccess($user['id']);
    }

    /**
     * @When /^I remove user ([A-Za-z0-9_-]+) for download access$/
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iRemoveUserForDownloadAccess($user) {
        $user = $this->resolveTestUser((string) $user);
        $album = new Album($this->driver, $this->wait);
        $album->removeUserDownloadAccess($user['id']);
    }

    /**
     * @When /^I remove user ([A-Za-z0-9_-]+) for share access$/
     * @param $user
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iRemoveUserForShareAccess($user) {
        $user = $this->resolveTestUser((string) $user);
        $album = new Album($this->driver, $this->wait);
        $album->removeUserShareAccess($user['id']);
    }

    /**
     * @When /^I add a new album$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iAddANewAlbum() {
        $this->editingAlbumId = null;
        $this->pendingAlbumFields = [];
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
        $selector = WebDriverBy::id('new-album-' . $field);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($selector));
        $input = $this->driver->findElement($selector);
        $input->clear()->sendKeys($value);
        $this->pendingAlbumFields[$field] = (string) $input->getAttribute('value');
    }

    /**
     * @When /^I create my album$/
     * @throws Exception
     */
    public function iCreateMyAlbum() {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className('glyphicon-folder-close')));
        $submitted = $this->captureAlbumFormValues();
        $this->driver->findElement(WebDriverBy::className('glyphicon-folder-close'))->click();
        // If this is a success, remember the browser-submitted values for later UI assertions.
        try {
            $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('album')));
            $albumId = (int) $this->driver->findElement(WebDriverBy::id('album'))->getAttribute('album-id');
            $this->albumIds[] = $albumId;
            $this->editingAlbumId = $albumId;
            $this->albumFixtures[$albumId] = [
                'name' => $submitted['name'] ?? '',
                'description' => $submitted['description'] ?? '',
                'date' => $submitted['date'] ?? '',
                'lastAccessed' => '',
                'code' => $submitted['code'] ?? '',
                'images' => '0',
            ];
            $this->pendingAlbumFields = [];
        } catch (TimeoutException|NoSuchElementException $e) {
            // Do nothing: validation/error scenarios intentionally remain in the dialog.
        }
    }

    /**
     * @When /^I edit album (\d+)$/
     * @param $albumId
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iEditAlbum($albumId) {
        $this->editingAlbumId = (int) $albumId;
        $this->pendingAlbumFields = [];
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
            if ($this->editingAlbumId !== null && isset($this->albumFixtures[$this->editingAlbumId])) {
                foreach ($this->pendingAlbumFields as $field => $value) {
                    $fixtureField = $field === 'last-accessed' ? 'lastAccessed' : $field;
                    $this->albumFixtures[$this->editingAlbumId][$fixtureField] = (string) $value;
                }
            }
            $this->pendingAlbumFields = [];
        } catch (Exception|TimeoutException|NoSuchElementException $e) {
            // Do nothing: validation/error scenarios intentionally remain in the dialog.
        }
    }

    /**
     * @When /^I delete my album$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iDeleteMyAlbum() {
        $editDialog = $this->driver->findElement(WebDriverBy::cssSelector('.bootstrap-dialog'));
        $deleteAlbumButton = WebDriverBy::xpath(".//button[contains(@class, 'btn-danger')][contains(normalize-space(.), 'Delete Album')]");
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($deleteAlbumButton));
        $editDialog->findElement($deleteAlbumButton)->click();

        $confirmButton = WebDriverBy::xpath("//div[contains(@class, 'bootstrap-dialog')][.//div[contains(@class, 'bootstrap-dialog-title')][normalize-space(.)='Are You Sure?']]//button[contains(@class, 'btn-danger')][contains(normalize-space(.), 'Delete')]");
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($confirmButton));
    }

    /**
     * @When /^I confirm my deletion of my album$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iConfirmMyDeletionOfMyAlbum() {
        $confirmButton = WebDriverBy::xpath("//div[contains(@class, 'bootstrap-dialog')][.//div[contains(@class, 'bootstrap-dialog-title')][normalize-space(.)='Are You Sure?']]//button[contains(@class, 'btn-danger')][contains(normalize-space(.), 'Delete')]");
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($confirmButton));
        $this->driver->findElement($confirmButton)->click();
        $this->wait->until(WebDriverExpectedCondition::not(
            WebDriverExpectedCondition::presenceOfElementLocated($confirmButton)
        ));
    }

    /**
     * @When /^I set access to my album$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSetAccessToMyAlbum() {
        $button = WebDriverBy::id('album-users-btn');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($button));
        $this->driver->findElement($button)->click();

        $search = WebDriverBy::cssSelector('#albumDiv #user-search');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($search));
        $this->wait->until(
            WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('album-users'))
        );
    }

    /**
     * @When /^I upload test image "([^"]*)"$/
     * @param string $fileName
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iUploadTestImage(string $fileName): void {
        $filePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . $fileName;
        if (!is_file($filePath)) {
            throw new Exception("Upload fixture '$fileName' does not exist");
        }
        $album = new Album($this->driver, $this->wait);
        $album->uploadImage(realpath($filePath));
    }

    /**
     * @When /^I close the album details modal$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iCloseTheAlbumDetailsModal(): void {
        $modal = $this->driver->findElement(WebDriverBy::cssSelector('.bootstrap-dialog'));
        $closeButtons = $modal->findElements(WebDriverBy::xpath(".//button[normalize-space(.)='Close']"));
        Assert::assertNotEmpty($closeButtons, 'Expected the album details modal to have a Close button');
        $closeButtons[count($closeButtons) - 1]->click();
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::visibilityOf($modal)));
    }

    /**
     * @Then /^I see uploaded image "([^"]*)" displayed in album (\d+) with (\d+) images$/
     * @param string $fileName
     * @param int $albumId
     * @throws TimeoutException
     */
    public function iSeeUploadedImageDisplayedInAlbum(string $fileName, int $albumId, int $imageCount): void {
        $baseUrl = $this->environment->getContext('ui\\bootstrap\\BaseFeatureContext')->getBaseUrl();
        $this->driver->get($baseUrl . "user/album.php?album=$albumId");

        try {
            $this->wait->until(function () {
                $config = $this->driver->findElement(WebDriverBy::id('album-page-config'));
                $expected = (int)$config->getAttribute('data-total');
                $cards = $this->driver->findElements(WebDriverBy::cssSelector('#album-grid .album-card'));
                return $expected > 0 && count($cards) === $expected;
            });
        } catch (TimeoutException $e) {
            $config = $this->driver->findElement(WebDriverBy::id('album-page-config'));
            $cards = $this->driver->findElements(WebDriverBy::cssSelector('#album-grid .album-card'));
            $titles = array_map(
                fn($card) => $card->getAttribute('data-title'),
                $cards
            );
            throw new TimeoutException(
                "Album page expected {$config->getAttribute('data-total')} image cards but rendered "
                . count($cards) . ': ' . implode(', ', $titles),
                0,
                $e
            );
        }

        $selector = '#album-grid .album-card[data-title="' . addcslashes($fileName, '\\"') . '"]';
        $cards = $this->driver->findElements(WebDriverBy::cssSelector($selector));
        Assert::assertCount(1, $cards, "Uploaded image '$fileName' was not rendered as an album card");
        $card = $cards[0];

        // Album images are intentionally lazy-loaded. Move the newly uploaded
        // card into view and dispatch the same scroll event a user browsing the
        // gallery would produce so album.js loads its protected background.
        $this->driver->executeScript(
            "arguments[0].scrollIntoView({block: 'center'}); window.dispatchEvent(new Event('scroll'));",
            [$card]
        );

        try {
            $this->wait->until(WebDriverExpectedCondition::visibilityOf($card));
        } catch (TimeoutException $e) {
            throw new TimeoutException("Uploaded image '$fileName' album card did not become visible after scrolling into view", 0, $e);
        }

        $media = $card->findElement(WebDriverBy::className('album-card-media'));
        try {
            $this->wait->until(function () use ($media) {
                $backgroundImage = $media->getCSSValue('background-image');
                return $backgroundImage !== '' && $backgroundImage !== 'none';
            });
        } catch (TimeoutException $e) {
            throw new TimeoutException("Uploaded image '$fileName' card did not lazy-load its protected background", 0, $e);
        }

        $src = $card->findElement(WebDriverBy::className('album-card-image'))->getAttribute('src');
        Assert::assertStringNotContainsString(
            '/albums/',
            $src,
            'Protected album image must not be exposed through the img src'
        );
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
     * @When /^I provide "([^"]*)" for the email album notification$/
     * @param $email
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iProvideForTheEmailAlbumNotification($email) {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('notify-email')));
        $this->driver->findElement(WebDriverBy::id('notify-email'))->clear()->sendKeys($email);
    }

    /**
     * @When /^I submit my email for album notification$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSubmitMyEmailForAlbumNotification() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('notify-submit')));
        $this->driver->findElement(WebDriverBy::id('notify-submit'))->click();
    }

    /**
     * @When /^I send the user notifications$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSendTheUserNotifications() {
        $this->driver->findElement(WebDriverBy::id('email-users'))->click();
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('notifications-message')));
    }

    /**
     * @When /^I set the email notification message to "([^"]*)"$/
     * @param $message
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSetTheEmailNotificationMessageTo($message) {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('notifications-message')));
        $this->driver->findElement(WebDriverBy::id('notifications-message'))->clear()->sendKeys($message);
    }

    /**
     * @Given /^I confirm sending user notification$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iConfirmSendingUserNotification() {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('notifications-message')));
        $this->driver->findElement(WebDriverBy::id('notifications-send-btn'))->click();
    }

    /**
     * @Then /^I see the "([^"]*)" album images load$/
     * @param $ord
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeTheAlbumImagesLoad($ord) {
        $album = new Album($this->driver, $this->wait);
        $row = (int) $ord;
        $album->waitForImagesToLoad($row);
        $start = ($row - 1) * 4;
        for ($i = 0; $i < 4; $i++) {
            $sequence = $start + $i;
            $image = $this->driver->findElement(
                WebDriverBy::cssSelector("#album-grid .album-card[data-image-id='$sequence']")
            );
            Assert::assertEquals('Image ' . $sequence, $image->getAttribute('data-title'));
        }
    }

    /**
     * @Then /^I see the image controls on album image (\d+)$/
     * @param $imgNum
     */
    public function iSeeTheInfoIconOnImage($imgNum) {
        Assert::assertTrue($this->image->findElement(WebDriverBy::className('album-card-overlay'))->isDisplayed());
    }

    /**
     * @Then /^I see album image (\d+) in the image viewer$/
     * @param $imgNum
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeImageInThePreviewModal($imgNum) {
        $overlay = $this->driver->findElement(WebDriverBy::id('album-viewer-overlay'));
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($overlay));
        Assert::assertTrue($overlay->isDisplayed());
        $album = new Album($this->driver, $this->wait);
        $img = $album->getSlideShowImage();
        Assert::assertEquals((string) ($imgNum - 1), $img->getAttribute('data-image-id'));
        Assert::assertNotEquals(
            'none',
            $this->driver->findElement(WebDriverBy::id('album-viewer-image'))->getCSSValue('background-image')
        );
    }

    /**
     * @Then /^I see the album caption "([^"]*)" displayed$/
     * @param $caption
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeTheCaptionDisplayed($caption) {
        $this->wait->until(function () use ($caption) {
            return $this->driver->findElement(WebDriverBy::id('album-viewer-caption'))->getText() === $caption;
        });
        Assert::assertEquals($caption, $this->driver->findElement(WebDriverBy::id('album-viewer-caption'))->getText());
    }

    /**
     * @Then /^I do not see any album captions$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iDoNotSeeAnyCaptions() {
        Assert::assertEquals('', $this->driver->findElement(WebDriverBy::id('album-viewer-caption'))->getText());
    }

    /**
     * @Then /^I see the image as a favorite$/
     */
    public function iSeeTheImageAsAFavorite() {
        $this->wait->until(function () {
            return !$this->driver->findElement(WebDriverBy::id('set-favorite-image-btn'))->isDisplayed()
                && $this->driver->findElement(WebDriverBy::id('unset-favorite-image-btn'))->isDisplayed();
        });
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
        $this->wait->until(function () {
            return $this->driver->findElement(WebDriverBy::id('set-favorite-image-btn'))->isDisplayed()
                && !$this->driver->findElement(WebDriverBy::id('unset-favorite-image-btn'))->isDisplayed();
        });
        Assert::assertTrue($this->driver->findElement(WebDriverBy::id('set-favorite-image-btn'))->isDisplayed());
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('unset-favorite-image-btn'))->isDisplayed());
    }

    /**
     * @Then /^I see (\d+) favorite[s]?$/
     * @param $favorites
     */
    public function iSeeFavorites($favorites) {
        $this->wait->until(function () use ($favorites) {
            $visibleFavorites = array_filter(
                $this->driver->findElements(WebDriverBy::cssSelector("#album-grid .album-card[data-favorite='1']")),
                fn($card) => $card->isDisplayed()
            );
            return count($visibleFavorites) === (int) $favorites;
        });
        $visibleFavorites = array_filter(
            $this->driver->findElements(WebDriverBy::cssSelector("#album-grid .album-card[data-favorite='1']")),
            fn($card) => $card->isDisplayed()
        );
        Assert::assertCount((int) $favorites, $visibleFavorites);
    }

    /**
     * @Then /^I see album image (\d+) as a favorite$/
     * @param $image
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeAlbumImageAsAFavorite($image) {
        $card = $this->driver->findElement(
            WebDriverBy::cssSelector("#album-grid .album-card[data-image-id='" . ($image - 1) . "'][data-favorite='1']")
        );
        $this->wait->until(WebDriverExpectedCondition::visibilityOf($card));
        Assert::assertTrue($card->isDisplayed());
    }

    /**
     * @Then /^the download favorites button is disabled$/
     */
    public function theDownloadFavoritesButtonIsDisabled() {
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('downloadable-favorites-btn'))->isEnabled());
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
        Assert::assertFalse($this->driver->findElement(WebDriverBy::id('submit-favorites-btn'))->isEnabled());
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
        $expected = 'There are no files available for you to download. Please purchase rights to the images you tried to download, and try again.';
        $alertSelector = WebDriverBy::cssSelector('.bootstrap-dialog .alert-danger');
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated($alertSelector));
        $alert = $this->driver->findElement($alertSelector);
        $actual = preg_replace('/^×\\s*/u', '', $alert->getText());
        $decoded = json_decode($actual, true);
        if (is_array($decoded) && isset($decoded['error'])) {
            $actual = $decoded['error'];
        }
        Assert::assertEquals($expected, $actual);
    }

    /**
     * @Then /^I see the large download email prompt$/
     */
    public function iSeeTheLargeDownloadEmailPrompt(): void {
        $selector = WebDriverBy::id('download-email-address-alert');
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($selector));

        $alert = $this->driver->findElement($selector);
        Assert::assertStringContainsString(
            'Please enter your email to receive a link to the files once they are ready for download',
            $alert->getText()
        );
        Assert::assertTrue($alert->findElement(WebDriverBy::id('download-email-address'))->isDisplayed());
    }

    /**
     * @When /^I submit my email for the large download$/
     */
    public function iSubmitMyEmailForTheLargeDownload(): void {
        $input = WebDriverBy::id('download-email-address');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($input));
        $this->driver->findElement($input)->clear()->sendKeys($this->user->getEmail());

        $submit = WebDriverBy::cssSelector('#download-email-address-alert button.btn-info');
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable($submit));
        $this->driver->findElement($submit)->click();

        $this->wait->until(function () {
            return count($this->driver->findElements(WebDriverBy::cssSelector('.bootstrap-dialog.modal.in'))) === 0;
        });
    }

    /**
     * @Then /^I receive a ready email for album (\d+)$/
     */
    public function iReceiveAReadyEmailForAlbum($albumId): void {
        $text = CustomAsserts::getEmailText(
            $this->user->getEmail(),
            'noreply@saperstonestudios.com',
            'Your Download Is Ready'
        );

        $albumName = preg_quote("Album $albumId", '#');
        Assert::assertMatchesRegularExpression(
            "#https://saperstonestudios\\.com/tmp/$albumName \\d{4}-\\d{2}-\\d{2} \\d{2}-\\d{2}-\\d{2}\\.zip#",
            $text
        );
        Assert::assertStringContainsString(
            'This download will be available for the next 48 hours',
            $text
        );
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
     */
    public function iSeeACookieWithMyAlbum($albumId) {
        $cookie = $this->driver->manage()->getCookieNamed('searched');
        Assert::assertNotNull($cookie, 'Expected the searched-albums cookie to exist');

        $savedAlbums = json_decode(urldecode($cookie->getValue()), true);
        Assert::assertIsArray($savedAlbums);
        Assert::assertArrayHasKey($albumId, $savedAlbums);
        Assert::assertNotEmpty($savedAlbums[$albumId]);
    }

    /**
     * @Then /^I see album (\d+) listed$/
     * @param $albumId
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeAlbumListed($albumId) {
        $albumId = (int) $albumId;
        $rowSelector = WebDriverBy::cssSelector("tr[album-id='$albumId']");
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated($rowSelector));

        $row = $this->driver->findElement($rowSelector);
        $link = $row->findElement(WebDriverBy::cssSelector('.album-name a'));
        $href = $link->getAttribute('href');

        Assert::assertStringEndsWith("album.php?album=$albumId", $href, $href);
        if (isset($this->albumFixtures[$albumId])) {
            Assert::assertSame($this->albumFixtures[$albumId]['name'], $link->getText());
        }
    }

    /**
     * @Then /^I see (\d+) album(s?) listed$/
     * @param $count
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeAlbumsListed($count) {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector("tbody tr:nth-child($count)")));
        Assert::assertEquals($count, sizeof($this->driver->findElements(WebDriverBy::cssSelector('tbody tr[role="row"]'))));
    }

    /**
     * @Then /^I see an error message indicating no album exists$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeAnErrorMessageIndicatingNoAlbumExists() {
        CustomAsserts::errorMessage($this->driver, 'That code does not match any albums');
    }

    /**
     * @Then /^I see an error message indicating album code required$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeAnErrorMessageIndicatingAlbumCodeRequired() {
        CustomAsserts::errorMessage($this->driver, 'Album code can not be blank');
    }

    /**
     * @Then /^I see an info message indicating album successfully added$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeAnInfoMessageIndicatingAlbumSuccessfullyAdded() {
        CustomAsserts::infoMessage($this->driver, 'Added album to your list');
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
        $downloadDirectory = $this->environment->getContext('ui\\bootstrap\\BaseFeatureContext')->getDownloadDirectory();
        $filename = $downloadDirectory . DIRECTORY_SEPARATOR . "Album $album $now.zip";
        while (!file_exists($filename)) {
            $matches = glob($downloadDirectory . DIRECTORY_SEPARATOR . "Album $album *.zip");
            if (!empty($matches)) {
                usort($matches, static function ($left, $right) {
                    return filemtime($right) <=> filemtime($left);
                });
                $filename = $matches[0];
                break;
            }
            sleep(1);
            $count++;
            if ($count > 120) {
                break;
            }
        }
        Assert::assertTrue(file_exists($filename));
        $images = array_map('intval', explode(", ", $images));
        $za = new ZipArchive();
        $za->open($filename);
        Assert::assertEquals(sizeof($images), $za->numFiles);
        for ($i = 0; $i < sizeof($images); $i++) {
            $expected = $this->fixtureImageFileName((int) $album, $images[$i]);
            Assert::assertEquals($expected, $za->statIndex($i)['name'], $za->statIndex($i)['name']);
        }
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
     * @Then /^I see album (\d+) album (.*)/
     * @param $albumId
     * @param $albumAttribute
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeAlbumAlbum($albumId, $albumAttribute) {
        $albumId = (int) $albumId;
        Assert::assertArrayHasKey($albumId, $this->albumFixtures, "No expected fixture state for album $albumId");

        $album = new Album($this->driver, $this->wait);
        $albumRow = $album->getAlbumRow($albumId);
        $field = $this->toCamelCase($albumAttribute);
        $element = $albumRow->findElement(WebDriverBy::className('album-' . str_replace(' ', '-', $albumAttribute)));

        Assert::assertTrue($element->isDisplayed());
        Assert::assertSame((string) $this->albumFixtures[$albumId][$field], $element->getText());
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
     * @Then /^I see album (\d+) has (\d+) images$/
     */
    public function iSeeAlbumHasImages(int $albumId, int $imageCount): void {
        $album = new Album($this->driver, $this->wait);
        $this->wait->until(function () use ($album, $albumId, $imageCount) {
            $row = $album->getAlbumRow($albumId);
            return (int) $row->findElement(WebDriverBy::className('album-images'))->getText() === $imageCount;
        });

        $row = $album->getAlbumRow($albumId);
        Assert::assertSame(
            $imageCount,
            (int) $row->findElement(WebDriverBy::className('album-images'))->getText()
        );
    }

    /**
     * @Then /^I don't see album (\d+) album (.*)/
     * @param $albumId
     * @param $albumAttribute
     * @throws NoSuchElementException
     * @throws TimeoutException
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
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iDonTSeeAlbumEditIcon($albumId) {
        $album = new Album($this->driver, $this->wait);
        $albumRow = $album->getAlbumRow($albumId);
        Assert::assertEquals(0, sizeof($albumRow->findElements(WebDriverBy::className('edit-album-btn'))));
    }

    /**
     * @Then /^I don't see album (\d+) log icon$/
     * @param $albumId
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iDonTSeeAlbumLogIcon($albumId) {
        $album = new Album($this->driver, $this->wait);
        $albumRow = $album->getAlbumRow($albumId);
        Assert::assertEquals(0, sizeof($albumRow->findElements(WebDriverBy::className('view-album-log-btn'))));
    }

    /**
     * @Then /^I see album (\d+) edit icon$/
     * @param $albumId
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeAlbumEditIcon($albumId) {
        $album = new Album($this->driver, $this->wait);
        $albumRow = $album->getAlbumRow($albumId);
        Assert::assertTrue($albumRow->findElement(WebDriverBy::className('edit-album-btn'))->isDisplayed());
    }

    /**
     * @Then /^I see album (\d+) log icon$/
     * @param $albumId
     * @throws NoSuchElementException
     * @throws TimeoutException
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
        $this->assertAlbumDetailsModal((int) $albumId, false);
    }

    /**
     * @Then I see the album details modal for the new album
     */
    public function iSeeTheAlbumDetailsModalForTheNewAlbum(): void {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('album')));
        $albumId = $this->driver->findElement(WebDriverBy::id('album'))->getAttribute('album-id');
        Assert::assertMatchesRegularExpression('/^\\d+$/', $albumId);
        $this->iSeeTheAlbumDetailsModalForAlbum($albumId);
        if (!in_array((int) $albumId, $this->albumIds, true)) {
            $this->albumIds[] = (int) $albumId;
        }
    }

    /**
     * @Then I see the edit album details modal for the new album
     */
    public function iSeeTheEditAlbumDetailsModalForTheNewAlbum(): void {
        $this->wait->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('album')));
        $albumId = $this->driver->findElement(WebDriverBy::id('album'))->getAttribute('album-id');
        Assert::assertMatchesRegularExpression('/^\\d+$/', $albumId);
        $this->iSeeTheEditAlbumDetailsModalForAlbum($albumId);
        if (!in_array((int) $albumId, $this->albumIds, true)) {
            $this->albumIds[] = (int) $albumId;
        }
    }

    /**
     * @Then /^I see the edit album details modal for album (\d+)$/
     * @param $albumId
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeTheEditAlbumDetailsModalForAlbum($albumId) {
        $this->assertAlbumDetailsModal((int) $albumId, true);
    }

    private function assertAlbumDetailsModal(int $albumId, bool $expectCode): void {
        Assert::assertArrayHasKey($albumId, $this->albumFixtures, "No expected fixture state for album $albumId");
        $expected = $this->albumFixtures[$albumId];

        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('new-album-name')));
        Assert::assertSame((string) $albumId, $this->driver->findElement(WebDriverBy::id('album'))->getAttribute('album-id'));
        Assert::assertSame($expected['name'], $this->driver->findElement(WebDriverBy::id('new-album-name'))->getAttribute('value'));
        Assert::assertSame($expected['description'], $this->driver->findElement(WebDriverBy::id('new-album-description'))->getAttribute('value'));
        Assert::assertSame($expected['date'], $this->driver->findElement(WebDriverBy::id('new-album-date'))->getAttribute('value'));

        $code = $this->driver->findElements(WebDriverBy::id('new-album-code'));
        if ($expectCode) {
            Assert::assertCount(1, $code);
            Assert::assertSame($expected['code'], $code[0]->getAttribute('value'));
        } else {
            Assert::assertCount(0, $code);
        }
    }

    /**
     * @Then /^I see the new album listed$/
     */
    public function iSeeTheNewAlbumListed(): void {
        Assert::assertNotNull($this->editingAlbumId, 'No newly created album id was captured from the browser');
        $this->iSeeAlbumListed($this->editingAlbumId);
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
     * @Then /^I see users "([A-Za-z0-9_,-]*)" with album access$/
     * @param $users
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeUserWithAlbumAccess($users) {
        $album = new Album($this->driver, $this->wait);
        $users = $this->resolveUserIds((string) $users);
        $accessors = $album->getAlbumAccessors();
        Assert::assertEquals(sizeof($users), sizeof($accessors));
        for ($i = 0; $i < sizeof($accessors); $i++) {
            Assert::assertEquals($users[$i], $accessors[$i]->getAttribute('user-id'));
        }
    }

    /**
     * @Then /^I see users "([A-Za-z0-9_,-]*)" with download access$/
     * @param $users
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeUsersWithDownloadAccess($users) {
        $album = new Album($this->driver, $this->wait);
        $users = $this->resolveUserIds((string) $users);
        $downloaders = $album->getAlbumDownloaders();
        Assert::assertEquals(sizeof($users), sizeof($downloaders));
        for ($i = 0; $i < sizeof($downloaders); $i++) {
            Assert::assertEquals($users[$i], $downloaders[$i]->getAttribute('user-id'));
        }
    }

    /**
     * @Then /^I see users "([A-Za-z0-9_,-]*)" with share access$/
     * @param $users
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeUsersWithShareAccess($users) {
        $album = new Album($this->driver, $this->wait);
        $users = $this->resolveUserIds((string) $users);
        $sharers = $album->getAlbumSharers();
        Assert::assertEquals(sizeof($users), sizeof($sharers));
        for ($i = 0; $i < sizeof($sharers); $i++) {
            Assert::assertEquals($users[$i], $sharers[$i]->getAttribute('user-id'));
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

    /**
     * @Then /^I see an error message indicating email is required$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeAnErrorMessageIndicatingEmailIsRequired() {
        CustomAsserts::errorMessage($this->driver, 'Email can not be blank');
    }

    /**
     * @Then /^I see an error message indicating email is not valid$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeAnErrorMessageIndicatingEmailIsNotValid() {
        CustomAsserts::errorMessage($this->driver, 'Email is not valid');
    }

    /**
     * @Then /^I see a success message indicating I will be notified when images are added$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeASuccessMessageIndicatingIWillBeNotifiedWhenImagesAreAdded() {
        CustomAsserts::successMessage($this->driver, 'Your email address was successfully recorded. You will be notified once the images have been uploaded.');
    }

    /**
     * @Then /^I see that I already requested an album notification$/
     */
    public function iSeeThatIAlreadyRequestedAnAlbumNotification(): void {
        $notice = WebDriverBy::id('notification-requested');
        $this->wait->until(WebDriverExpectedCondition::visibilityOfElementLocated($notice));
        Assert::assertSame(
            'You have already asked to be notified when images are added.',
            trim($this->driver->findElement($notice)->getText())
        );
    }

    /**
     * @Then /^I don't see the album notification form$/
     */
    public function iDonTSeeTheAlbumNotificationForm() {
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('notify-email'))));
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('notify-submit'))));
    }

    /**
     * @Then /^I don't see any email notification messages$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iDonTSeeAnyEmailNotificationMessages() {
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::id('email-list'))));
        Assert::assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('email-list'))));
    }

    /**
     * @Then /^I see notification emails of:$/
     */
    public function iSeeNotificationEmailsOf(TableNode $table) {
        $emailList = $this->driver->findElement(WebDriverBy::id('email-list'));
        Assert::assertTrue($emailList->isDisplayed());
        $emails = $emailList->findElements(WebDriverBy::tagName('a'));
        Assert::assertEquals(sizeof($table->getRows()) - 1, sizeof($emails));
        for ($i = 0; $i < sizeof($emails); $i++) {
            Assert::assertEquals($table->getRow($i + 1)[0], $emails[$i]->getText(), $table->getRow($i + 1)[0] . " " . $emails[$i]->getText());
        }
    }

    /**
     * @Then /^I see an album notification for album (\d+) was emailed out$/
     * @param $albumId
     * @throws ExceptionAlias
     */
    public function iSeeAnAlbumNotificationWasEmailedOutTo($albumId) {
        CustomAsserts::assertEmailMatches($this->user->getEmail(), 'noreply@saperstonestudios.com', 'Album Updated on Saperstone Studios',
            "An album you requested to be updated about has been updated.\r
\r
Images have been posted to album Album $albumId. You can access your images by logging in at https://saperstonestudios.com/ and then navigating to https://saperstonestudios.com/user/album.php?album=$albumId.",
            "<html><body>An album you requested to be updated about has been updated.\r
\r
Images have been posted to album Album $albumId. You can access your images by logging in at https://saperstonestudios.com/ and then navigating to https://saperstonestudios.com/user/album.php?album=$albumId.</body></html>");
    }

    /**
     * @Then /^I see the email notification set to "([^"]*)"$/
     * @param $message
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iSeeTheEmailNotificationSetTo($message) {
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('notifications-message')));
        $actualMessage = $this->driver->findElement(WebDriverBy::id('notifications-message'))->getText();
        Assert::assertEquals($message, $actualMessage, $actualMessage);
    }

    /**
     * @Then /^I see an email indicating images "([^"]*)" from album (\d+) downloaded$/
     * @param $images
     * @param $albumId
     * @throws ExceptionAlias
     */
    public function iSeeAnEmailIndicatingImagesFromAlbumDownloaded($images, $albumId) {
        $imageNumbers = array_map('intval', explode(", ", $images));
        $imgs = array_map(
            fn(int $imageNumber): string => $this->fixtureImageFileName((int) $albumId, $imageNumber),
            $imageNumbers
        );
        $images = implode("\r\n", $imgs);
        $imagesLi = implode("</li><li>", $imgs);
        CustomAsserts::assertEmailMatches((string)getenv('EMAIL_ACTIONS'), 'actions@saperstonestudios.com', 'Someone Downloaded Something', "This is an automatically generated message from Saperstone Studios

Downloads have been made from the Album $albumId album at %s://%s/user/album.php?album=$albumId

$images

Name: {$this->user->getName()}
Email: {$this->user->getEmail()}
Location: unknown (use %d.%d.%d.%d to manually lookup)
Browser: %s %s
Resolution: 
OS: %s
Full UA: %s", "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p>Downloads have been made from the <a href='%s://%s/user/album.php?album=$albumId' target='_blank'>Album $albumId</a> album</p><p><ul><li>$imagesLi</li></ul></p><br/><p><strong>Name</strong>: {$this->user->getName()}<br/><strong>Email</strong>: <a href='mailto:{$this->user->getEmail()}'>{$this->user->getEmail()}</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: <br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>");
    }

    /**
     * @Then /^an email is sent indicating album (\d+) images "([^"]*)" submitted$/
     */
    public function anEmailIsSentIndicatingImagesSubmitted($albumId, $images) {
        $imageNumbers = array_map('intval', explode(", ", $images));
        $titles = array_map(
            fn(int $imageNumber): string => $this->fixtureImageTitle((int) $albumId, $imageNumber),
            $imageNumbers
        );
        $imagesText = implode("\r\n", $titles);
        $imagesLi = implode("</li><li>", $titles);

        CustomAsserts::assertEmailMatches((string)getenv('EMAIL_SELECTS'), 'selects@saperstonestudios.com', 'Selects Have Been Made',
            "This is an automatically generated message from Saperstone Studios\r
\r
{$this->user->getName()} has made a selection from the Album $albumId album at %s://%s/user/album.php?album=$albumId. Their email address is {$this->user->getEmail()}\r
\r
$imagesText\r
\r
\t\t",
            "<html><body><p>This is an automatically generated message from Saperstone Studios</p><p><a href='mailto:{$this->user->getEmail()}'>{$this->user->getName()}</a> has made a selection from the <a href='%s://%s/user/album.php?album=$albumId' target='_blank'>Album $albumId</a> album</p><p><ul><li>$imagesLi</li></ul></p><br/><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p></body></html>");
    }

    /**
     * @Then /^I receive an email indicating I have submitted my selects$/
     * @throws ExceptionAlias
     */
    public function iReceiveAnEmailIndicatingIHaveSubmittedMySelects() {
        CustomAsserts::assertEmailMatches($this->user->getEmail(), 'selects@saperstonestudios.com', 'Thank You for Making Selects',
            'Thank you for making your selects. We\'ll start working on your images, and reach back out to you shortly with access to your final images.',
            '<html><body>Thank you for making your selects. We\'ll start working on your images, and reach back out to you shortly with access to your final images.</body></html>');
    }

    /**
     * 
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iCloseTheAlbumView() {
        $album = new Album($this->driver, $this->wait);
        $album->closeSlideShow();
    }

    /**
     * @Then /^I don't see the image viewer$/
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function iDonTSeeTheAlbumPreviewModal() {
        $overlay = $this->driver->findElement(WebDriverBy::id('album-viewer-overlay'));
        $this->wait->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::visibilityOf($overlay)));
        Assert::assertFalse($overlay->isDisplayed());
    }
}
