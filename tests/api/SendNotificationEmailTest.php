<?php

namespace api;

use CustomAsserts;
use Exception;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Google\Exception as ExceptionAlias;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SendNotificationEmailTest extends TestCase {
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
        $this->sql->executeStatement("INSERT INTO `notification_emails` (`album`, `user`, `email`, `contacted`) VALUES ('999', 'NULL', 'msaperst+sstest@gmail.com', 0);");
        $this->sql->executeStatement("INSERT INTO `notification_emails` (`album`, `user`, `email`, `contacted`) VALUES ('999', '2', 'msaperst+sstest2@gmail.com', 0);");
        $this->sql->executeStatement("INSERT INTO `notification_emails` (`album`, `user`, `email`, `contacted`) VALUES ('999', '2', 'msaperst+sstest2@gmail.com', 1);");
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

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/send-notification-email.php');
        } catch (GuzzleException | ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        try {
            $this->http->request('POST', 'api/send-notification-email.php', [
                'cookies' => $cookieJar
            ]);
        } catch (GuzzleException | ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testNoAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/send-notification-email.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Album id is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankAlbumId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/send-notification-email.php', [
            'cookies' => $cookieJar,
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
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/send-notification-email.php', [
            'cookies' => $cookieJar,
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
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/send-notification-email.php', [
            'cookies' => $cookieJar,
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
    public function testNoMessage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/send-notification-email.php', [
            'cookies' => $cookieJar,
            'form_params' => [
                'album' => 999
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Message is required", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankMessage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/send-notification-email.php', [
            'cookies' => $cookieJar,
            'form_params' => [
                'album' => 999,
                'message' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Message can not be blank", (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     * @throws ExceptionAlias
     */
    public function testMessage() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/send-notification-email.php', [
            'cookies' => $cookieJar,
            'form_params' => [
                'album' => 999,
                'message' => 'max@max'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $notifications = $this->sql->getRows("SELECT * FROM notification_emails WHERE album = 999;");
        $this->assertEquals(3, sizeof($notifications));
        $this->assertEquals(1, $notifications[0]['contacted']);
        $this->assertEquals(1, $notifications[1]['contacted']);
        $this->assertEquals(1, $notifications[2]['contacted']);
        CustomAsserts::assertEmailEquals('Album Updated on Saperstone Studios',
            "An album you requested to be updated about has been updated.\r
\r
max@max",
            "<html><body>An album you requested to be updated about has been updated.\r
\r
max@max</body></html>");
        CustomAsserts::assertEmailEquals('Album Updated on Saperstone Studios',
            "An album you requested to be updated about has been updated.\r
\r
max@max",
            "<html><body>An album you requested to be updated about has been updated.\r
\r
max@max</body></html>");
    }
}