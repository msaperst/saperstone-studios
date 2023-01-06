<?php

namespace ui\page;

use CustomAsserts;
use Exception;
use Facebook\WebDriver\Cookie;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Google\Exception as ExceptionAlias;
use Sql;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestBase.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class TemplatesLoadTest extends TestBase {

    /**
     * @throws ExceptionAlias
     */
    public function testErrorLoad() {
        $this->driver->get($this->baseUrl . 'badPage123');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home 404', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals('Looks like you got turned around. The server has not found anything matching the Request-URI.', $this->driver->findElements(WebDriverBy::className('lead'))[1]->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/badPage123\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/badPage123\' target=\'_blank\'>%s://%s/badPage123</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testFooterContent() {
        $this->driver->get($this->baseUrl);
        $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('my-user-id'))->getAttribute('value'));
        $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('my-user-role'))->getAttribute('value'));
    }

    public function testFooterContentAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl);
        $this->assertEquals('1', $this->driver->findElement(WebDriverBy::id('my-user-id'))->getAttribute('value'));
        $this->assertEquals('admin', $this->driver->findElement(WebDriverBy::id('my-user-role'))->getAttribute('value'));
    }

    public function testNoAnnouncement() {
        $this->driver->get($this->baseUrl);
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::className('alert-warning'))));
    }

    /**
     * @throws Exception
     */
    public function testAnnouncementBadTime() {
        try {
            $sql = new Sql();
            $sql->executeStatement("INSERT INTO `announcements` (`id`, `message`, `path`, `start`, `end`, `dismissible`) VALUES (999, '<a href=\'/blog/post.php?p=458\'>Information Regarding Covid-19</a>', '/', '2000-07-23 00:00:00', '2000-12-31 00:00:00', 1);");
            $this->driver->get($this->baseUrl);
            $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::className('alert-warning'))));
        } finally {
            $sql->executeStatement("DELETE FROM `announcements` WHERE `announcements`.`id` = 999;");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `announcements`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `announcements` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }

    /**
     * @throws Exception
     */
    public function testAnnouncementGoodTime() {
        try {
            $sql = new Sql();
            $sql->executeStatement("INSERT INTO `announcements` (`id`, `message`, `path`, `start`, `end`, `dismissible`) VALUES (999, '<a href=\'/blog/post.php?p=458\'>Information Regarding Covid-19</a>', '/', '2000-07-23 00:00:00', '3000-12-31 00:00:00', 1);");
            $this->driver->get($this->baseUrl);
            $this->assertTrue($this->driver->findElement(WebDriverBy::className('alert-warning'))->isDisplayed());
        } finally {
            $sql->executeStatement("DELETE FROM `announcements` WHERE `announcements`.`id` = 999;");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `announcements`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `announcements` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }

    /**
     * @throws Exception
     */
    public function testAnnouncementDismissed() {
        try {
            $sql = new Sql();
            $sql->executeStatement("INSERT INTO `announcements` (`id`, `message`, `path`, `start`, `end`, `dismissible`) VALUES (999, '<a href=\'/blog/post.php?p=458\'>Information Regarding Covid-19</a>', '/', '2000-07-23 00:00:00', '3000-12-31 00:00:00', 1);");
            $this->driver->get($this->baseUrl);
            $cookie = new Cookie('announcement-999', 'dismissed');
            $this->driver->manage()->addCookie($cookie);
            $this->driver->get($this->baseUrl);
            $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::className('alert-warning'))));
        } finally {
            $sql->executeStatement("DELETE FROM `announcements` WHERE `announcements`.`id` = 999;");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `announcements`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `announcements` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }

    /**
     * @throws Exception
     */
    public function testAnnouncementDomain() {
        try {
            $sql = new Sql();
            $sql->executeStatement("INSERT INTO `announcements` (`id`, `message`, `path`, `start`, `end`, `dismissible`) VALUES (999, '<a href=\'/blog/post.php?p=458\'>Information Regarding Covid-19</a>', '/portrait', '2000-07-23 00:00:00', '3000-12-31 00:00:00', 1);");
            $this->driver->get($this->baseUrl);
            $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::className('alert-warning'))));
            $this->driver->get($this->baseUrl . '/portrait');
            $this->assertTrue($this->driver->findElement(WebDriverBy::className('alert-warning'))->isDisplayed());
        } finally {
            $sql->executeStatement("DELETE FROM `announcements` WHERE `announcements`.`id` = 999;");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `announcements`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `announcements` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }

    public function testMainMenu() {
        $this->driver->get($this->baseUrl);
        $this->assertEquals('Portraits
Weddings
B\'Nai Mitzvahs
Commercial
Blog
Information
Login', $this->driver->findElement(WebDriverBy::id('bs-example-navbar-collapse-1'))->getText());
    }

    private function blogMenu(): RemoteWebElement {
        return $this->driver->findElement(WebDriverBy::id('bs-example-navbar-collapse-1'))->findElements(WebDriverBy::className('dropdown'))[4];
    }

    private function infoMenu(): RemoteWebElement {
        return $this->driver->findElement(WebDriverBy::id('bs-example-navbar-collapse-1'))->findElements(WebDriverBy::className('dropdown'))[5];
    }

    private function userMenu(): RemoteWebElement {
        return $this->driver->findElement(WebDriverBy::id('bs-example-navbar-collapse-1'))->findElements(WebDriverBy::className('dropdown'))[6];
    }

    public function testMainMenuBlog() {
        $this->driver->get($this->baseUrl);
        $this->assertEquals(3, sizeof($this->blogMenu()->findElements(WebDriverBy::tagName('li'))));
    }

    public function testMainMenuBlogAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl);
        $this->assertEquals(5, sizeof($this->blogMenu()->findElements(WebDriverBy::tagName('li'))));
    }

    public function testMainMenuInformation() {
        $this->driver->get($this->baseUrl);
        $this->assertEquals(5, sizeof($this->infoMenu()->findElements(WebDriverBy::tagName('li'))));
    }

    public function testMainMenuInformationAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl);
        $this->assertEquals(4, sizeof($this->infoMenu()->findElements(WebDriverBy::tagName('li'))));
    }

    public function testMainMenuUser() {
        $this->driver->get($this->baseUrl);
        $this->assertEquals(6, sizeof($this->driver->findElement(WebDriverBy::id('bs-example-navbar-collapse-1'))->findElements(WebDriverBy::className('dropdown'))));
    }

    public function testMainMenuUserUser() {
        $this->driver->get($this->baseUrl);
        $this->loginAs('c90788c0e409eac6a95f6c6360d8dbf7');
        $this->driver->get($this->baseUrl);
        $this->assertEquals(7, sizeof($this->driver->findElement(WebDriverBy::id('bs-example-navbar-collapse-1'))->findElements(WebDriverBy::className('dropdown'))));
        $this->assertEquals(3, sizeof($this->userMenu()->findElements(WebDriverBy::tagName('li'))));
    }

    public function testMainMenuUserAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl);
        $this->assertEquals(7, sizeof($this->driver->findElement(WebDriverBy::id('bs-example-navbar-collapse-1'))->findElements(WebDriverBy::className('dropdown'))));
        $this->assertEquals(7, sizeof($this->userMenu()->findElements(WebDriverBy::tagName('li'))));
    }

    public function testPortrait() {
        $this->driver->get($this->baseUrl . '/portrait');
        $this->assertEquals('Details
Gallery
Retouch
Raves
Blog
About
Contact
Login', $this->driver->findElement(WebDriverBy::id('bs-example-navbar-collapse-1'))->getText());
    }

    public function testWedding() {
        $this->driver->get($this->baseUrl . '/wedding');
        $this->assertEquals('Details
Gallery
Retouch
Raves
Blog
About
Contact
Login', $this->driver->findElement(WebDriverBy::id('bs-example-navbar-collapse-1'))->getText());
    }

    public function testCommercial() {
        $this->driver->get($this->baseUrl . '/commercial');
        $this->assertEquals('Details
Gallery
Retouch
Raves
Blog
About
Contact
Login', $this->driver->findElement(WebDriverBy::id('bs-example-navbar-collapse-1'))->getText());
    }
}