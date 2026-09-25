<?php

namespace api;

use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class BlogVisibilityTest extends TestCase {
    private ApiTestClient $http;
    private Sql $sql;

    public function setUp(): void {
        $this->http = new ApiTestClient([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/',
        ]);
        $this->sql = new Sql();

        $this->sql->executeStatement(
            "INSERT INTO blog_details (id, title, date, preview, offset, active)
             VALUES
             (985, 'Loader Active Both', '2098-01-01', '', 0, 1),
             (986, 'Loader Active One', '2097-01-01', '', 0, 1),
             (987, 'Loader Inactive Both', '2099-01-01', '', 0, 0)"
        );

        $this->sql->executeStatement(
            "INSERT INTO blog_tags (blog, tag)
             VALUES (985, 29), (985, 30), (986, 29), (987, 29), (987, 30)"
        );

        $this->sql->executeStatement(
            "INSERT INTO blog_texts (blog, contentGroup, text)
             VALUES (987, 1, 'inactive-hidden-loader-search-987')"
        );
    }

    public function tearDown(): void {
        $this->sql->executeStatement("DELETE FROM blog_texts WHERE blog IN (985, 986, 987)");
        $this->sql->executeStatement("DELETE FROM blog_tags WHERE blog IN (985, 986, 987)");
        $this->sql->executeStatement("DELETE FROM blog_details WHERE id IN (985, 986, 987)");
        $this->sql->disconnect();
    }

    public function testBlogIndexLoaderTotalMatchesActivePosts(): void {
        $expected = (int)$this->sql->getRow(
            "SELECT COUNT(*) AS count FROM blog_details WHERE active = 1"
        )['count'];

        $response = $this->http->request('GET', 'blog/index.php');
        $body = (string)$response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(1, preg_match('/new PostsFull\\(\\s*(\\d+)\\s*\\)/', $body, $match));
        $this->assertSame($expected, (int)$match[1]);
    }

    public function testCategoryLoaderTotalMatchesActivePostsWithAllTags(): void {
        $expected = (int)$this->sql->getRow(
            "SELECT COUNT(*) AS count
             FROM blog_details AS details
             WHERE details.active = 1
               AND EXISTS (
                   SELECT 1 FROM blog_tags AS tag29
                   WHERE tag29.blog = details.id AND tag29.tag = 29
               )
               AND EXISTS (
                   SELECT 1 FROM blog_tags AS tag30
                   WHERE tag30.blog = details.id AND tag30.tag = 30
               )"
        )['count'];

        $response = $this->http->request('GET', 'blog/category.php', [
            'query' => ['t' => '29,30'],
        ]);
        $body = (string)$response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            1,
            preg_match('/new PostsFull\\(\\s*(\\d+)\\s*,\\s*\\[29,30\\]/', $body, $match)
        );
        $this->assertSame($expected, (int)$match[1]);
    }

    public function testTaggedFullApiExcludesInactivePosts(): void {
        $response = $this->http->request('GET', 'api/get-blogs-full.php', [
            'query' => [
                'tag' => [29, 30],
                'start' => 0,
            ],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $post = json_decode((string)$response->getBody(), true);
        $this->assertSame(985, $post['id']);
        $this->assertSame(1, $post['active']);
    }

    public function testSearchApiExcludesInactiveTextMatches(): void {
        $response = $this->http->request('GET', 'api/get-blogs-search-details.php', [
            'query' => ['searchTerm' => 'inactive-hidden-loader-search-987'],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], json_decode((string)$response->getBody(), true));
    }

    public function testSearchPageTotalExcludesInactiveTextMatches(): void {
        $response = $this->http->request('GET', 'blog/search.php', [
            'query' => ['s' => 'inactive-hidden-loader-search-987'],
        ]);
        $body = (string)$response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            1,
            preg_match('/new Posts\\(\\s*3\\s*,\\s*(\\d+)\\s*,/', $body, $match)
        );
        $this->assertSame(0, (int)$match[1]);
    }
}
