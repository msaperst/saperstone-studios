<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";

class CreateBlogTagTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
    }

    public function tearDown() {
        $this->http = NULL;
    }

    public function testNotLoggedIn() {
        $response;
        try {
            $response = $this->http->request('POST', 'api/create-blog-tag.php');
        } catch ( GuzzleHttp\Exception\ClientException $e ) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody() );
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '5510b5e6fffd897c234cafe499f76146'
                ], 'localhost');
        $response;
        try {
            $response = $this->http->request('POST', 'api/create-blog-tag.php', [
                    'cookies' => $cookieJar
            ]);
        } catch ( GuzzleHttp\Exception\ClientException $e ) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody() );
        }
    }

    public function testNoTag() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/create-blog-tag.php', [
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog tag is required", $response->getBody() );
    }

    public function testBlankTag() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/create-blog-tag.php', [
                'form_params' => [
                   'tag' => ''
               ],
               'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Blog tag can not be blank", $response->getBody() );
    }

    public function testDuplicateTag() {
        $tagId = $this->sql->executeStatement( "INSERT INTO tags ( tag ) VALUES ('crazyTestTag');" );
        try {
            $cookieJar = CookieJar::fromArray([
                        'hash' => '1d7505e7f434a7713e84ba399e937191'
                    ], 'localhost');
            $response = $this->http->request('POST', 'api/create-blog-tag.php', [
                    'form_params' => [
                       'tag' => 'crazyTestTag'
                   ],
                   'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $this->assertEquals("Blog tag already exists", $response->getBody() );
        } finally {
            $this->sql->executeStatement( "DELETE FROM `tags` WHERE `tags`.`id` = $tagId;" );
            $count = $this->sql->getRow( "SELECT MAX(`id`) AS `count` FROM `tags`;")['count'];
            $count++;
            $this->sql->executeStatement( "ALTER TABLE `tags` AUTO_INCREMENT = $count;" );
        }
    }

    public function testTag() {
        $tagId;
        try {
            $cookieJar = CookieJar::fromArray([
                        'hash' => '1d7505e7f434a7713e84ba399e937191'
                    ], 'localhost');
            $response = $this->http->request('POST', 'api/create-blog-tag.php', [
                    'form_params' => [
                       'tag' => 'crazyTestTag'
                   ],
                   'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $tagId = $response->getBody();
            $tagDetails = $this->sql->getRow( "SELECT * FROM `tags` WHERE `tags`.`id` = $tagId;" );
            $this->assertEquals( $tagId, $tagDetails['id'] );
            $this->assertEquals( 'crazyTestTag', $tagDetails['tag'] );
        } finally {
            $this->sql->executeStatement( "DELETE FROM `tags` WHERE `tags`.`id` = $tagId;" );
            $count = $this->sql->getRow( "SELECT MAX(`id`) AS `count` FROM `tags`;")['count'];
            $count++;
            $this->sql->executeStatement( "ALTER TABLE `tags` AUTO_INCREMENT = $count;" );
        }
    }
}
?>