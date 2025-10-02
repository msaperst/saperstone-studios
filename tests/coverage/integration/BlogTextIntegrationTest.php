<?php

namespace coverage\integration;

use BadBlogException;
use BadBlogImageException;
use BadBlogTextException;
use BadCommentException;
use BadUserException;
use Blog;
use BlogText;
use BlogTextException;
use PHPUnit\Framework\TestCase;
use Sql;
use SqlException;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class BlogTextIntegrationTest extends TestCase {
    private Sql $sql;

    private string $hash;

    /**
     * @throws SqlException
     */
    public function setUp(): void {
        if (isset($_SESSION ['hash'])) {
            $this->hash = $_SESSION ['hash'];
        }
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('899', 'Sample Blog', '2031-01-01', '/some/img', 0)");
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
        $this->sql->executeStatement("DELETE FROM blog_details WHERE id = 899;");
        $this->sql->executeStatement("DELETE FROM blog_texts WHERE blog = 899");
        $this->sql->disconnect();
    }

    public function testNulls() {
        $this->expectException(BadBlogTextException::class);
        $this->expectExceptionMessage('Blog content group is required');
        new BlogText(new Blog(), null);
    }

    public function testNoGroup() {
        $this->expectException(BadBlogTextException::class);
        $this->expectExceptionMessage('Blog content group is required');
        new BlogText(new Blog(), array());
    }

    public function testBlankGroup() {
        $params = [
            'group' => ''
        ];
        $this->expectException(BadBlogTextException::class);
        $this->expectExceptionMessage('Blog content group can not be blank');
        new BlogText(new Blog(), $params);
    }

    public function testNoText() {
        $params = [
            'group' => '2'
        ];
        $this->expectException(BadBlogTextException::class);
        $this->expectExceptionMessage('Blog content text is required');
        new BlogText(new Blog(), $params);
    }

    public function testBlankText() {
        $params = [
            'group' => '2',
            'text' => ''
        ];
        $this->expectException(BadBlogTextException::class);
        $this->expectExceptionMessage('Blog content text can not be blank');
        new BlogText(new Blog(), $params);
    }

    /**
     * @throws BadBlogTextException
     */
    public function testGetValues() {
        $params = [
            'group' => '2',
            'text' => 'my text'
        ];
        $blogText = new BlogText(new Blog(), $params);
        $this->assertEquals(", 2, 'my text'", $blogText->getValues());
    }

    /**
     * @throws BadUserException
     * @throws BadBlogTextException
     * @throws BadBlogException
     * @throws BadBlogImageException
     * @throws BadCommentException
     */
    public function testSetBlog() {
        $params = [
            'group' => '2',
            'text' => 'my text'
        ];
        $blogText = new BlogText(new Blog(), $params);
        $blogText->setBlog(Blog::withId(899));
        $this->assertEquals("899, 2, 'my text'", $blogText->getValues());
    }

    /**
     * @throws BadBlogException
     * @throws BadBlogImageException
     * @throws BadBlogTextException
     * @throws BadCommentException
     * @throws BadUserException
     * @throws SqlException
     */
    public function testCreateNoPermissions() {
        $params = [
            'group' => '2',
            'text' => 'my text'
        ];
        $blogText = new BlogText(new Blog(), $params);
        $blogText->setBlog(Blog::withId(899));
        $this->expectException(BlogTextException::class);
        $this->expectExceptionMessage('User not authorized to create blog content');
        $blogText->create();
    }

    /**
     * @throws BadBlogTextException
     * @throws SqlException
     * @throws BlogTextException
     * @throws BadUserException
     * @throws BadBlogException
     * @throws BadBlogImageException
     * @throws BadCommentException
     */
    public function testCreate() {
        $params = [
            'group' => '2',
            'text' => 'my text'
        ];
        $blogText = new BlogText(new Blog(), $params);
        $blogText->setBlog(Blog::withId(899));
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $blogText->create();
        $blogDetails = $this->sql->getRows("SELECT * FROM blog_texts WHERE blog = 899");
        $this->assertEquals(1, sizeof($blogDetails));
        $this->assertEquals(899, $blogDetails[0]['blog']);
        $this->assertEquals(2, $blogDetails[0]['contentGroup']);
        $this->assertEquals('my text', $blogDetails[0]['text']);
    }
}