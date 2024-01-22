<?php

namespace coverage\integration;

use PHPUnit\Framework\TestCase;
use SocialMedia;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SocialMediaIntegrationTest extends TestCase {

    private $sql;

    function setUp() {
        $this->sql = new Sql();
        copy(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/flower.jpeg');
    }

    function tearDown() {
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 996;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 997;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 998;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 999;");
        $this->sql->disconnect();
        unlink(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/flower.jpeg');
    }

    public function testEmptyBlogFeed() {
        try {
            $_SERVER['SERVER_NAME'] = "www.examples.com";
            $_SERVER['SERVER_PORT'] = "90";
            $socialMedia = new SocialMedia();
            $socialMedia->generateRSS();
            unset($_SERVER['SERVER_NAME']);
            unset($_SERVER['SERVER_PORT']);
            $this->assertStringStartsWith("<?xml version='1.0'?>
<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>
  <channel>
    <atom:link href='http://www.examples.com/blog.rss' rel='self' type='application/rss+xml' />
    <title>Saperstone Studios Photo Blog</title>
    <description>Blogging our way through engagements, weddings, babies, then families</description>
    <link>http://www.examples.com/blog/</link>
    <language>en</language>

", file_get_contents(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog.rss'));
        } finally {
            unlink(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog.rss');
        }
    }

    public function testGenerateRssOneActivePost() {
        try {
            $_SERVER['SERVER_NAME'] = "www.examples.com";
            $_SERVER['SERVER_PORT'] = "90";
            $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('998', 'Sample Blog', '2031-01-01', '', 0, 1)");
            $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('999', 'Sample Blog', '2031-01-01', '', 0)");
            $socialMedia = new SocialMedia();
            $socialMedia->generateRSS();
            unset($_SERVER['SERVER_NAME']);
            unset($_SERVER['SERVER_PORT']);
            $this->assertStringStartsWith("<?xml version='1.0'?>
<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>
  <channel>
    <atom:link href='http://www.examples.com/blog.rss' rel='self' type='application/rss+xml' />
    <title>Saperstone Studios Photo Blog</title>
    <description>Blogging our way through engagements, weddings, babies, then families</description>
    <link>http://www.examples.com/blog/</link>
    <language>en</language>

    <item>
      <title>Sample Blog</title>
      <link>http://www.examples.com/blog/post.php?p=998</link>
      <guid>http://www.examples.com/blog/post.php?p=998</guid>
    </item>

", file_get_contents(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog.rss'));
        } finally {
            unlink(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog.rss');
        }
    }

    public function testGenerateRssOrderedPosts() {
        try {
            $_SERVER['SERVER_NAME'] = "www.examples.com";
            $_SERVER['SERVER_PORT'] = "90";
            $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('996', 'Sample Blog', '2030-02-01', '', 0, 0)");
            $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('997', 'Sample Blog', '2031-01-01', '', 0, 1)");
            $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('998', 'Sample Blog', '2030-01-01', '', 0, 1)");
            $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`, `active`) VALUES ('999', 'Sample Blog', '2032-01-01', '', 0, 1)");
            $socialMedia = new SocialMedia();
            $socialMedia->generateRSS();
            unset($_SERVER['SERVER_NAME']);
            unset($_SERVER['SERVER_PORT']);
            $this->assertStringStartsWith("<?xml version='1.0'?>
<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>
  <channel>
    <atom:link href='http://www.examples.com/blog.rss' rel='self' type='application/rss+xml' />
    <title>Saperstone Studios Photo Blog</title>
    <description>Blogging our way through engagements, weddings, babies, then families</description>
    <link>http://www.examples.com/blog/</link>
    <language>en</language>

    <item>
      <title>Sample Blog</title>
      <link>http://www.examples.com/blog/post.php?p=999</link>
      <guid>http://www.examples.com/blog/post.php?p=999</guid>
    </item>

    <item>
      <title>Sample Blog</title>
      <link>http://www.examples.com/blog/post.php?p=997</link>
      <guid>http://www.examples.com/blog/post.php?p=997</guid>
    </item>

    <item>
      <title>Sample Blog</title>
      <link>http://www.examples.com/blog/post.php?p=998</link>
      <guid>http://www.examples.com/blog/post.php?p=998</guid>
    </item>

", file_get_contents(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog.rss'));
        } finally {
            unlink(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'blog.rss');
        }
    }
}