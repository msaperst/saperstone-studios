<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class GetUsersTest extends TestCase {
    private $http;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://' . getenv('DB_HOST') . ':90/']);
    }

    public function tearDown() {
        $this->http = NULL;
    }

    public function testNotLoggedIn() {
        try {
            $this->http->request('POST', 'api/get-users.php');
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
            $this->http->request('POST', 'api/get-users.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testUsers() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/get-users.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $users = json_decode($response->getBody(), true)['data'];
        $this->assertTrue(5 <= sizeof($users));
        $this->assertEquals(0, $users[0]['id']);
        $this->assertEquals('<i>All Users</i>', $users[0]['usr']);
        $this->assertEquals('', $users[0]['pass']);
        $this->assertEquals('', $users[0]['firstName']);
        $this->assertEquals('', $users[0]['lastName']);
        $this->assertEquals('', $users[0]['email']);
        $this->assertEquals('admin', $users[0]['role']);
        $this->assertEquals('', $users[0]['hash']);
        $this->assertEquals(0, $users[0]['active']);
        $this->assertTrue(key_exists('created', $users[0]));
        $this->assertNull($users[0]['lastLogin']);
        $this->assertNull($users[0]['resetKey']);

        $this->assertEquals(1, $users[1]['id']);
        $this->assertEquals('msaperst', $users[1]['usr']);
        $this->assertEquals('d52f8e3b932c448547de9539085da351', $users[1]['pass']);
        $this->assertEquals('Max', $users[1]['firstName']);
        $this->assertEquals('Saperstone', $users[1]['lastName']);
        $this->assertEquals('msaperst@gmail.com', $users[1]['email']);
        $this->assertEquals('admin', $users[1]['role']);
        $this->assertEquals('1d7505e7f434a7713e84ba399e937191', $users[1]['hash']);
        $this->assertEquals(1, $users[1]['active']);
        $this->assertTrue(key_exists('created', $users[1]));
        $this->assertTrue(key_exists('lastLogin', $users[1]));
        $this->assertNull($users[1]['resetKey']);

        $this->assertEquals(2, $users[2]['id']);
        $this->assertEquals('lsaperst', $users[2]['usr']);
        $this->assertEquals('99cac6be874ee7b7087a09bc6dbc527b', $users[2]['pass']);
        $this->assertEquals('Leigh Ann', $users[2]['firstName']);
        $this->assertEquals('Saperstone', $users[2]['lastName']);
        $this->assertEquals('la@saperstonestudios.com', $users[2]['email']);
        $this->assertEquals('admin', $users[2]['role']);
        $this->assertEquals('398702d7c97b7fcf8aa31379bab01828', $users[2]['hash']);
        $this->assertEquals(1, $users[2]['active']);
        $this->assertTrue(key_exists('created', $users[2]));
        $this->assertTrue(key_exists('lastLogin', $users[2]));
        $this->assertNull($users[2]['resetKey']);

        $this->assertEquals(3, $users[3]['id']);
        $this->assertEquals('downloader', $users[3]['usr']);
        $this->assertEquals('5f4dcc3b5aa765d61d8327deb882cf99', $users[3]['pass']);
        $this->assertEquals('Download', $users[3]['firstName']);
        $this->assertEquals('User', $users[3]['lastName']);
        $this->assertEquals('email@example.org', $users[3]['email']);
        $this->assertEquals('downloader', $users[3]['role']);
        $this->assertEquals('5510b5e6fffd897c234cafe499f76146', $users[3]['hash']);
        $this->assertEquals(1, $users[3]['active']);
        $this->assertTrue(key_exists('created', $users[3]));
        $this->assertTrue(key_exists('lastLogin', $users[3]));
        $this->assertNull($users[3]['resetKey']);

        $this->assertEquals(4, $users[4]['id']);
        $this->assertEquals('uploader', $users[4]['usr']);
        $this->assertEquals('5f4dcc3b5aa765d61d8327deb882cf99', $users[4]['pass']);
        $this->assertEquals('Upload', $users[4]['firstName']);
        $this->assertEquals('User', $users[4]['lastName']);
        $this->assertEquals('uploader@example.org', $users[4]['email']);
        $this->assertEquals('uploader', $users[4]['role']);
        $this->assertEquals('c90788c0e409eac6a95f6c6360d8dbf7', $users[4]['hash']);
        $this->assertEquals(1, $users[4]['active']);
        $this->assertTrue(key_exists('created', $users[4]));
        $this->assertTrue(key_exists('lastLogin', $users[4]));
        $this->assertNull($users[4]['resetKey']);
    }
}
