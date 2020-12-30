<?php

namespace ui\page;

use CustomAsserts;
use Exception;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;
use Google\Exception as ExceptionAlias;
use Sql;
use ui\models\Album;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestBase.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'Album.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class AlbumPageLoadTest extends TestBase {

    /**
     * @var Sql
     */
    private $sql;
    /**
     * @var WebDriverWait
     */
    private $wait;

    /**
     * @throws Exception
     */
    public function setUp() {
        parent::setUp();
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('99999', 'sample-album', 'sample album for testing', 'sample', 1, '2345');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (9997, '99999', '', '1', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (9998, '99999', '', '2', '', '', '300', '400', '1');");
        $this->sql->executeStatement("INSERT INTO `album_images` (`id`, `album`, `title`, `sequence`, `caption`, `location`, `width`, `height`, `active`) VALUES (9999, '99999', '', '3', '', '', '300', '400', '1');");

        $this->wait = new WebDriverWait($this->driver, 5);
    }

    /**
     * @throws Exception
     */
    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 99999;");
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 99999;");
        $this->sql->executeStatement("DELETE FROM `albums_for_users` WHERE `albums_for_users`.`album` = 99999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `album_images`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `album_images` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
        parent::tearDown();
    }

    /**
     * @throws ExceptionAlias
     */
    public function testAlbumNoAlbum() {
        $this->driver->get($this->baseUrl . 'user/album.php?');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/user/album.php?\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/user/album.php?\' target=\'_blank\'>%s://%s/user/album.php?</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws ExceptionAlias
     */
    public function testAlbumBlankAlbum() {
        $this->driver->get($this->baseUrl . 'user/album.php?album=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/user/album.php?album=\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/user/album.php?album=\' target=\'_blank\'>%s://%s/user/album.php?album=</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws ExceptionAlias
     */
    public function testAlbumBadAlbum() {
        $this->driver->get($this->baseUrl . 'user/album.php?album=998');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/user/album.php?album=998\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/user/album.php?album=998\' target=\'_blank\'>%s://%s/user/album.php?album=998</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws Exception
     */
    public function testAlbumLoadsForAdmin() {
        $this->sql->executeStatement("UPDATE albums SET owner = 4 WHERE id = 99999;");
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('sample-album sample album for testing', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    /**
     * @throws Exception
     */
    public function testAlbumLoadsForOwner() {
        $this->sql->executeStatement("UPDATE albums SET owner = 4 WHERE id = 99999;");
        $this->driver->get($this->baseUrl);
        $this->loginAs('c90788c0e409eac6a95f6c6360d8dbf7');
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('sample-album sample album for testing', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    /**
     * @throws ExceptionAlias
     */
    public function testAlbumLoadsForNoOne() {
        $this->driver->get($this->baseUrl);
        $this->loginAs('5510b5e6fffd897c234cafe499f76146');
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/user/album.php?album=99999\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
User Id: 3\r
Name: Download User\r
Email: email@example.org\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/user/album.php?album=99999\' target=\'_blank\'>%s://%s/user/album.php?album=99999</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>User Id</strong>: 3<br/><strong>Name</strong>: Download User<br/><strong>Email</strong>: <a href=\'mailto:email@example.org\'>email@example.org</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testAlbumLoadsSearchedFor() {
        $this->driver->get($this->baseUrl);
        $searched [99999] = md5("album2345");
        $cookie = new Cookie('searched', json_encode($searched));
        $this->driver->manage()->addCookie($cookie);
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('sample-album sample album for testing', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    /**
     * @throws Exception
     */
    public function testAlbumLoadsInList() {
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (3, '99999');");
        $this->driver->get($this->baseUrl);
        $this->loginAs('5510b5e6fffd897c234cafe499f76146');
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('sample-album sample album for testing', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    /**
     * @throws Exception
     */
    public function testAlbumUpdatesLastAccessed() {
        date_default_timezone_set("America/New_York");
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (3, '99999');");
        $this->driver->get($this->baseUrl);
        $this->loginAs('5510b5e6fffd897c234cafe499f76146');
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        CustomAsserts::timeWithin(2, $this->sql->getRow("SELECT lastAccessed FROM albums WHERE id = 99999;")['lastAccessed']);
    }

    /**
     * @throws Exception
     */
    public function testAlbumLogsAccessed() {
        date_default_timezone_set("America/New_York");
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (3, '99999');");
        $this->driver->get($this->baseUrl);
        $this->loginAs('5510b5e6fffd897c234cafe499f76146');
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $userLogs = $this->sql->getRow("SELECT * FROM `user_logs` ORDER BY time DESC");
        $this->assertEquals(3, $userLogs['user']);
        CustomAsserts::timeWithin(2, $userLogs['time']);
        $this->assertEquals('Visited Album', $userLogs['action']);
        $this->assertNull($userLogs['what']);
        $this->assertEquals(99999, $userLogs['album']);
    }

    public function testAlbumAdminToolBar() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('Home Albums sample-album', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('edit-album-btn'))->isDisplayed());
    }

    public function testAlbumGuestToolBar() {
        $this->driver->get($this->baseUrl);
        $searched [99999] = md5("album2345");
        $cookie = new Cookie('searched', json_encode($searched));
        $this->driver->manage()->addCookie($cookie);
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('Home sample-album', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('edit-album-btn'))));
    }

    /**
     * @throws Exception
     */
    public function testNoImages() {
        $this->sql->executeStatement("DELETE FROM `album_images` WHERE `album_images`.`album` = 99999;");
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('notify-email'))->isDisplayed());
        $this->assertEquals('msaperst@gmail.com', $this->driver->findElement(WebDriverBy::id('notify-email'))->getAttribute('value'));
    }

    public function testSlideShowModal() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('99999', $this->driver->findElement(WebDriverBy::id('album'))->getAttribute('album-id'));
//        $this->assertEquals('sample-album sample album for testing', $this->driver->findElement(WebDriverBy::id('album'))->findElement(WebDriverBy::className('modal-title'))->getText());
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testSlideShowButtonsGuestUser() {
        $this->driver->get($this->baseUrl);
        $searched [99999] = md5("album2345");
        $cookie = new Cookie('searched', json_encode($searched));
        $this->driver->manage()->addCookie($cookie);
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $album = new Album($this->driver, $this->wait);
        $album->openSlideShow(1);
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('downloadable-image-btn'))->isEnabled());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('not-downloadable-image-btn'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('shareable-image-btn'))->isEnabled());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('not-shareable-image-btn'))));
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('cart-image-btn'))->isEnabled());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('access-image-btn'))));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('delete-image-btn'))));
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testSlideShowButtonsAdminUser() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $album = new Album($this->driver, $this->wait);
        $album->openSlideShow(1);
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('downloadable-image-btn'))->isEnabled());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('not-downloadable-image-btn'))->isDisplayed());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('shareable-image-btn'))->isEnabled());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('not-shareable-image-btn'))->isDisplayed());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('cart-image-btn'))->isEnabled());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('access-image-btn'))->isEnabled());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('delete-image-btn'))->isEnabled());
    }

    /**
     * @throws Exception
     */
    public function testSlideShowButtonsDownloaderUser() {
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (3, '99999');");
        $this->driver->get($this->baseUrl);
        $this->loginAs('5510b5e6fffd897c234cafe499f76146');
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $album = new Album($this->driver, $this->wait);
        $album->openSlideShow(1);
        $this->wait->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('not-downloadable-image-btn')));
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('downloadable-image-btn'))->isEnabled());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('not-downloadable-image-btn'))->isEnabled());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('shareable-image-btn'))->isEnabled());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('not-shareable-image-btn'))->isEnabled());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('cart-image-btn'))->isEnabled());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('access-image-btn'))));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('delete-image-btn'))));
    }

    public function testFavoritesModal() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('99999', $this->driver->findElement(WebDriverBy::id('favorites'))->getAttribute('album-id'));
