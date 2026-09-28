<?php

namespace ui\http;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'HttpTestBase.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

use Sql;

class BlogPagesHttpTest extends HttpTestBase {
    protected function tearDown(): void {
        $sql = new Sql();
        try {
            $sql->executeStatement('DELETE FROM blog_images WHERE blog = 999');
            $sql->executeStatement('DELETE FROM blog_tags WHERE blog = 999');
            $sql->executeStatement('DELETE FROM blog_texts WHERE blog = 999');
            $sql->executeStatement('DELETE FROM blog_details WHERE id = 999');
        } finally {
            $sql->disconnect();
        }
    }

    /**
     * @dataProvider publicBlogPageProvider
     */
    public function testPublicBlogPage(string $path, string $heading): void {
        $this->assertPage($this->get($path), $heading);
    }

    public static function publicBlogPageProvider(): array {
        return [
            'categories' => ['blog/categories.php', 'Blog Categories'],
            'index' => ['blog/index.php', 'Blog Posts'],
            'posts' => ['blog/posts.php', 'Recent Blog Posts'],
            'single category' => ['blog/category.php?t=2', '6 Month Session Blog Posts'],
            'multiple categories' => ['blog/category.php?t=2,3', '6 Month Session and Babies Blog Posts'],
            'search with no results' => ['blog/search.php?s=xlkjliu', 'Blog Posts'],
        ];
    }

    /**
     * @dataProvider invalidBlogPageProvider
     */
    public function testInvalidBlogPageReturnsNotFound(string $path): void {
        $response = $this->get($path);
        self::assertSame(404, $response->getStatusCode());
        self::assertSame('404 Not Found', $this->text($response, '//h1'));
    }

    public static function invalidBlogPageProvider(): array {
        return [
            'category missing id' => ['blog/category.php'],
            'category blank id' => ['blog/category.php?t='],
            'category unknown id' => ['blog/category.php?t=999'],
            'post missing id' => ['blog/post.php'],
            'post blank id' => ['blog/post.php?p='],
            'post unknown id' => ['blog/post.php?p=9999'],
            'search missing term' => ['blog/search.php'],
            'search blank term' => ['blog/search.php?s='],
        ];
    }

    /**
     * @dataProvider adminBlogPageProvider
     */
    public function testAdminBlogPageRejectsAnonymousUser(string $path): void {
        $response = $this->get($path);
        self::assertSame(401, $response->getStatusCode());
        self::assertSame('401 Unauthorized', $this->text($response, '//h1'));
    }

    public static function adminBlogPageProvider(): array {
        return [
            'manage' => ['blog/manage.php'],
            'new' => ['blog/new.php'],
        ];
    }

    public function testAdminManagePage(): void {
        $this->adminLogin();
        $this->assertPage($this->get('blog/manage.php'), 'Manage Blog Posts');
    }

    public function testAdminNewPostPage(): void {
        $this->adminLogin();
        $response = $this->get('blog/new.php');
        $this->assertPage($response, 'Create Post');
        self::assertSame('../tmp', $this->attribute($response, "//*[@id='post']", 'post-location'));
        self::assertSame('', $this->attribute($response, "//*[@id='post-title-input']", 'value'));
        $renderedDate = $this->attribute($response, "//*[@id='post-date-input']", 'value');
        self::assertMatchesRegularExpression('/^\\d{4}-\\d{2}-\\d{2}$/', $renderedDate);
        self::assertLessThanOrEqual(1, abs((int) ((strtotime($renderedDate) - strtotime(date('Y-m-d'))) / 86400)));
        self::assertSame(0, $this->elementCount($response, "//*[@id='update-post']"));
    }

    public function testAdminEditUnknownPostReturnsNotFound(): void {
        $this->adminLogin();
        $response = $this->get('blog/new.php?p=9999');
        self::assertSame(404, $response->getStatusCode());
        self::assertSame('404 Not Found', $this->text($response, '//h1'));
    }

    public function testAdminEditPostUsesStoredPostMetadata(): void {
        $this->insertBlog(false);
        $this->adminLogin();

        $response = $this->get('blog/new.php?p=999');
        $this->assertPage($response, 'Edit Post');
        self::assertSame('posts/2031/01/01', $this->attribute($response, "//*[@id='post']", 'post-location'));
        self::assertSame('999', $this->attribute($response, "//*[@id='post']", 'post-id'));
        self::assertSame('Sample Blog', $this->attribute($response, "//*[@id='post-title-input']", 'value'));
        self::assertSame('2031-01-01', $this->attribute($response, "//*[@id='post-date-input']", 'value'));
    }

    public function testActivePostRendersForGuestWithAnonymousCommentFields(): void {
        $this->insertBlog(true);

        $response = $this->get('blog/post.php?p=999');
        $this->assertPage($response, 'Recent Blog Posts');
        self::assertSame(0, $this->elementCount($response, "//*[@id='edit-post-btn']"));
        self::assertSame('', $this->attribute($response, "//*[@id='post-comment-user']", 'value'));
        self::assertSame('', $this->attribute($response, "//*[@id='post-comment-name']", 'value'));
        self::assertSame('', $this->attribute($response, "//*[@id='post-comment-email']", 'value'));
        self::assertSame('999', $this->attribute($response, "//*[@id='post-comment-submit']", 'post-id'));
        self::assertSame('999', $this->attribute($response, "//*[@id='blog-page-config']", 'data-post'));
    }

    public function testActivePostRendersAdminControlsAndIdentity(): void {
        $this->insertBlog(true);
        $this->adminLogin();

        $response = $this->get('blog/post.php?p=999');
        $this->assertPage($response, 'Recent Blog Posts');
        self::assertSame(1, $this->elementCount($response, "//*[@id='edit-post-btn']"));
        self::assertSame('1', $this->attribute($response, "//*[@id='post-comment-user']", 'value'));
        self::assertSame('Max Saperstone', $this->attribute($response, "//*[@id='post-comment-name']", 'value'));
        self::assertSame('msaperst@gmail.com', $this->attribute($response, "//*[@id='post-comment-email']", 'value'));
    }

    public function testSearchReportsMatchingPostCountToJavascriptLoader(): void {
        $this->insertBlog(true);

        $response = $this->get('blog/search.php?s=sample');
        $this->assertPage($response, 'Blog Posts');
        self::assertSame('1', $this->attribute($response, "//*[@id='blog-page-config']", 'data-total'));
        self::assertSame('sample', $this->attribute($response, "//*[@id='blog-page-config']", 'data-search'));
    }

    private function insertBlog(bool $active): void {
        $sql = new Sql();
        try {
            $sql->executeStatement("INSERT INTO blog_details (id, title, date, preview, offset, active) VALUES (999, 'Sample Blog', '2031-01-01', '', 0, ?)", [$active ? 1 : 0]);
            $sql->executeStatement("INSERT INTO blog_images (blog, contentGroup, location, width, height, `left`, `top`) VALUES (999, 1, 'posts/2031/01/01/sample.jpg', 300, 400, 0, 0)");
            $sql->executeStatement("INSERT INTO blog_tags (blog, tag) VALUES (999, 29)");
            $sql->executeStatement("INSERT INTO blog_texts (blog, contentGroup, text) VALUES (999, 2, 'Some blog text')");
        } finally {
            $sql->disconnect();
        }
    }
}
