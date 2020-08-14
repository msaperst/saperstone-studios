<?php

namespace coverage\integration;

use Comment;
use Exception;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class CommentIntegrationTest extends TestCase {
    private $sql;

    public function setUp() {
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES (898, 899, NULL, 'Anna', '2012-10-31 09:56:47', '68.98.132.164', 'annad@annadbruce.com', 'hehehehehe this rules!')");
        $this->sql->executeStatement("INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES (899, 899, 4, 'Uploader', '2012-10-31 13:56:47', '192.168.1.2', 'msaperst@gmail.com', 'awesome post')");
    }

    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM `blog_comments` WHERE `blog_comments`.`blog` = 899;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_comments`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_comments` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    public function testNullCommentId() {
        try {
            new Comment(NULL);
        } catch (Exception $e) {
            $this->assertEquals("Comment id is required", $e->getMessage());
        }
    }

    public function testBlankCommentId() {
        try {
            new Comment("");
        } catch (Exception $e) {
            $this->assertEquals("Comment id can not be blank", $e->getMessage());
        }
    }

    public function testLetterCommentId() {
        try {
            new Comment("a");
        } catch (Exception $e) {
            $this->assertEquals("Comment id does not match any comments", $e->getMessage());
        }
    }

    public function testBadCommentId() {
        try {
            new Comment(8999);
        } catch (Exception $e) {
            $this->assertEquals("Comment id does not match any comments", $e->getMessage());
        }
    }

    public function testBadStringCommentId() {
        try {
            new Comment("8999");
        } catch (Exception $e) {
            $this->assertEquals("Comment id does not match any comments", $e->getMessage());
        }
    }

    public function testGetId() {
        $comment = new Comment('899');
        $this->assertEquals(899, $comment->getId());
    }

    public function testCanUserGetDataNobody() {
        $comment = new Comment(899);
        $this->assertFalse($comment->canUserGetData());
    }

    public function testCanUserGetDataAdmin() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $comment = new Comment(899);
        $this->assertTrue($comment->canUserGetData());
        unset($_SESSION['hash']);
    }

    public function testCanUserGetDataOwner() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $comment = new Comment(899);
        $this->assertTrue($comment->canUserGetData());
        unset($_SESSION['hash']);
    }

    public function testCanUserGetDataOtherUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $comment = new Comment(899);
        $this->assertFalse($comment->canUserGetData());
        unset($_SESSION['hash']);
    }

    public function testAllDataLoadedCantDelete() {
        date_default_timezone_set("America/New_York");
        $comment = new Comment(898);
        $commentInfo = $comment->getDataArray();
        $this->assertEquals(898, $commentInfo['id']);
        $this->assertEquals(899, $commentInfo['blog']);
        $this->assertNull($commentInfo['user']);
        $this->assertEquals('Anna', $commentInfo['name']);
        $this->assertEquals('2012-10-31 09:56:47', $commentInfo['date']);
        $this->assertEquals('68.98.132.164', $commentInfo['ip']);
        $this->assertEquals('annad@annadbruce.com', $commentInfo['email']);
        $this->assertEquals('hehehehehe this rules!', $commentInfo['comment']);
        $this->assertFalse(key_exists('delete', $commentInfo));
    }

    public function testAllDataLoadedCanDelete() {
        date_default_timezone_set("America/New_York");
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $comment = new comment(899);
        $commentInfo = $comment->getDataArray();
        unset($_SESSION['hash']);
        $this->assertEquals(899, $commentInfo['id']);
        $this->assertEquals(899, $commentInfo['blog']);
        $this->assertEquals(4, $commentInfo['user']);
        $this->assertEquals('Uploader', $commentInfo['name']);
        $this->assertEquals('2012-10-31 13:56:47', $commentInfo['date']);
        $this->assertEquals('192.168.1.2', $commentInfo['ip']);
        $this->assertEquals('msaperst@gmail.com', $commentInfo['email']);
        $this->assertEquals('awesome post', $commentInfo['comment']);
        $this->assertTrue($commentInfo['delete']);
    }

    public function testDeleteNoAccess() {
        $comment = new Comment(899);
        try {
            $comment->delete();
        } catch (Exception $e) {
            $this->assertEquals("User not authorized to delete comment", $e->getMessage());
        }
        $this->assertEquals(2, $this->sql->getRowCount("SELECT * FROM `blog_comments` WHERE `blog_comments`.`blog` = 899;"));
    }

    public function testDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $comment = new Comment(899);
        $comment->delete();
        unset($_SESSION ['hash']);
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_comments` WHERE `blog_comments`.`id` = 899;"));
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `blog_comments` WHERE `blog_comments`.`id` = 898;"));
    }
}