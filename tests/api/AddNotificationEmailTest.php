<?php

namespace api;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class AddNotificationEmailTest extends TestCase {
    /**
     * @var Client
     */
    private $http;
    /**
     * @var Sql
     */
    private $sql;

    /**
     * @throws Exception
     */
    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `albums` (`id`, `name`, `description`, `location`) VALUES ('999', 'sample-album', 'sample album for testing', '');");
    }

    /**
     * @throws Exception
     */
    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `albums` WHERE `albums`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `notification_emails` WHERE `notification_emails`.`album` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `albums`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `albums` AUTO_INCREMENT = $count;");
        $this->sql->disconnect();
    }

    /**
     * @throws GuzzleException
     */
    public function testNoAlbumId() {
        $response = $this->http->request('POST', 'api/add-notification-email.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankAlbumId() {
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
            'form_params' => [
                'album' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testLetterAlbumId() {
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
            'form_params' => [
                'album' => 'a'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBadAlbumId() {
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
            'form_params' => [
                'album' => 9999
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id does not match any albums", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testNoEmail() {
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
            'form_params' => [
                'album' => 999
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankEmail() {
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
            'form_params' => [
                'album' => 999,
                'email' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBadEmail() {
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
            'form_params' => [
                'album' => 999,
                'email' => 'max@max'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is not valid", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testLoggedIn() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
            'form_params' => [
                'album' => 999,
                'email' => 'msaperst+sstest@gmail.com'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $rows = $this->sql->getRows("SELECT * FROM `notification_emails` WHERE `notification_emails`.`album` = 999");
        $this->assertEquals(1, sizeOf($rows));
        $this->assertEquals(999, $rows[0]['album']);
        $this->assertEquals(1, $rows[0]['user']);
        $this->assertEquals('msaperst+sstest@gmail.com', $rows[0]['email']);
    }

    /**
     * @throws GuzzleException
     */
    public function testNotLoggedIn() {
        $response = $this->http->request('POST', 'api/add-notification-email.php', [
            'form_params' => [
                'album' => 999,
                'email' => 'msaperst+sstest@gmail.com'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $rows = $this->sql->getRows("SELECT * FROM `notification_emails` WHERE `notification_emails`.`album` = 999");
        $this->assertEquals(1, sizeOf($rows));
        $this->assertEquals(999, $rows[0]['album']);
        $this->assertTrue(filter_var($rows[0]['user'], FILTER_VALIDATE_IP) !== false);
        $this->assertEquals('msaperst+sstest@gmail.com', $rows[0]['email']);
    }
}