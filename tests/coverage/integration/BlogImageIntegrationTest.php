<?php

namespace coverage\integration;

use BadBlogException;
use BadBlogImageException;
use BadBlogTextException;
use BadCommentException;
use BadUserException;
use Blog;
use BlogImage;
use BlogImageException;
use PHPUnit\Framework\TestCase;
use Sql;
use SqlException;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class BlogImageIntegrationTest extends TestCase {
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
        $this->sql->executeStatement("INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('899', 'Sample Blog', '2031-01-01', 'posts/2030/01/01/preview_image-899.jpg', 0)");
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
        $this->sql->executeStatement("DELETE FROM blog_images WHERE blog = 899");
        system("rm -rf " . escapeshellarg(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/blog/posts'));
        system("rm -rf " . escapeshellarg(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/tmp'));

        $this->sql->disconnect();
    }

    public function testNulls() {
        $this->expectException(BadBlogImageException::class);
        $this->expectExceptionMessage('Blog content group is required');
        new BlogImage(new Blog(), null, null);
    }

    public function testNullGroup() {
        $this->expectException(BadBlogImageException::class);
        $this->expectExceptionMessage('Blog content group is required');
        new BlogImage(new Blog(), null, array());
    }

    public function testNoGroup() {
        $this->expectException(BadBlogImageException::class);
        $this->expectExceptionMessage('Blog content group can not be blank');
        new BlogImage(new Blog(), '', array());
    }

    public function testNoTop() {
        $this->expectException(BadBlogImageException::class);
        $this->expectExceptionMessage('Blog image top location is required');
        new BlogImage(new Blog(), 1, array());
    }

    public function testBlankTop() {
        $params = [
            'top' => ''
        ];
        $this->expectException(BadBlogImageException::class);
        $this->expectExceptionMessage('Blog image top location can not be blank');
        new BlogImage(new Blog(), 1, $params);
    }

    public function testNoLeft() {
        $params = [
            'top' => '0'
        ];
        $this->expectException(BadBlogImageException::class);
        $this->expectExceptionMessage('Blog image left location is required');
        new BlogImage(new Blog(), 1, $params);
    }

    public function testBlankLeft() {
        $params = [
            'top' => '0',
            'left' => ''
        ];
        $this->expectException(BadBlogImageException::class);
        $this->expectExceptionMessage('Blog image left location can not be blank');
        new BlogImage(new Blog(), 1, $params);
    }

    public function testNoWidth() {
        $params = [
            'top' => '0',
            'left' => 0
        ];
        $this->expectException(BadBlogImageException::class);
        $this->expectExceptionMessage('Blog image width is required');
        new BlogImage(new Blog(), 1, $params);
    }

    public function testBlankWidth() {
        $params = [
            'top' => '0',
            'left' => 0,
            'width' => ''
        ];
        $this->expectException(BadBlogImageException::class);
        $this->expectExceptionMessage('Blog image width can not be blank');
        new BlogImage(new Blog(), 1, $params);
    }

    public function testNoHeight() {
        $params = [
            'top' => '0',
            'left' => 0,
            'width' => 1000
        ];
        $this->expectException(BadBlogImageException::class);
        $this->expectExceptionMessage('Blog image height is required');
        new BlogImage(new Blog(), 1, $params);
    }

    public function testBlankHeight() {
        $params = [
            'top' => '0',
            'left' => 0,
            'width' => 1000,
            'height' => ''
        ];
        $this->expectException(BadBlogImageException::class);
        $this->expectExceptionMessage('Blog image height can not be blank');
        new BlogImage(new Blog(), 1, $params);
    }

    public function testNoLocation() {
        $params = [
            'top' => '0',
            'left' => 0,
            'width' => 1000,
            'height' => 1000
        ];
        $this->expectException(BadBlogImageException::class);
        $this->expectExceptionMessage('Blog image location is required');
        new BlogImage(new Blog(), 1, $params);
    }

    public function testBlankLocation() {
        $params = [
            'top' => '0',
            'left' => 0,
            'width' => 1000,
            'height' => 1000,
            'location' => ''
        ];
        $this->expectException(BadBlogImageException::class);
        $this->expectExceptionMessage('Blog image location can not be blank');
        new BlogImage(new Blog(), 1, $params);
    }

    /**
     * @throws BadBlogException
     * @throws BadBlogImageException
     * @throws BadBlogTextException
     * @throws BadCommentException
     * @throws BlogImageException
     * @throws SqlException
     * @throws BadUserException
     */
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
        $this->expectException(BlogImageException::class);
        $this->expectExceptionMessage('User not authorized to create blog content');
        $blogText->create();
    }

    /**
     * @throws BadBlogTextException
     * @throws SqlException
     * @throws BlogImageException
     * @throws BadUserException
     * @throws BadBlogImageException
     * @throws BadCommentException
     * @throws BadBlogException
     */
    public function testCreate() {
        $oldMask = umask(0);
        mkdir(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/tmp', 0777, true);
        copy(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/tmp/sample.jpg');
        chmod(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/tmp/sample.jpg', 0777);
        umask($oldMask);
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
        $this->assertTrue(file_exists(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/blog/posts/2030/01/01/sample.jpg'));
        $size = getimagesize(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/blog/posts/2030/01/01/sample.jpg');
        $this->assertEquals(1000, $size[0]);
        $this->assertEquals(750, $size[1]);
    }

    /**
     * @throws BadBlogImageException
     */
    public function testGetLocation() {
        $oldMask = umask(0);
        mkdir(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/tmp', 0777, true);
        copy(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'tests/resources/flower.jpeg', dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/tmp/sample.jpg');
        chmod(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public/tmp/sample.jpg', 0777);
        umask($oldMask);
        $params = [
            'top' => '0',
            'left' => 0,
            'width' => 1000,
            'height' => 1000,
            'location' => '../tmp/sample.jpg'
        ];
        $blogImage = new BlogImage(new Blog(), 1, $params);
        $this->assertEquals('../tmp/sample.jpg', $blogImage->getLocation());
    }
}