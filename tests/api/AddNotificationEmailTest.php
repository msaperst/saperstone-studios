<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";

class AddNotificationEmailTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement( "INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES ('999', 'sample-album', 'sample album for testing', '');" );
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement( "DELETE FROM `albums` WHERE `albums`.`id` = 999" );
        $this->sql->executeStatement( "DELETE FROM `notification_emails` WHERE `notification_emails`.`album` = 999" );
    }

    public function testNoAlbumId() {
        $response = $this->http->request('POST', 'api/add-notification-email.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", $response->getBody());
    }

    public function testBlankAlbumId() {
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
                'form_params' => [
                    'album' => ''
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", $response->getBody());
    }

    public function testLetterAlbumId() {
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
                'form_params' => [
                    'album' => 'a'
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", $response->getBody());
    }

    public function testBadAlbumId() {
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
                'form_params' => [
                    'album' => 9999
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", $response->getBody());
    }

    public function testNoEmail() {
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
                'form_params' => [
                    'album' => 999
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is required", $response->getBody());
    }

    public function testBlankEmail() {
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
                'form_params' => [
                    'album' => 999,
                    'email' => ''
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email can not be blank", $response->getBody());
    }

    public function testLoggedIn() {
        $cookieJar = CookieJar::fromArray([
                    'hash' => '1d7505e7f434a7713e84ba399e937191'
                ], 'localhost');
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
                'form_params' => [
                    'album' => 999,
                    'email' => 'msaperst@gmail.com'
                ],
                'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", $response->getBody());
        $rows = $this->sql->getRows( "SELECT * FROM `notification_emails` WHERE `notification_emails`.`album` = 999" );
        $this->assertEquals( 1, sizeOf( $rows ) );
        $this->assertEquals( 999, $rows[0]['album'] );
        $this->assertEquals( 1, $rows[0]['user'] );
        $this->assertEquals( 'msaperst@gmail.com', $rows[0]['email'] );
    }

    public function testNotLoggedIn() {
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
                'form_params' => [
                    'album' => 999,
                    'email' => 'msaperst@gmail.com'
                ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", $response->getBody());
        $rows = $this->sql->getRows( "SELECT * FROM `notification_emails` WHERE `notification_emails`.`album` = 999" );
        $this->assertEquals( 1, sizeOf( $rows ) );
        $this->assertEquals( 999, $rows[0]['album'] );
        $this->assertTrue( filter_var( $rows[0]['user'], FILTER_VALIDATE_IP ) !== false );
        $this->assertEquals( 'msaperst@gmail.com', $rows[0]['email'] );
    }
}
?>