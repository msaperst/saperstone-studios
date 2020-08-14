<?php

namespace coverage\integration;

use Blog;
use Exception;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class BlogIntegrationTest extends TestCase {
    private $sql;

    public function setUp() {
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('898', 'Sample Blog', '2031-01-01', '', 0)");
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('899', 'Sample Blog', '2031-01-01', '', 0)");
        $this->sql->executeStatement("INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES (898, 899, NULL, 'Anna', '2012-10-31 09:56:47', '68.98.132.164', 'annad@annadbruce.com', 'hehehehehe this rules!')");
        $this->sql->executeStatement("INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES (899, 899, 4, 'Uploader', '2012-10-31 13:56:47', '192.168.1.2', 'msaperst@gmail.com', 'awesome post')");
        $this->sql->executeStatement("INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('899', '1', 'posts/2031/01/01/sample.jpg', 300, 400, 0, 0)");
        $this->sql->executeStatement("INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('899', 29)");
        $this->sql->executeStatement("INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('899', '2', 'Some blog text')");
        $oldmask = umask(0);
        mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts/2031/01/01', 0777, true);
        touch(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts/2031/01/01/sample.jpg');
        chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts/2031/01/01/sample.jpg', 0777);
        umask($oldmask);
    }

    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 898;");
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 899;");
        $this->sql->executeStatement("DELETE FROM `blog_images` WHERE `blog_images`.`blog` = 899;");
        $this->sql->executeStatement("DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 899;");
        $this->sql->executeStatement("DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = 899;");
        $this->sql->executeStatement("DELETE FROM `blog_comments` WHERE `blog_comments`.`blog` = 899;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_comments`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_comments` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
        system("rm -rf " . escapeshellarg(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts'));
    }

    public function testNullBlogId() {
        try {
            new Blog(NULL);
        } catch (Exception $e) {
            $this->assertEquals("Blog id is required", $e->getMessage());
        }
    }

    public function testBlankBlogId() {
        try {
            new Blog("");
        } catch (Exception $e) {
            $this->assertEquals("Blog id can not be blank", $e->getMessage());
        }
    }

    public function testLetterBlogId() {
        try {
            new Blog("a");
        } catch (Exception $e) {
            $this->assertEquals("Blog id does not match any blog posts", $e->getMessage());
        }
    }

    public function testBadBlogId() {
        try {
            new Blog(8999);
        } catch (Exception $e) {
            $this->assertEquals("Blog id does not match any blog posts", $e->getMessage());
        }
    }

    public function testBadStringBlogId() {
        try {
            new Blog("8999");
        } catch (Exception $e) {
            $this->assertEquals("Blog id does not match any blog posts", $e->getMessage());
        }
    }

    public function testGetId() {
        $blog = new Blog('899');
        $this->assertEquals(899, $blog->getId());
    }

    public function testAllDataLoadedMinimal() {
        date_default_timezone_set("America/New_York");
        $blog = new Blog(898);
        $blogInfo = $blog->getDataArray();
        $this->assertEquals(898, $blogInfo['id']);
        $this->assertEquals('Sample Blog', $blogInfo['title']);
        $this->assertNull($blogInfo['safe_title']);
        $this->assertEquals('January 1st, 2031', $blogInfo['date']);
        $this->assertEquals('', $blogInfo['preview']);
        $this->assertEquals('0', $blogInfo['offset']);
        $this->assertEquals('0', $blogInfo['active']);
        $this->assertEquals(0, $blogInfo['twitter']);
        $this->assertEquals(array(), $blogInfo['tags']);
        $this->assertEquals(array(), $blogInfo['comments']);
        $this->assertEquals(array(), $blogInfo['content']);
    }

    public function testAllDataLoaded() {
        date_default_timezone_set("America/New_York");
        $blog = new Blog(899);
        $blogInfo = $blog->getDataArray();
        $this->assertEquals(899, $blogInfo['id']);
        $this->assertEquals('Sample Blog', $blogInfo['title']);
        $this->assertNull($blogInfo['safe_title']);
        $this->assertEquals('January 1st, 2031', $blogInfo['date']);
        $this->assertEquals('', $blogInfo['preview']);
        $this->assertEquals('0', $blogInfo['offset']);
        $this->assertEquals('0', $blogInfo['active']);
        $this->assertEquals(0, $blogInfo['twitter']);
        $this->assertEquals(2, sizeOf($blogInfo['content']));
        $this->assertEquals(1, sizeOf($blogInfo['content'][1]));
        $this->assertEquals(899, $blogInfo['content'][1][0]['blog']);
        $this->assertEquals(1, $blogInfo['content'][1][0]['contentGroup']);
        $this->assertEquals('posts/2031/01/01/sample.jpg', $blogInfo['content'][1][0]['location']);
        $this->assertEquals(300, $blogInfo['content'][1][0]['width']);
        $this->assertEquals(400, $blogInfo['content'][1][0]['height']);
        $this->assertEquals(0, $blogInfo['content'][1][0]['left']);
        $this->assertEquals(0, $blogInfo['content'][1][0]['top']);
        $this->assertEquals(1, sizeOf($blogInfo['content'][2]));
        $this->assertEquals(899, $blogInfo['content'][2][0]['blog']);
        $this->assertEquals(2, $blogInfo['content'][2][0]['contentGroup']);
        $this->assertEquals('Some blog text', $blogInfo['content'][2][0]['text']);
        $this->assertEquals(1, sizeOf($blogInfo['tags']));
        $this->assertEquals(29, $blogInfo['tags'][0]['id']);
        $this->assertEquals('Tea Ceremony', $blogInfo['tags'][0]['tag']);
        $this->assertEquals(2, sizeOf($blogInfo['comments']));
        $this->assertEquals(899, $blogInfo['comments'][0]['id']);
        $this->assertEquals(899, $blogInfo['comments'][0]['blog']);
        $this->assertEquals('awesome post', $blogInfo['comments'][0]['comment']);
        $this->assertEquals('2012-10-31 13:56:47', $blogInfo['comments'][0]['date']);
        $this->assertEquals('msaperst@gmail.com', $blogInfo['comments'][0]['email']);
        $this->assertEquals('192.168.1.2', $blogInfo['comments'][0]['ip']);
        $this->assertEquals('Uploader', $blogInfo['comments'][0]['name']);
        $this->assertEquals(4, $blogInfo['comments'][0]['user']);
        $this->assertEquals(898, $blogInfo['comments'][1]['id']);
        $this->assertEquals(899, $blogInfo['comments'][1]['blog']);
        $this->assertEquals('hehehehehe this rules!', $blogInfo['comments'][1]['comment']);
        $this->assertEquals('2012-10-31 09:56:47', $blogInfo['comments'][1]['date']);
        $this->assertEquals('annad@annadbruce.com', $blogInfo['comments'][1]['email']);
        $this->assertEquals('68.98.132.164', $blogInfo['comments'][1]['ip']);
        $this->assertEquals('Anna', $blogInfo['comments'][1]['name']);
        $this->assertNull($blogInfo['comments'][1]['user']);
    }

    public function testDeleteNoAccess() {
        $blog = new Blog(899);
        try {
            $blog->delete();
        } catch (Exception $e) {
            $this->assertEquals("User not authorized to delete blog post", $e->getMessage());
        }
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 899;"));
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 899;"));
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 899;"));
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 899;"));
        $this->assertEquals(2, $this->sql->getRowCount("SELECT * FROM `blog_comments` WHERE `blog_comments`.`blog` = 899;"));
        $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts/2031/01/01/sample.jpg'));
    }

    public function testDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $blog = new Blog(899);
        $blog->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_details` WHERE `blog_details`.`id` = 899;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_images` WHERE `blog_images`.`blog` = 899;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_tags` WHERE `blog_tags`.`blog` = 899;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_texts` WHERE `blog_texts`.`blog` = 899;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_comments` WHERE `blog_comments`.`blog` = 899;"));
        $this->assertFalse(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts/2031/01/01/sample.jpg'));
    }
}