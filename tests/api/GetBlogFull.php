<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";

class GetBlogFullTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement( "INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('998', 'Sample Blog', '2031-01-01', '', 0)" );
        $this->sql->executeStatement( "INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES (998, 999, NULL, 'Anna', '2012-10-31 09:56:47', '68.98.132.164', 'annad@annadbruce.com', 'hehehehehe this rules!')" );
        $this->sql->executeStatement( "INSERT INTO `blog_comments` (`id`, `blog`, `user`, `name`, `date`, `ip`, `email`, `comment`) VALUES (999, 999, 4, 'Uploader', '2012-10-31 13:56:47', '192.168.1.2', 'msaperst@gmail.com', 'awesome post')" );
        $this->sql->executeStatement( "INSERT INTO `blog_details` (`id`, `title`, `date`, `preview`, `offset`) VALUES ('999', 'Sample Blog', '2031-01-01', '', 0)" );
        $this->sql->executeStatement( "INSERT INTO `blog_images` (`blog`, `contentGroup`, `location`, `width`, `height`, `left`, `top`) VALUES ('999', '1', 'posts/2031/01/01/sample.jpg', 300, 400, 0, 0)" );
        $this->sql->executeStatement( "INSERT INTO `blog_tags` (`blog`, `tag`) VALUES ('999', 29)" );
        $this->sql->executeStatement( "INSERT INTO `blog_texts` (`blog`, `contentGroup`, `text`) VALUES ('999', '2', 'Some blog text')" );
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement( "DELETE FROM `blog_details` WHERE `blog_details`.`id` = 998;" );
        $this->sql->executeStatement( "DELETE FROM `blog_details` WHERE `blog_details`.`id` = 999;" );
        $this->sql->executeStatement( "DELETE FROM `blog_images` WHERE `blog_images`.`blog` = 999;" );
        $this->sql->executeStatement( "DELETE FROM `blog_tags` WHERE `blog_tags`.`blog` = 999;" );
        $this->sql->executeStatement( "DELETE FROM `blog_texts` WHERE `blog_texts`.`blog` = 999;" );
        $this->sql->executeStatement( "DELETE FROM `blog_comments` WHERE `blog_comments`.`blog` = 999;" );
        $count = $this->sql->getRow( "SELECT MAX(`id`) AS `count` FROM `blog_details`;")['count'];
        $count++;
        $this->sql->executeStatement( "ALTER TABLE `blog_details` AUTO_INCREMENT = $count;" );
        $count = $this->sql->getRow( "SELECT MAX(`id`) AS `count` FROM `blog_comments`;")['count'];
        $count++;
        $this->sql->executeStatement( "ALTER TABLE `blog_comments` AUTO_INCREMENT = $count;" );
        $this->sql->disconnect();
    }
    
    public function testNoBlog() {
        $response = $this->http->request('GET', 'api/get-blog-full.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id is required", (string) $response->getBody());
    }

    public function testBlankBlog() {
        $response = $this->http->request('GET', 'api/get-blog-full.php', [
                'query' => [
                    'post' => ''
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id can not be blank", (string) $response->getBody());
    }

    public function testLetterBlog() {
        $response = $this->http->request('GET', 'api/get-blog-full.php', [
                'query' => [
                    'post' => 'a'
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id does not match any blogs", (string) $response->getBody());
    }

    public function testBadBlog() {
        $response = $this->http->request('GET', 'api/get-blog-full.php', [
                'query' => [
                    'post' => 9999
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog id does not match any blogs", (string) $response->getBody());
    }

    public function testGetBlogFullSimple() {
        $response = $this->http->request('GET', 'api/get-blog-full.php', [
                'query' => [
                    'post' => 998
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $blogDetails = json_decode($response->getBody(), true);
        $this->assertEquals("998", $blogDetails['id']);
        $this->assertEquals("Sample Blog", $blogDetails['title']);
        $this->assertNull($blogDetails['safe_title']);
        $this->assertEquals("January 1st, 2031", $blogDetails['date']);
        $this->assertEquals("", $blogDetails['preview']);
        $this->assertEquals(0, $blogDetails['offset']);
        $this->assertEquals(0, $blogDetails['active']);
        $this->assertEquals(0, $blogDetails['twitter']);
        $this->assertEquals( array(), $blogDetails['tags']);
        $this->assertEquals( array(), $blogDetails['comments']);
    }

    public function testGetBlogFullComplex() {
        $response = $this->http->request('GET', 'api/get-blog-full.php', [
                'query' => [
                    'post' => 999
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $blogDetails = json_decode($response->getBody(), true);
        $this->assertEquals("999", $blogDetails['id']);
        $this->assertEquals("Sample Blog", $blogDetails['title']);
        $this->assertNull($blogDetails['safe_title']);
        $this->assertEquals("January 1st, 2031", $blogDetails['date']);
        $this->assertEquals("", $blogDetails['preview']);
        $this->assertEquals(0, $blogDetails['offset']);
        $this->assertEquals(0, $blogDetails['active']);
        $this->assertEquals(0, $blogDetails['twitter']);
        $this->assertEquals( 2, sizeOf( $blogDetails['content'] ) );
        $this->assertEquals( 1, sizeOf( $blogDetails['content'][1] ) );
        $this->assertEquals( 999, $blogDetails['content'][1][0]['blog'] );
        $this->assertEquals( 1, $blogDetails['content'][1][0]['contentGroup'] );
        $this->assertEquals( 'posts/2031/01/01/sample.jpg', $blogDetails['content'][1][0]['location'] );
        $this->assertEquals( 300, $blogDetails['content'][1][0]['width'] );
        $this->assertEquals( 400, $blogDetails['content'][1][0]['height'] );
        $this->assertEquals( 0, $blogDetails['content'][1][0]['left'] );
        $this->assertEquals( 0, $blogDetails['content'][1][0]['top'] );
        $this->assertEquals( 1, sizeOf( $blogDetails['content'][2] ) );
        $this->assertEquals( 999, $blogDetails['content'][2][0]['blog'] );
        $this->assertEquals( 2, $blogDetails['content'][2][0]['contentGroup'] );
        $this->assertEquals( 'Some blog text', $blogDetails['content'][2][0]['text'] );
        $this->assertEquals( 1, sizeOf( $blogDetails['tags'] ) );
        $this->assertEquals( 29, $blogDetails['tags'][0]['id'] );
        $this->assertEquals( 'Tea Ceremony', $blogDetails['tags'][0]['tag'] );
        $this->assertEquals( 2, sizeOf( $blogDetails['comments'] ) );
        $this->assertEquals( 999, $blogDetails['comments'][0]['id'] );
        $this->assertEquals( 999, $blogDetails['comments'][0]['blog'] );
        $this->assertEquals( 'awesome post', $blogDetails['comments'][0]['comment'] );
        $this->assertEquals( '2012-10-31 13:56:47', $blogDetails['comments'][0]['date'] );
        $this->assertEquals( 'msaperst@gmail.com', $blogDetails['comments'][0]['email'] );
        $this->assertEquals( '192.168.1.2', $blogDetails['comments'][0]['ip'] );
        $this->assertEquals( 'Uploader', $blogDetails['comments'][0]['name'] );
        $this->assertEquals( 4, $blogDetails['comments'][0]['user'] );
        $this->assertEquals( 998, $blogDetails['comments'][1]['id'] );
        $this->assertEquals( 999, $blogDetails['comments'][1]['blog'] );
        $this->assertEquals( 'hehehehehe this rules!', $blogDetails['comments'][1]['comment'] );
        $this->assertEquals( '2012-10-31 09:56:47', $blogDetails['comments'][1]['date'] );
        $this->assertEquals( 'annad@annadbruce.com', $blogDetails['comments'][1]['email'] );
        $this->assertEquals( '68.98.132.164', $blogDetails['comments'][1]['ip'] );
        $this->assertEquals( 'Anna', $blogDetails['comments'][1]['name'] );
        $this->assertNull( $blogDetails['comments'][1]['user'] );
    }

    public function testGetBlogFullUser() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-blog-full.php', [
                'query' => [
                    'post' => 999
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $blogDetails = json_decode($response->getBody(), true);
        $this->assertEquals("999", $blogDetails['id']);
        $this->assertEquals("Sample Blog", $blogDetails['title']);
        $this->assertNull($blogDetails['safe_title']);
        $this->assertEquals("January 1st, 2031", $blogDetails['date']);
        $this->assertEquals("", $blogDetails['preview']);
        $this->assertEquals(0, $blogDetails['offset']);
        $this->assertEquals(0, $blogDetails['active']);
        $this->assertEquals(0, $blogDetails['twitter']);
        $this->assertEquals( 2, sizeOf( $blogDetails['content'] ) );
        $this->assertEquals( 1, sizeOf( $blogDetails['content'][1] ) );
        $this->assertEquals( 999, $blogDetails['content'][1][0]['blog'] );
        $this->assertEquals( 1, $blogDetails['content'][1][0]['contentGroup'] );
        $this->assertEquals( 'posts/2031/01/01/sample.jpg', $blogDetails['content'][1][0]['location'] );
        $this->assertEquals( 300, $blogDetails['content'][1][0]['width'] );
        $this->assertEquals( 400, $blogDetails['content'][1][0]['height'] );
        $this->assertEquals( 0, $blogDetails['content'][1][0]['left'] );
        $this->assertEquals( 0, $blogDetails['content'][1][0]['top'] );
        $this->assertEquals( 1, sizeOf( $blogDetails['content'][2] ) );
        $this->assertEquals( 999, $blogDetails['content'][2][0]['blog'] );
        $this->assertEquals( 2, $blogDetails['content'][2][0]['contentGroup'] );
        $this->assertEquals( 'Some blog text', $blogDetails['content'][2][0]['text'] );
        $this->assertEquals( 1, sizeOf( $blogDetails['tags'] ) );
        $this->assertEquals( 29, $blogDetails['tags'][0]['id'] );
        $this->assertEquals( 'Tea Ceremony', $blogDetails['tags'][0]['tag'] );
        $this->assertEquals( 2, sizeOf( $blogDetails['comments'] ) );
        $this->assertEquals( 999, $blogDetails['comments'][0]['id'] );
        $this->assertEquals( 999, $blogDetails['comments'][0]['blog'] );
        $this->assertEquals( 'awesome post', $blogDetails['comments'][0]['comment'] );
        $this->assertEquals( '2012-10-31 13:56:47', $blogDetails['comments'][0]['date'] );
        $this->assertEquals( true, $blogDetails['comments'][0]['delete'] );
        $this->assertEquals( 'msaperst@gmail.com', $blogDetails['comments'][0]['email'] );
        $this->assertEquals( '192.168.1.2', $blogDetails['comments'][0]['ip'] );
        $this->assertEquals( 'Uploader', $blogDetails['comments'][0]['name'] );
        $this->assertEquals( 4, $blogDetails['comments'][0]['user'] );
        $this->assertEquals( 998, $blogDetails['comments'][1]['id'] );
        $this->assertEquals( 999, $blogDetails['comments'][1]['blog'] );
        $this->assertEquals( 'hehehehehe this rules!', $blogDetails['comments'][1]['comment'] );
        $this->assertEquals( '2012-10-31 09:56:47', $blogDetails['comments'][1]['date'] );
        $this->assertEquals( 'annad@annadbruce.com', $blogDetails['comments'][1]['email'] );
        $this->assertEquals( '68.98.132.164', $blogDetails['comments'][1]['ip'] );
        $this->assertEquals( 'Anna', $blogDetails['comments'][1]['name'] );
        $this->assertNull( $blogDetails['comments'][1]['user'] );
    }

    public function testGetBlogFullAdmin() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-blog-full.php', [
                'query' => [
                    'post' => 999
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $blogDetails = json_decode($response->getBody(), true);
        $this->assertEquals("999", $blogDetails['id']);
        $this->assertEquals("Sample Blog", $blogDetails['title']);
        $this->assertNull($blogDetails['safe_title']);
        $this->assertEquals("January 1st, 2031", $blogDetails['date']);
        $this->assertEquals("", $blogDetails['preview']);
        $this->assertEquals(0, $blogDetails['offset']);
        $this->assertEquals(0, $blogDetails['active']);
        $this->assertEquals(0, $blogDetails['twitter']);
        $this->assertEquals( 2, sizeOf( $blogDetails['content'] ) );
        $this->assertEquals( 1, sizeOf( $blogDetails['content'][1] ) );
        $this->assertEquals( 999, $blogDetails['content'][1][0]['blog'] );
        $this->assertEquals( 1, $blogDetails['content'][1][0]['contentGroup'] );
        $this->assertEquals( 'posts/2031/01/01/sample.jpg', $blogDetails['content'][1][0]['location'] );
        $this->assertEquals( 300, $blogDetails['content'][1][0]['width'] );
        $this->assertEquals( 400, $blogDetails['content'][1][0]['height'] );
        $this->assertEquals( 0, $blogDetails['content'][1][0]['left'] );
        $this->assertEquals( 0, $blogDetails['content'][1][0]['top'] );
        $this->assertEquals( 1, sizeOf( $blogDetails['content'][2] ) );
        $this->assertEquals( 999, $blogDetails['content'][2][0]['blog'] );
        $this->assertEquals( 2, $blogDetails['content'][2][0]['contentGroup'] );
        $this->assertEquals( 'Some blog text', $blogDetails['content'][2][0]['text'] );
        $this->assertEquals( 1, sizeOf( $blogDetails['tags'] ) );
        $this->assertEquals( 29, $blogDetails['tags'][0]['id'] );
        $this->assertEquals( 'Tea Ceremony', $blogDetails['tags'][0]['tag'] );
        $this->assertEquals( 2, sizeOf( $blogDetails['comments'] ) );
        $this->assertEquals( 999, $blogDetails['comments'][0]['id'] );
        $this->assertEquals( 999, $blogDetails['comments'][0]['blog'] );
        $this->assertEquals( 'awesome post', $blogDetails['comments'][0]['comment'] );
        $this->assertEquals( '2012-10-31 13:56:47', $blogDetails['comments'][0]['date'] );
        $this->assertEquals( true, $blogDetails['comments'][0]['delete'] );
        $this->assertEquals( 'msaperst@gmail.com', $blogDetails['comments'][0]['email'] );
        $this->assertEquals( '192.168.1.2', $blogDetails['comments'][0]['ip'] );
        $this->assertEquals( 'Uploader', $blogDetails['comments'][0]['name'] );
        $this->assertEquals( 4, $blogDetails['comments'][0]['user'] );
        $this->assertEquals( 998, $blogDetails['comments'][1]['id'] );
        $this->assertEquals( 999, $blogDetails['comments'][1]['blog'] );
        $this->assertEquals( 'hehehehehe this rules!', $blogDetails['comments'][1]['comment'] );
        $this->assertEquals( '2012-10-31 09:56:47', $blogDetails['comments'][1]['date'] );
        $this->assertEquals( true, $blogDetails['comments'][1]['delete'] );
        $this->assertEquals( 'annad@annadbruce.com', $blogDetails['comments'][1]['email'] );
        $this->assertEquals( '68.98.132.164', $blogDetails['comments'][1]['ip'] );
        $this->assertEquals( 'Anna', $blogDetails['comments'][1]['name'] );
        $this->assertNull( $blogDetails['comments'][1]['user'] );
    }
}
?>