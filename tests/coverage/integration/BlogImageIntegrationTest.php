<?php

namespace coverage\integration;

use Blog;
use BlogImage;
use Exception;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class BlogImageIntegrationTest extends TestCase {
    private $sql;

    public function setUp() {
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('899', 'Sample Blog', '2031-01-01', 'posts/2030/01/01/preview_image-899.jpg', 0)");
    }

    public function tearDown() {
        $this->sql->executeStatement("DELETE FROM blog_details WHERE id = 899;");
        $this->sql->disconnect();
    }

    public function testNulls() {
        try {
            new BlogImage(new Blog(), null, null);
        } catch (Exception $e) {
            $this->assertEquals('Blog content group is required', $e->getMessage());
        }
    }

    public function testNullGroup() {
        try {
            new BlogImage(new Blog(), null, array());
        } catch (Exception $e) {
            $this->assertEquals('Blog content group is required', $e->getMessage());
        }
    }

    public function testNoGroup() {
        try {
            new BlogImage(new Blog(), '', array());
        } catch (Exception $e) {
            $this->assertEquals('Blog content group can not be blank', $e->getMessage());
        }
    }

    public function testNoTop() {
        try {
            new BlogImage(new Blog(), 1, array());
        } catch (Exception $e) {
            $this->assertEquals('Blog image top location is required', $e->getMessage());
        }
    }

    public function testBlankTop() {
        $params = [
            'top' => ''
        ];
        try {
            new BlogImage(new Blog(), 1, $params);
        } catch (Exception $e) {
            $this->assertEquals('Blog image top location can not be blank', $e->getMessage());
        }
    }

    public function testNoLeft() {
        $params = [
            'top' => '0'
        ];
        try {
            new BlogImage(new Blog(), 1, $params);
        } catch (Exception $e) {
            $this->assertEquals('Blog image left location is required', $e->getMessage());
        }
    }

    public function testBlankLeft() {
        $params = [
            'top' => '0',
            'left' => ''
        ];
        try {
            new BlogImage(new Blog(), 1, $params);
        } catch (Exception $e) {
            $this->assertEquals('Blog image left location can not be blank', $e->getMessage());
        }
    }

    public function testNoWidth() {
        $params = [
            'top' => '0',
            'left' => 0
        ];
        try {
            new BlogImage(new Blog(), 1, $params);
        } catch (Exception $e) {
            $this->assertEquals('Blog image width is required', $e->getMessage());
        }
    }

    public function testBlankWidth() {
        $params = [
            'top' => '0',
            'left' => 0,
            'width' => ''
        ];
        try {
            new BlogImage(new Blog(), 1, $params);
        } catch (Exception $e) {
            $this->assertEquals('Blog image width can not be blank', $e->getMessage());
        }
    }

    public function testNoHeight() {
        $params = [
            'top' => '0',
            'left' => 0,
            'width' => 1000
        ];
        try {
            new BlogImage(new Blog(), 1, $params);
        } catch (Exception $e) {
            $this->assertEquals('Blog image height is required', $e->getMessage());
        }
    }

    public function testBlankHeight() {
        $params = [
            'top' => '0',
            'left' => 0,
            'width' => 1000,
            'height' => ''
        ];
        try {
            new BlogImage(new Blog(), 1, $params);
        } catch (Exception $e) {
            $this->assertEquals('Blog image height can not be blank', $e->getMessage());
        }
    }

    public function testNoLocation() {
        $params = [
            'top' => '0',
            'left' => 0,
            'width' => 1000,
            'height' => 1000
        ];
        try {
            new BlogImage(new Blog(), 1, $params);
        } catch (Exception $e) {
            $this->assertEquals('Blog image location is required', $e->getMessage());
        }
    }

    public function testBlankLocation() {
        $params = [
            'top' => '0',
            'left' => 0,
            'width' => 1000,
            'height' => 1000,
            'location' => ''
        ];
        try {
            new BlogImage(new Blog(), 1, $params);
        } catch (Exception $e) {
            $this->assertEquals('Blog image location can not be blank', $e->getMessage());
        }
    }

    public function testCreateNoPermissions() {
        $params = [
            'top' => '0',
            'left' => 0,
            'width' => 1000,
            'height' => 1000,
            'location' => '../tmp/sample1.jpg'
        ];
        $blogText = new BlogImage(new Blog(), 1, $params);
        $blogText->setBlog(Blog::withId(899));
        try {
            $blogText->create();
        } catch (Exception $e) {
            $this->assertEquals('User not authorized to create blog content', $e->getMessage());
        }
    }

    public function testCreate() {
        try {
            $oldmask = umask(0);
            mkdir(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/tmp', 0777, true);
            copy(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/tmp/sample.jpg');
            chmod(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/tmp/sample.jpg', 0777);
            umask($oldmask);
            $params = [
                'top' => '0',
                'left' => 0,
                'width' => 1000,
                'height' => 1000,
                'location' => '../tmp/sample.jpg'
            ];
            $blogText = new BlogImage(new Blog(), 1, $params);
            $blogText->setBlog(Blog::withId(899));
            $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
            $blogText->create();
            $blogDetails = $this->sql->getRows("SELECT * FROM blog_images WHERE blog = 899");
            $this->assertEquals(1, sizeof($blogDetails));
            $this->assertEquals(899, $blogDetails[0]['blog']);
            $this->assertEquals(1, $blogDetails[0]['contentGroup']);
            $this->assertEquals('posts/2030/01/01/sample.jpg', $blogDetails[0]['location']);
            $this->assertEquals(0, $blogDetails[0]['left']);
            $this->assertEquals(0, $blogDetails[0]['top']);
            $this->assertEquals(1000, $blogDetails[0]['height']);
            $this->assertEquals(1000, $blogDetails[0]['width']);
            $this->assertTrue(file_exists(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts/2030/01/01/sample.jpg'));
            $size = getimagesize(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts/2030/01/01/sample.jpg');
            $this->assertEquals(1000, $size[0]);
            $this->assertEquals(750, $size[1]);
        } finally {
            $this->sql->executeStatement("DELETE FROM blog_images WHERE blog = 899");
            system("rm -rf " . escapeshellarg(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/blog/posts'));
            system("rm -rf " . escapeshellarg(dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'public/tmp'));
            unset($_SESSION ['hash']);
        }
    }
}