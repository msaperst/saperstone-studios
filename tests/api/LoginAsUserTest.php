<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class LoginAsUserTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
        $this->sql = new Sql();
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/login-as-user.php');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        try {
            $this->http->request('POST', 'api/login-as-user.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoUserId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/login-as-user.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("User id is required", (string)$response->getBody());
    }

    public function testBlankUserId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/login-as-user.php', [
            'form_params' => [
                'id' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("User id can not be blank", (string)$response->getBody());
    }

    public function testBadUserId() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/login-as-user.php', [
            'form_params' => [
                'id' => '999'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("User id does not match any users", (string)$response->getBody());
    }

    public function testLoginAsUser() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/login-as-user.php', [
            'form_params' => [
                'id' => '4'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        //TODO - check cookies
    }

}
