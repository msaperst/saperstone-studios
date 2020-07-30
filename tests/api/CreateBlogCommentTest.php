<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";

class CreateBlogCommentTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement( "INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('999', 'Sample Blog', CURRENT_TIMESTAMP, '', 0)" );
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement( "DELETE FROM `blog_details` WHERE `blog_details`.`id` = 999;" );
        $this->sql->executeStatement( "DELETE FROM `blog_comments` WHERE `blog_comments`.`blog` = 999;" );
        $count = $this->sql->getRow( "SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
        $count++;
        $this->sql->executeStatement( "ALTER TABLE `blog_details` AUTO_INCREMENT = $count;" );
        $count = $this->sql->getRow( "SELECT MAX(`id`) AS `count` FROM `blog_comments`;")['count'];
        $count++;
        $this->sql->executeStatement( "ALTER TABLE `blog_comments` AUTO_INCREMENT = $count;" );
    }

    public function testNoBlogId() {
        $response = $this->http->request('POST', 'api/create-blog-comment.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id is required", $response->getBody());
    }

    public function testBlankBlogId() {
        $response = $this->http->request('POST', 'api/create-blog-comment.php', [
                'form_params' => [
                    'post' => ''
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id can not be blank", $response->getBody());
    }

    public function testLetterBlogId() {
        $response = $this->http->request('POST', 'api/create-blog-comment.php', [
                'form_params' => [
                    'post' => 'a'
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id does not match any blogs", $response->getBody());
    }

    public function testBadBlogId() {
        $response = $this->http->request('POST', 'api/create-blog-comment.php', [
                'form_params' => [
                    'post' => 9999
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id does not match any blogs", $response->getBody());
    }

    public function testNoMessage() {
        $response = $this->http->request('POST', 'api/create-blog-comment.php', [
                'form_params' => [
                    'post' => 999
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Message is required", $response->getBody());
    }

    public function testBlankMessage() {
        $response = $this->http->request('POST', 'api/create-blog-comment.php', [
                'form_params' => [
                    'post' => 999,
                    'message' => ''
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Message can not be blank", $response->getBody());
    }

    public function testLoggedInUser() {    //no other data
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/create-blog-comment.php', [
                'form_params' => [
                    'post' => 999,
                    'message' => 'I love your post!'
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $commentId = $response->getBody();
        $this->assertEquals( 1, preg_match( "/^[\d]+$/", $commentId ) );
        $blogComment = $this->sql->getRow( "SELECT * FROM `blog_comments` WHERE `blog_comments`.`id` = $commentId;" );
        $this->assertEquals( $commentId, $blogComment['id'] );
        $this->assertEquals( 999, $blogComment['blog'] );
        $this->assertEquals( 1, $blogComment['user'] );
        $this->assertEquals( '', $blogComment['name'] );
        $this->assertEquals( date("Y-m-d H:i:s"), $blogComment['date'] );
        $this->assertTrue( filter_var( $blogComment['ip'], FILTER_VALIDATE_IP ) !== false );
        $this->assertEquals( '', $blogComment['email'] );
        $this->assertEquals( 'I love your post!', $blogComment['comment'] );
    }

    public function testAnonymousUser() {  //all data
        $response = $this->http->request('POST', 'api/create-blog-comment.php', [
                'form_params' => [
                    'post' => 999,
                    'message' => 'I love your post!',
                    'name' => 'MaxMaxMaxMax',
                    'email' => '123@example.com'
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $commentId = $response->getBody();
        $this->assertEquals( 1, preg_match( "/^[\d]+$/", $commentId ) );
        $blogComment = $this->sql->getRow( "SELECT * FROM `blog_comments` WHERE `blog_comments`.`id` = $commentId;" );
        $this->assertEquals( $commentId, $blogComment['id'] );
        $this->assertEquals( 999, $blogComment['blog'] );
        $this->assertNull( $blogComment['user'] );
        $this->assertEquals( 'MaxMaxMaxMax', $blogComment['name'] );
        $this->assertEquals( date("Y-m-d H:i:s"), $blogComment['date'] );
        $this->assertTrue( filter_var( $blogComment['ip'], FILTER_VALIDATE_IP ) !== false );
        $this->assertEquals( '123@example.com', $blogComment['email'] );
        $this->assertEquals( 'I love your post!', $blogComment['comment'] );
    }
}
?>