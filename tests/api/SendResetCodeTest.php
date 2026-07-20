<?php

namespace api;

use CustomAsserts;
use Exception;
use Google\Exception as ExceptionAlias;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';
require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SendResetCodeTest extends TestCase {
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
    public function setUp(): void {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `users` (`usr`, `pass`, `firstName`, `lastName`, `email`, `role`, `active`, `hash`) VALUES ('testUser', 'somepassword', 'Test', 'User', 'msaperst+sstest@gmail.com', 'downloader', '1', 'sdlkjfisudkhfkvlzjh');");

        // Ensure every single test starts with a completely clean mailbox slate!
        CustomAsserts::clearAllEmails();
    }

    /**
     * @throws Exception
     */
    public function tearDown(): void {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `users` WHERE email = 'msaperst+sstest@gmail.com';");
        $this->sql->disconnect();
    }

    /**
     * @throws GuzzleException
     */
    public function testNoEmail() {
        $response = $this->http->request('POST', 'api/send-reset-code.php');
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is required", $response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     */
    public function testBlankEmail() {
        $response = $this->http->request('POST', 'api/send-reset-code.php', [
            'form_params' => [
                'email' => ''
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email can not be blank", $response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     */
    public function testInvalidEmail() {
        $response = $this->http->request('POST', 'api/send-reset-code.php', [
            'form_params' => [
                'email' => '1234@hi'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Email is not valid", $response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     */
    public function testBadEmail() {
        $response = $this->http->request('POST', 'api/send-reset-code.php', [
            'form_params' => [
                'email' => 'msap@gmail.com'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Credentials do not match our records", (string)$response->getBody());
        CustomAsserts::assertEmailCount(0);
    }

    /**
     * @throws GuzzleException
     * @throws ExceptionAlias
     */
    public function testSendResetCode() {
        $response = $this->http->request('POST', 'api/send-reset-code.php', [
            'form_params' => [
                'email' => 'msaperst@gmail.com'
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());

        CustomAsserts::assertEmailCount(1);
        CustomAsserts::assertEmailMatches(
            'msaperst@gmail.com',
            'noreply@saperstonestudios.com',
            'Reset Key For Saperstone Studios Account',
            "You requested a reset key for your saperstone studios account. Enter the key below to reset your password. If you did not request this key, disregard this message.

\t%s",
            '<html><body>You requested a reset key for your saperstone studios account. Enter the key below to reset your password. If you did not request this key, disregard this message.<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;%s</body></html>');
    }
}