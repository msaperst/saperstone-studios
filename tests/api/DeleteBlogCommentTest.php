<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";

class DeleteBlogCommentTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement( "INSERT INTO `blog_comments` (`id`, `blog`, `name`, `date`, `ip`, `email`, `comment`) VALUES ('998', '999', 'MaxMaxMax', CURRENT_TIMESTAMP, '127.0.0.1', 'msaperst+sstest@gmail.com', 'this is an awesome post')" );
        $this->sql->executeStatement( "INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES ('999', '999', 4, 'MaxMaxMax', CURRENT_TIMESTAMP, '127.0.0.1', 'msaperst+sstest@gmail.com', 'this is an awesome post')" );
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement( "DELETE FROM `blog_comments` WHERE `blog_comments`.`blog` = 999;" );
        $count = $this->sql->getRow( "SELECT MAX(`id`) AS `count` FROM `blog_comments`;")['count'];
        $count++;
        $this->sql->executeStatement( "ALTER TABLE `blog_comments` AUTO_INCREMENT = $count;" );
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        $response;
        try {
            $response = $this->http->request('POST', 'api/delete-blog-comment.php');
        } catch ( GuzzleHttp\Exception\ClientException $e ) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody() );
        }
    }

    public function testNoComment() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-blog-comment.php', [
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Comment id is required", $response->getBody());
    }

    public function testBlankComment() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-blog-comment.php', [
                'form_params' => [
                    'comment' => ''
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Comment id can not be blank", $response->getBody());
    }

    public function testLetterComment() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-blog-comment.php', [
                'form_params' => [
                    'comment' => 'a'
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Comment id does not match any comments", $response->getBody());
    }

    public function testBadComment() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-blog-comment.php', [
                'form_params' => [
                    'comment' => 9999
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Comment id does not match any comments", $response->getBody());
    }

    public function testUploaderCantDeleteOtherComment() {
        $response;
        try {
            $cookieJar = CookieJar::fromArray([
                        'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                    ], 'localhost');
            $response = $this->http->request('POST', 'api/delete-blog-comment.php', [
                    'form_params' => [
                        'comment' => 998
                    ],
                    'cookies' => $cookieJar
            ]);
        } catch ( GuzzleHttp\Exception\ClientException $e ) {
            $this->assertEquals(403, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody() );
            $this->assertEquals( 1, $this->sql->getRowCount( "SELECT * FROM `blog_comments` WHERE `blog_comments`.`id` = 998;" ) );
        }
    }

    public function testAdminCanDeleteAnyComment() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-blog-comment.php', [
                'form_params' => [
                    'comment' => 998
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", $response->getBody() );
        $this->assertEquals( 0, $this->sql->getRowCount( "SELECT * FROM `blog_comments` WHERE `blog_comments`.`id` = 998;" ) );
    }

    public function testUploaderCanDeleteOwnComment() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-blog-comment.php', [
                'form_params' => [
                    'comment' => 999
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", $response->getBody() );
        $this->assertEquals( 0, $this->sql->getRowCount( "SELECT * FROM `blog_comments` WHERE `blog_comments`.`id` = 999;" ) );
    }
}
?>