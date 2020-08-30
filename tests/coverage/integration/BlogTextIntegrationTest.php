<?php

namespace coverage\integration;

use Blog;
use BlogText;
use Exception;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class BlogTextIntegrationTest extends TestCase {
    private $sql;

    public function setUp() {
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('899', 'Sample Blog', '2031-01-01', '/some/img', 0)");
    }

    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM blog_details WHERE id = 899;");
        $this->sql->disconnect();
    }

    public function testNulls() {
        try {
            new BlogText(new Blog(), null);
        } catch (Exception $e) {
            $this->assertEquals('Blog content group is required', $e->getMessage());
        }
    }

    public function testNoGroup() {
        try {
            new BlogText(new Blog(), array());
        } catch (Exception $e) {
            $this->assertEquals('Blog content group is required', $e->getMessage());
        }
    }

    public function testBlankGroup() {
        $params = [
            'group' => ''
        ];
        try {
            new BlogText(new Blog(), $params);
        } catch (Exception $e) {
            $this->assertEquals('Blog content group can not be blank', $e->getMessage());
        }
    }

    public function testNoText() {
        $params = [
            'group' => '2'
        ];
        try {
            new BlogText(new Blog(), $params);
        } catch (Exception $e) {
            $this->assertEquals('Blog content text is required', $e->getMessage());
        }
    }

    public function testBlankText() {
        $params = [
            'group' => '2',
            'text' => ''
        ];
        try {
            new BlogText(new Blog(), $params);
        } catch (Exception $e) {
            $this->assertEquals('Blog content text can not be blank', $e->getMessage());
        }
    }

    public function testGetValues() {
        $params = [
            'group' => '2',
            'text' => 'my text'
        ];
        $blogText = new BlogText(new Blog(), $params);
        $this->assertEquals(", 2, 'my text'", $blogText->getValues());
    }

    public function testSetBlog() {
        $params = [
            'group' => '2',
            'text' => 'my text'
        ];
        $blogText = new BlogText(new Blog(), $params);
        $blogText->setBlog(Blog::withId(899));
        $this->assertEquals("899, 2, 'my text'", $blogText->getValues());
    }

    public function testCreateNoPermissions() {
        $params = [
            'group' => '2',
            'text' => 'my text'
        ];
        $blogText = new BlogText(new Blog(), $params);
        $blogText->setBlog(Blog::withId(899));
        try {
            $blogText->create();
        } catch (Exception $e) {
            $this->assertEquals('User not authorized to create blog content', $e->getMessage());
        }
    }

    public function testCreate() {
        $params = [
            'group' => '2',
            'text' => 'my text'
        ];
        $blogText = new BlogText(new Blog(), $params);
        $blogText->setBlog(Blog::withId(899));
        try {
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $blogText->create();
            $blogDetails = $this->sql->getRows("SELECT * FROM blog_texts WHERE blog = 899");
            $this->assertEquals(1, sizeof($blogDetails));
            $this->assertEquals(899, $blogDetails[0]['blog']);
            $this->assertEquals(2, $blogDetails[0]['contentGroup']);
            $this->assertEquals('my text', $blogDetails[0]['text']);
        } finally {
            unset($_SESSION ['hash']);
            $this->sql->executeStatement("DELETE FROM blog_texts WHERE blog = 899");
        }
    }
}