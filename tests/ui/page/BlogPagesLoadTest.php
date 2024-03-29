<?php

namespace ui\page;

use CustomAsserts;
use Exception;
use Facebook\WebDriver\WebDriverBy;
use Google\Exception as ExceptionAlias;
use Sql;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestBase.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class BlogPagesLoadTest extends TestBase {

    public function testCategoriesPage() {
        $this->driver->get($this->baseUrl . 'blog/categories.php');
        $this->assertEquals('Blog Categories', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testIndexPage() {
        $this->driver->get($this->baseUrl . 'blog/index.php');
        $this->assertEquals('Blog Posts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testPostsPage() {
        $this->driver->get($this->baseUrl . 'blog/posts.php');
        $this->assertEquals('Recent Blog Posts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    // testing pages with logic in them

    /**
     * @throws ExceptionAlias
     */
    public function testCategoryPageNoT() {
        $this->driver->get($this->baseUrl . 'blog/category.php');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/blog/category.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/blog/category.php\' target=\'_blank\'>%s://%s/blog/category.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws ExceptionAlias
     */
    public function testCategoryPageBlankT() {
        $this->driver->get($this->baseUrl . 'blog/category.php?t=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/blog/category.php?t=\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/blog/category.php?t=\' target=\'_blank\'>%s://%s/blog/category.php?t=</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws ExceptionAlias
     */
    public function testCategoryPageBadT() {
        $this->driver->get($this->baseUrl . 'blog/category.php?t=999');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/blog/category.php?t=999\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/blog/category.php?t=999\' target=\'_blank\'>%s://%s/blog/category.php?t=999</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');    }

    public function testCategoryPageSingleT() {
        $this->driver->get($this->baseUrl . 'blog/category.php?t=2');
        $this->assertEquals('6 Month Session Blog Posts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home Blog Categories 6 Month Session', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testCategoryPageDoubleT() {
        $this->driver->get($this->baseUrl . 'blog/category.php?t=2,3');
        $this->assertEquals('6 Month Session and Babies Blog Posts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home Blog Categories 6 Month Session and Babies', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    /**
     * @throws ExceptionAlias
     */
    public function testManagePage() {
        $this->driver->get($this->baseUrl . 'blog/manage.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/blog/manage.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/blog/manage.php\' target=\'_blank\'>%s://%s/blog/manage.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testManagePageAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'blog/manage.php');
        $this->assertEquals('Manage Blog Posts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    /**
     * @throws ExceptionAlias
     */
    public function testNewPage() {
        $this->driver->get($this->baseUrl . 'blog/new.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('401 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 401 on page %s://%s/blog/new.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 401 on page <a href=\'%s://%s/blog/new.php\' target=\'_blank\'>%s://%s/blog/new.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testNewPageAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'blog/new.php');
        $this->assertEquals('Write A New Blog Post', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home Blog New Post', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('update-post'))));
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('save-post'))->isDisplayed());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('schedule-post'))->isDisplayed());
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('publish-post'))->isDisplayed());
        $this->assertEquals('../tmp', $this->driver->findElement(WebDriverBy::id('post'))->getAttribute('post-location'));
        $this->assertNull($this->driver->findElement(WebDriverBy::id('post'))->getAttribute('post-id'));
        $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('post-title-input'))->getAttribute('value'));
        $this->assertEquals(date("Y-m-d"), $this->driver->findElement(WebDriverBy::id('post-date-input'))->getAttribute('value'));
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    /**
     * @throws ExceptionAlias
     */
    public function testNewPageAdminBadPost() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'blog/new.php?p=9999');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/blog/new.php?p=9999\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
User Id: 1
Name: Max Saperstone
Email: msaperst@gmail.com
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/blog/new.php?p=9999\' target=\'_blank\'>%s://%s/blog/new.php?p=9999</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>User Id</strong>: 1<br/><strong>Name</strong>: Max Saperstone<br/><strong>Email</strong>: <a href=\'mailto:msaperst@gmail.com\'>msaperst@gmail.com</a><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws Exception
     */
    public function testNewPageAdminGoodPost() {
        $sql = new Sql();
        try {
            $sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('999', 'Sample Blog', '2031-01-01', 'posts/2031/01/01/preview_image-999.jpg', 0)");
            $sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '1', 'posts/2031/01/01/sample.jpg', 300, 400, 0, 0)");
            $sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 29)");
            $sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('999', '2', 'Some blog text')");
            $this->driver->get($this->baseUrl);
            $this->adminLogin();
            $this->driver->get($this->baseUrl . 'blog/new.php?p=999');
            $this->assertEquals('Edit Your Blog Post', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
            $this->assertEquals('Home Blog Edit Post', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
            $this->assertTrue($this->driver->findElement(WebDriverBy::id('update-post'))->isDisplayed());
            $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('save-post'))));
            $this->assertTrue($this->driver->findElement(WebDriverBy::id('schedule-saved-post'))->isDisplayed());
            $this->assertTrue($this->driver->findElement(WebDriverBy::id('publish-saved-post'))->isDisplayed());
            $this->assertEquals('posts/2031/01/01', $this->driver->findElement(WebDriverBy::id('post'))->getAttribute('post-location'));
            $this->assertEquals(999, $this->driver->findElement(WebDriverBy::id('post'))->getAttribute('post-id'));
            $this->assertEquals('Sample Blog', $this->driver->findElement(WebDriverBy::id('post-title-input'))->getAttribute('value'));
            $this->assertEquals(date("2031-01-01"), $this->driver->findElement(WebDriverBy::id('post-date-input'))->getAttribute('value'));
            $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        } finally {
            $sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 999;");
            $sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = 999;");
            $sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;");
            $sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = 999;");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }

    /**
     * @throws Exception
     */
    public function testNewPageAdminGoodPostActive() {
        $sql = new Sql();
        try {
            $sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('999', 'Sample Blog', '2031-01-01', 'posts/2031/01/01/preview_image-999.jpg', 0, 1)");
            $sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '1', 'posts/2031/01/01/sample.jpg', 300, 400, 0, 0)");
            $sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 29)");
            $sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('999', '2', 'Some blog text')");
            $this->driver->get($this->baseUrl);
            $this->adminLogin();
            $this->driver->get($this->baseUrl . 'blog/new.php?p=999');
            $this->assertEquals('Edit Your Blog Post', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
            $this->assertEquals('Home Blog Edit Post', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
            $this->assertTrue($this->driver->findElement(WebDriverBy::id('update-post'))->isDisplayed());
            $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('save-post'))));
            $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('schedule-saved-post'))));
            $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('publish-saved-post'))));
            $this->assertEquals('posts/2031/01/01', $this->driver->findElement(WebDriverBy::id('post'))->getAttribute('post-location'));
            $this->assertEquals(999, $this->driver->findElement(WebDriverBy::id('post'))->getAttribute('post-id'));
            $this->assertEquals('Sample Blog', $this->driver->findElement(WebDriverBy::id('post-title-input'))->getAttribute('value'));
            $this->assertEquals(date("2031-01-01"), $this->driver->findElement(WebDriverBy::id('post-date-input'))->getAttribute('value'));
            $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        } finally {
            $sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 999;");
            $sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = 999;");
            $sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;");
            $sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = 999;");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }

    /**
     * @throws ExceptionAlias
     */
    public function testPostPageNoP() {
        $this->driver->get($this->baseUrl . 'blog/post.php');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/blog/post.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/blog/post.php\' target=\'_blank\'>%s://%s/blog/post.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws ExceptionAlias
     */
    public function testPostPageBlankP() {
        $this->driver->get($this->baseUrl . 'blog/post.php?p=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/blog/post.php?p=\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/blog/post.php?p=\' target=\'_blank\'>%s://%s/blog/post.php?p=</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws ExceptionAlias
     */
    public function testPostPageBadP() {
        $this->driver->get($this->baseUrl . 'blog/post.php?p=9999');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/blog/post.php?p=9999\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/blog/post.php?p=9999\' target=\'_blank\'>%s://%s/blog/post.php?p=9999</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws Exception
     */
    public function testPostPageGoodP() {
        $sql = new Sql();
        try {
            $sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('999', 'Sample Blog', '2031-01-01', '', 0, 1)");
            $sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '1', 'posts/2031/01/01/sample.jpg', 300, 400, 0, 0)");
            $sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 29)");
            $sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('999', '2', 'Some blog text')");
            $this->driver->get($this->baseUrl . 'blog/post.php?p=999');
            $this->assertEquals('Sample Blog', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
            $this->assertEquals(0, sizeof($this->driver->findElements(WebDriverBy::id('edit-post-btn'))));
            $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('post-comment-user'))->getAttribute('value'));
            $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('post-comment-name'))->getAttribute('value'));
            $this->assertEquals('', $this->driver->findElement(WebDriverBy::id('post-comment-email'))->getAttribute('value'));
            $this->assertEquals('999', $this->driver->findElement(WebDriverBy::id('post-comment-submit'))->getAttribute('post-id'));
            $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        } finally {
            $sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 999;");
            $sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = 999;");
            $sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;");
            $sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = 999;");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }

    /**
     * @throws Exception
     */
    public function testPostPageGoodPAdmin() {
        $sql = new Sql();
        try {
            $sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('999', 'Sample Blog', '2031-01-01', '', 0, 1)");
            $sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '1', 'posts/2031/01/01/sample.jpg', 300, 400, 0, 0)");
            $sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 29)");
            $sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('999', '2', 'Some blog text')");
            $this->driver->get($this->baseUrl);
            $this->adminLogin();
            $this->driver->get($this->baseUrl . 'blog/post.php?p=999');
            $this->assertEquals('Sample Blog', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
            $this->assertTrue($this->driver->findElement(WebDriverBy::id('edit-post-btn'))->isDisplayed());
            $this->assertEquals(1, $this->driver->findElement(WebDriverBy::id('post-comment-user'))->getAttribute('value'));
            $this->assertEquals('Max Saperstone', $this->driver->findElement(WebDriverBy::id('post-comment-name'))->getAttribute('value'));
            $this->assertEquals('msaperst@gmail.com', $this->driver->findElement(WebDriverBy::id('post-comment-email'))->getAttribute('value'));
            $this->assertEquals('999', $this->driver->findElement(WebDriverBy::id('post-comment-submit'))->getAttribute('post-id'));
            $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        } finally {
            $sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 999;");
            $sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = 999;");
            $sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;");
            $sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = 999;");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }

    /**
     * @throws ExceptionAlias
     */
    public function testSearchPageNoS() {
        $this->driver->get($this->baseUrl . 'blog/search.php');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/blog/search.php\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/blog/search.php\' target=\'_blank\'>%s://%s/blog/search.php</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    /**
     * @throws ExceptionAlias
     */
    public function testSearchPageBlankS() {
        $this->driver->get($this->baseUrl . 'blog/search.php?s=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        CustomAsserts::assertEmailMatches('404 Error',
            "This is an automatically generated message from Saperstone Studios\r
\t\tSomeone got a 404 on page %s://%s/blog/search.php?s=\r
\t\tThey came from page Unknown\r
\t\tYou might want to look into this or take action\r
\t\tUser information is collected before\r
\r
Location: unknown (use %d.%d.%d.%d to manually lookup)\r
Browser: %s %s\r
Resolution: %dx%d\r
OS: %s\r
Full UA: %s\r\n",
            '<html><body>This is an automatically generated message from Saperstone Studios<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Someone got a 404 on page <a href=\'%s://%s/blog/search.php?s=\' target=\'_blank\'>%s://%s/blog/search.php?s=</a><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;They came from page <a href=\'Unknown\' target=\'_blank\'>Unknown</a>.<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;You might want to look into this or take action<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User information is collected before<br/><br/><strong>Location</strong>: unknown (use %d.%d.%d.%d to manually lookup)<br/><strong>Browser</strong>: %s %s<br/><strong>Resolution</strong>: %dx%d<br/><strong>OS</strong>: %s<br/><strong>Full UA</strong>: %s<br/></body></html>');
    }

    public function testSearchPageBadS() {
        $this->driver->get($this->baseUrl . 'blog/search.php?s=xlkjliu');
        $this->assertEquals('Blog Posts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals('Home Blog Search xlkjliu', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    /**
     * @throws Exception
     */
    public function testSearchPageGoodS() {
        $sql = new Sql();
        try {
            $sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('999', 'Sample Blog', '2031-01-01', '', 0, 1)");
            $sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '1', 'posts/2031/01/01/sample.jpg', 300, 400, 0, 0)");
            $sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 29)");
            $sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('999', '2', 'Some blog text')");
            $this->driver->get($this->baseUrl . 'blog/search.php?s=sample');
            $this->assertEquals('Blog Posts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
            $this->assertEquals('Home Blog Search sample', $this->driver->findElement(WebDriverBy::className('breadcrumb'))->getText());
            $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
        } finally {
            $sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 999;");
            $sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = 999;");
            $sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;");
            $sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = 999;");
            $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
            $count++;
            $sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
            $sql->disconnect();
        }
    }
}