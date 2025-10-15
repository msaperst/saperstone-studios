<?php

namespace coverage\integration;

use BadBlogException;
use BadCommentException;
use BadUserException;
use Comment;
use CommentException;
use CustomAsserts;
use PHPUnit\Framework\TestCase;
use Sql;
use SqlException;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class CommentIntegrationTest extends TestCase {
    private Sql $sql;

    private string $hash;

    private string $httpClientIp;

    /**
     * @throws SqlException
     */
    public function setUp(): void {
        if (isset($_SESSION ['hash'])) {
            $this->hash = $_SESSION ['hash'];
        }
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $this->httpClientIp = $_SERVER['HTTP_CLIENT_IP'];
        }
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('899', 'Sample Blog', '2031-01-01', '/some/img', 0)");
        $this->sql->executeStatement("INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES (898, 899, NULL, 'Anna', '2012-10-31 09:56:47', '68.98.132.164', 'annad@annadbruce.com', 'hehehehehe this rules!')");
        $this->sql->executeStatement("INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES (899, 899, 4, 'Uploader', '2012-10-31 13:56:47', '192.168.1.2', 'msaperst@gmail.com', 'awesome post')");
    }

    /**
     * @throws SqlException
     */
    public function tearDown(): void {
        if (isset($this->hash)) {
            $_SESSION ['hash'] = $this->hash;
        } else {
            unset($_SESSION ['hash']);
        }
        if (isset($this->httpClientIp)) {
            $_SERVER['HTTP_CLIENT_IP'] = $this->httpClientIp;
        } else {
            unset($_SERVER['HTTP_CLIENT_IP']);
        }
        $this->sql->executeStatement("DELETE FROM `blog_details` WHERE `blog_details`.`id` = 899;");
        $this->sql->executeStatement("DELETE FROM `blog_comments` WHERE `blog_comments`.`blog` = 899;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_details` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `blog_comments`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `blog_comments` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    /**
     * @throws BadUserException
     */
    public function testNullCommentId() {
        $this->expectException(BadCommentException::class);
        $this->expectExceptionMessage('Comment id is required');
        Comment::withId(NULL);
    }

    /**
     * @throws BadUserException
     */
    public function testBlankCommentId() {
        $this->expectException(BadCommentException::class);
        $this->expectExceptionMessage('Comment id can not be blank');
        Comment::withId("");
    }

    /**
     * @throws BadUserException
     */
    public function testLetterCommentId() {
        $this->expectException(BadCommentException::class);
        $this->expectExceptionMessage('Comment id does not match any comments');
        Comment::withId("a");
    }

    /**
     * @throws BadUserException
     */
    public function testBadCommentId() {
        $this->expectException(BadCommentException::class);
        $this->expectExceptionMessage('Comment id does not match any comments');
        Comment::withId(8999);
    }

    /**
     * @throws BadUserException
     */
    public function testBadStringCommentId() {
        $this->expectException(BadCommentException::class);
        $this->expectExceptionMessage('Comment id does not match any comments');
        Comment::withId("8999");
    }

    /**
     * @throws BadCommentException
     * @throws BadUserException
     */
    public function testGetId() {
        $comment = Comment::withId('899');
        $this->assertEquals(899, $comment->getId());
    }

    /**
     * @throws BadCommentException
     * @throws BadUserException
     */
    public function testCanUserGetDataNobody() {
        $comment = Comment::withId(899);
        $this->assertFalse($comment->canUserGetData());
    }

    /**
     * @throws BadCommentException
     * @throws BadUserException
     */
    public function testCanUserGetDataAdmin() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $comment = Comment::withId(899);
        $this->assertTrue($comment->canUserGetData());
    }

    /**
     * @throws BadCommentException
     * @throws BadUserException
     */
    public function testCanUserGetDataOwner() {
        $_SESSION ['hash'] = "c90788c0e409eac6a95f6c6360d8dbf7";
        $comment = Comment::withId(899);
        $this->assertTrue($comment->canUserGetData());
    }

    /**
     * @throws BadCommentException
     * @throws BadUserException
     */
    public function testCanUserGetDataOtherUser() {
        $_SESSION ['hash'] = "5510b5e6fffd897c234cafe499f76146";
        $comment = Comment::withId(899);
        $this->assertFalse($comment->canUserGetData());
    }

    /**
     * @throws BadCommentException
     * @throws BadUserException
     */
    public function testAllDataLoadedCantDelete() {
        date_default_timezone_set("America/New_York");
        $comment = Comment::withId(898);
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

    /**
     * @throws BadCommentException
     * @throws BadUserException
     */
    public function testAllDataLoadedCanDelete() {
        date_default_timezone_set("America/New_York");
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $comment = Comment::withId(899);
        $commentInfo = $comment->getDataArray();

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

    /**
     * @throws BadCommentException
     * @throws BadUserException
     * @throws SqlException
     */
    public function testDeleteNoAccess() {
        $comment = Comment::withId(899);
        $this->expectException(CommentException::class);
        $this->expectExceptionMessage('User not authorized to delete comment');
        $comment->delete();
        $this->assertEquals(2, $this->sql->getRowCount("SELECT * FROM `blog_comments` WHERE `blog_comments`.`blog` = 899;"));
    }

    /**
     * @throws BadUserException
     * @throws SqlException
     * @throws BadCommentException
     * @throws CommentException
     */
    public function testDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $comment = Comment::withId(899);
        $comment->delete();

        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `blog_comments` WHERE `blog_comments`.`id` = 899;"));
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `blog_comments` WHERE `blog_comments`.`id` = 898;"));
    }

    /**
     * @throws BadCommentException
     * @throws BadUserException
     */
    public function testWithParamsNull() {
        $this->expectException(BadBlogException::class);
        $this->expectExceptionMessage('Blog id is required');
        Comment::withParams(NULL);
    }

    /**
     * @throws BadCommentException
     * @throws BadUserException
     */
    public function testWithParamsEmpty() {
        $this->expectException(BadBlogException::class);
        $this->expectExceptionMessage('Blog id is required');
        Comment::withParams(array());
    }

    /**
     * @throws BadUserException
     * @throws BadBlogException
     */
    public function testWithParamsNoMessage() {
        $params = [
            'post' => '899'
        ];
        $this->expectException(BadCommentException::class);
        $this->expectExceptionMessage('Message is required');
        Comment::withParams($params);
    }

    /**
     * @throws BadBlogException
     * @throws BadUserException
     */
    public function testWithParamsBlankMessage() {
        $params = [
            'post' => '899',
            'message' => ''
        ];
        $this->expectException(BadCommentException::class);
        $this->expectExceptionMessage('Message can not be blank');
        Comment::withParams($params);
    }

    /**
     * @throws BadCommentException
     * @throws BadBlogException
     * @throws BadUserException
     */
    public function testWithParams() {
        $params = [
            'post' => '899',
            'message' => 'Some message'
        ];
        $comment = Comment::withParams($params);
        $this->assertNull($comment->getId());
        $this->assertNull($comment->getDate());
    }

    /**
     * @throws BadUserException
     * @throws BadCommentException
     * @throws SqlException
     * @throws BadBlogException
     */
    public function testCreateBasic() {
        date_default_timezone_set("America/New_York");
        $params = [
            'post' => '899',
            'message' => 'Some message'
        ];
        $comment = Comment::withParams($params);
        $_SERVER["HTTP_CLIENT_IP"] = '1.1.1.1';
        $comment->create();
        $commentInfo = $comment->getDataArray();
        $this->assertEquals(900, $commentInfo['id']);
        $this->assertEquals(899, $commentInfo['blog']);
        $this->assertNull($commentInfo['user']);
        $this->assertEquals('', $commentInfo['name']);
        CustomAsserts::timeWithin(2, $commentInfo['date']);
        $this->assertEquals('1.1.1.1', $commentInfo['ip']);
        $this->assertEquals('', $commentInfo['email']);
        $this->assertEquals('Some message', $commentInfo['comment']);
        $this->assertFalse(key_exists('delete', $commentInfo));
    }

    /**
     * @throws BadUserException
     * @throws SqlException
     * @throws BadBlogException
     * @throws BadCommentException
     */
    public function testCreateFull() {
        date_default_timezone_set("America/New_York");
        $params = [
            'post' => '899',
            'name' => 'max',
            'email' => 'max@max.max',
            'message' => 'Some message'
        ];
        $comment = Comment::withParams($params);
        $_SERVER["HTTP_CLIENT_IP"] = '1.1.1.1';
        $comment->create();
        $commentInfo = $comment->getDataArray();
        $this->assertEquals(900, $commentInfo['id']);
        $this->assertEquals(899, $commentInfo['blog']);
        $this->assertNull($commentInfo['user']);
        $this->assertEquals('max', $commentInfo['name']);
        CustomAsserts::timeWithin(2, $commentInfo['date']);
        $this->assertEquals('1.1.1.1', $commentInfo['ip']);
        $this->assertEquals('max@max.max', $commentInfo['email']);
        $this->assertEquals('Some message', $commentInfo['comment']);
        $this->assertFalse(key_exists('delete', $commentInfo));
    }

    /**
     * @throws BadUserException
     * @throws BadCommentException
     * @throws SqlException
     * @throws BadBlogException
     */
    public function testCreateLoggedIn() {
        date_default_timezone_set("America/New_York");
        $params = [
            'post' => '899',
            'name' => 'max',
            'email' => 'max@max.max',
            'message' => 'Some message'
        ];
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $comment = Comment::withParams($params);
        $_SERVER["HTTP_CLIENT_IP"] = '1.1.1.1';
        $comment->create();
        $commentInfo = $comment->getDataArray();
        $this->assertEquals(900, $commentInfo['id']);
        $this->assertEquals(899, $commentInfo['blog']);
        $this->assertEquals(1, $commentInfo['user']);
        $this->assertEquals('max', $commentInfo['name']);
        CustomAsserts::timeWithin(2, $commentInfo['date']);
        $this->assertEquals('1.1.1.1', $commentInfo['ip']);
        $this->assertEquals('max@max.max', $commentInfo['email']);
        $this->assertEquals('Some message', $commentInfo['comment']);
        $this->assertTrue($commentInfo['delete']);
    }
}