//        $this->assertEquals('sample-album', $this->driver->findElement(WebDriverBy::id('favorites'))->findElement(WebDriverBy::className('modal-title'))->getText());
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testFavoritesButtonsGuestUser() {
        $this->driver->get($this->baseUrl);
        $searched [99999] = md5("album2345");
        $cookie = new Cookie('searched', json_encode($searched));
        $this->driver->manage()->addCookie($cookie);
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $album = new Album($this->driver, $this->wait);
        $album->openFavorites();
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('favorites-all-title'))));
        $this->assertEquals('btn btn-default', $this->driver->findElement(WebDriverBy::id('downloadable-favorites-btn'))->getAttribute('class'));
        $this->assertEquals('btn btn-default', $this->driver->findElement(WebDriverBy::id('shareable-favorites-btn'))->getAttribute('class'));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('view-all-favorites-btn'))));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('view-my-favorites-btn'))));
    }

    /**
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function testFavoritesButtonsAdminUser() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $album = new Album($this->driver, $this->wait);
        $album->openFavorites();
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('favorites-all-title'))->isDisplayed());
        $this->assertEquals('btn btn-default btn-action btn-success', $this->driver->findElement(WebDriverBy::id('downloadable-favorites-btn'))->getAttribute('class'));
        $this->assertEquals('btn btn-default btn-action btn-success', $this->driver->findElement(WebDriverBy::id('shareable-favorites-btn'))->getAttribute('class'));
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('view-all-favorites-btn'))->isDisplayed());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('view-my-favorites-btn'))->isDisplayed());
    }

    /**
     * @throws Exception
     */
    public function testFavoritesButtonsDownloaderUser() {
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (3, '99999');");
        $this->driver->get($this->baseUrl);
        $this->loginAs('5510b5e6fffd897c234cafe499f76146');
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $album = new Album($this->driver, $this->wait);
        $album->openFavorites();
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('favorites-all-title'))));
        $this->assertEquals('btn btn-default btn-action btn-success', $this->driver->findElement(WebDriverBy::id('downloadable-favorites-btn'))->getAttribute('class'));
        $this->assertEquals('btn btn-default btn-action btn-success', $this->driver->findElement(WebDriverBy::id('shareable-favorites-btn'))->getAttribute('class'));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('view-all-favorites-btn'))));
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('view-my-favorites-btn'))));
    }

    public function testCartImageModal() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('99999', $this->driver->findElement(WebDriverBy::id('cart-image'))->getAttribute('album-id'));
        $this->assertEquals(4, sizeof($this->driver->findElement(WebDriverBy::id('cart-image'))->findElement(WebDriverBy::className('nav-tabs'))->findElements(WebDriverBy::tagName('li'))));
    }

    public function testCartModal() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('99999', $this->driver->findElement(WebDriverBy::id('cart'))->getAttribute('album-id'));
    }

    public function testCartModalGuest() {
        $this->driver->get($this->baseUrl);
        $searched [99999] = md5("album2345");
        $cookie = new Cookie('searched', json_encode($searched));
        $this->driver->manage()->addCookie($cookie);
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('cart-name'))->getAttribute('value'));
        $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('cart-email'))->getAttribute('value'));
    }

    public function testCartModalAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->driver->findElement(WebDriverBy::id('cart-btn'))->click();
        $this->assertEquals('Max Saperstone', $this->driver->findElement(WebDriverBy::id('cart-name'))->getAttribute('value'));
        $this->assertEquals('msaperst@gmail.com', $this->driver->findElement(WebDriverBy::id('cart-email'))->getAttribute('value'));
    }

    public function testSubmitModal() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('99999', $this->driver->findElement(WebDriverBy::id('submit'))->getAttribute('album-id'));
    }

    public function testSubmitModalGuest() {
        $this->driver->get($this->baseUrl);
        $searched [99999] = md5("album2345");
        $cookie = new Cookie('searched', json_encode($searched));
        $this->driver->manage()->addCookie($cookie);
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('submit-name'))->getAttribute('value'));
        $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('submit-email'))->getAttribute('value'));
    }

    public function testSubmitModalAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertEquals('Max Saperstone', $this->driver->findElement(WebDriverBy::id('submit-name'))->getAttribute('value'));
        $this->assertEquals('msaperst@gmail.com', $this->driver->findElement(WebDriverBy::id('submit-email'))->getAttribute('value'));
    }

    public function testButtonsGuestUser() {
        $this->driver->get($this->baseUrl);
        $searched [99999] = md5("album2345");
        $cookie = new Cookie('searched', json_encode($searched));
        $this->driver->manage()->addCookie($cookie);
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('downloadable-all-btn'))->isEnabled());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('shareable-all-btn'))->isEnabled());
        $this->assertFalse($this->driver->findElement(WebDriverBy::id('cart-btn'))->isEnabled());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('access-btn'))));
    }

    public function testButtonsAdminUser() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('downloadable-all-btn'))->isEnabled());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('shareable-all-btn'))->isEnabled());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('cart-btn'))->isEnabled());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('access-btn'))->isEnabled());
    }

    /**
     * @throws Exception
     */
    public function testButtonsDownloaderUser() {
        $this->sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (3, '99999');");
        $this->driver->get($this->baseUrl);
        $this->loginAs('5510b5e6fffd897c234cafe499f76146');
        $this->driver->get($this->baseUrl . 'user/album.php?album=99999');
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('downloadable-all-btn'))->isEnabled());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('shareable-all-btn'))->isEnabled());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('cart-btn'))->isEnabled());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('access-btn'))));
    }
}