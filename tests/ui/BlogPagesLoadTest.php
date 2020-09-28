<?php

namespace ui;

use Facebook\WebDriver\WebDriverBy;
use Sql;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'TestBase.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

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

    public function testCategoryPageNoT() {
        $this->driver->get($this->baseUrl . 'blog/category.php');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testCategoryPageBlankT() {
        $this->driver->get($this->baseUrl . 'blog/category.php?t=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testCategoryPageBadT() {
        $this->driver->get($this->baseUrl . 'blog/category.php?t=999');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testCategoryPageSingleT() {
        $this->driver->get($this->baseUrl . 'blog/category.php?t=2');
        $this->assertEquals('6 Month Session Blog Posts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testCategoryPageDoubleT() {
        $this->driver->get($this->baseUrl . 'blog/category.php?t=2,3');
        $this->assertEquals('6 Month Session and Babies Blog Posts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testManagePage() {
        $this->driver->get($this->baseUrl . 'blog/manage.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testManagePageAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'blog/manage.php');
        $this->assertEquals('Manage Blog Posts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testNewPage() {
        $this->driver->get($this->baseUrl . 'blog/new.php');
        $this->assertEquals('401 Unauthorized', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testNewPageAdmin() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'blog/new.php');
        $this->assertEquals('Write A New Blog Post', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testNewPageAdminBadPost() {
        $this->driver->get($this->baseUrl);
        $this->adminLogin();
        $this->driver->get($this->baseUrl . 'blog/new.php?p=9999');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testNewPageAdminGoodPost() {
        $sql = new Sql();
        try {
            $sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('999', 'Sample Blog', '2031-01-01', '', 0)");
            $sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '1', 'posts/2031/01/01/sample.jpg', 300, 400, 0, 0)");
            $sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 29)");
            $sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('999', '2', 'Some blog text')");
            $this->driver->get($this->baseUrl);
            $this->adminLogin();
            $this->driver->get($this->baseUrl . 'blog/new.php?p=999');
            $this->assertEquals('Edit Your Blog Post', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
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

    public function testPostPageNoP() {
        $this->driver->get($this->baseUrl . 'blog/post.php');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testPostPageBlankP() {
        $this->driver->get($this->baseUrl . 'blog/post.php?p=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testPostPageBadP() {
        $this->driver->get($this->baseUrl . 'blog/post.php?p=9999');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testPostPageGoodP() {
        $sql = new Sql();
        try {
            $sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('999', 'Sample Blog', '2031-01-01', '', 0, 1)");
            $sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '1', 'posts/2031/01/01/sample.jpg', 300, 400, 0, 0)");
            $sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 29)");
            $sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('999', '2', 'Some blog text')");
            $this->driver->get($this->baseUrl . 'blog/post.php?p=999');
            $this->assertEquals('Sample Blog', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
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

    public function testSearchPageNoS() {
        $this->driver->get($this->baseUrl . 'blog/search.php');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testSearchPageBlankS() {
        $this->driver->get($this->baseUrl . 'blog/search.php?s=');
        $this->assertEquals('404 Not Found', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testSearchPageBadS() {
        $this->driver->get($this->baseUrl . 'blog/search.php?s=xlkjliu');
        $this->assertEquals('Blog Posts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
        $this->assertEquals($this->copyright, $this->driver->findElement(WebDriverBy::className('copyright'))->getText());
    }

    public function testSearchPageGoodS() {
        $sql = new Sql();
        try {
            $sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('999', 'Sample Blog', '2031-01-01', '', 0, 1)");
            $sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '1', 'posts/2031/01/01/sample.jpg', 300, 400, 0, 0)");
            $sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 29)");
            $sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('999', '2', 'Some blog text')");
            $this->driver->get($this->baseUrl . 'blog/search.php?s=sample');
            $this->assertEquals('Blog Posts', $this->driver->findElement(WebDriverBy::tagName('h1'))->getText());
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