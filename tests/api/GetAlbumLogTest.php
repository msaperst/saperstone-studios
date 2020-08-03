<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";

class GetAlbumLogTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement( "INSERT INTO `albums` (`id`, `name`, `description`, `location`, `owner`, `code`) VALUES ('999', 'sample-album', 'sample album for testing', 'sample', 4, '123');" );
        $this->sql->executeStatement( "INSERT INTO `user_logs` (`user`, `time`, `action`, `what`, `album`) VALUES ('0', '2020-01-01 12:00:00', 'some action', '0', 999);" );
        $this->sql->executeStatement( "INSERT INTO `user_logs` (`user`, `time`, `action`, `what`, `album`) VALUES ('1', '2020-01-01 13:00:00', 'some other action', 'something', 999);" );
        $this->sql->executeStatement( "INSERT INTO `user_logs` (`user`, `time`, `action`, `what`) VALUES ('1', '2020-01-01 10:00:00', '123456', '');" );
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement( "DELETE FROM `albums` WHERE `albums`.`id` = 999;" );
        $this->sql->executeStatement( "DELETE FROM `user_logs` WHERE `user_logs`.`album` = 999;" );
        $this->sql->executeStatement( "DELETE FROM `user_logs` WHERE `user_logs`.`action` = '123456';" );
        $count = $this->sql->getRow( "SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement( "ALTER TABLE `albums` AUTO_INCREMENT = $count;" );
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        $response;
        try {
            $response = $this->http->request('POST', 'api/get-album-log.php');
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
            $response = $this->http->request('POST', 'api/get-album-log.php', [
                    'cookies' => $cookieJar
            ]);
        } catch ( GuzzleHttp\Exception\ClientException $e ) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody() );
        }
    }
    
    public function testNoAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-log.php', [
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", (string) $response->getBody());
    }

    public function testBlankAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-log.php', [
                'query' => [
                    'id' => ''
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", (string) $response->getBody());
    }

    public function testLetterAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-log.php', [
                'query' => [
                    'id' => 'a'
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string) $response->getBody());
    }

    public function testBadAlbumId() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-log.php', [
                'query' => [
                    'id' => 9999
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string) $response->getBody());
    }

    public function testAlbumResults() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('GET', 'api/get-album-log.php', [
                'query' => [
                    'id' => 999
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $albumInfo = json_decode($response->getBody(), true);
        $this->assertEquals(2, sizeOf( $albumInfo ) );
        $this->assertEquals(0, $albumInfo[0]['user'] );
        $this->assertEquals('2020-01-01 12:00:00', $albumInfo[0]['time'] );
        $this->assertEquals('some action', $albumInfo[0]['action'] );
        $this->assertEquals(0, $albumInfo[0]['what'] );
        $this->assertEquals(999, $albumInfo[0]['album'] );
        $this->assertEquals(1, $albumInfo[1]['user'] );
        $this->assertEquals('2020-01-01 13:00:00', $albumInfo[1]['time'] );
        $this->assertEquals('some other action', $albumInfo[1]['action'] );
        $this->assertEquals('something', $albumInfo[1]['what'] );
        $this->assertEquals(999, $albumInfo[1]['album'] );
    }
}
?>