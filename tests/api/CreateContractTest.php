<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class CreateContractTest extends TestCase {
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
            $this->http->request('POST', 'api/create-contract.php');
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
            $response = $this->http->request('POST', 'api/create-contract.php', [
                'cookies' => $cookieJar
            ]);
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoType() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-contract.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract type is required", (string)$response->getBody());
    }

    public function testBlankType() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-contract.php', [
            'form_params' => [
                'type' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract type can not be blank", (string)$response->getBody());
    }

    public function testNoName() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-contract.php', [
            'form_params' => [
                'type' => 'wedding'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract name is required", (string)$response->getBody());
    }

    public function testBlankName() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-contract.php', [
            'form_params' => [
                'type' => 'wedding',
                'name' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract name can not be blank", (string)$response->getBody());
    }

    public function testNoSession() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-contract.php', [
            'form_params' => [
                'type' => 'wedding',
                'name' => 'MaxMaxMax'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract session is required", (string)$response->getBody());
    }

    public function testBlankSession() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-contract.php', [
            'form_params' => [
                'type' => 'wedding',
                'name' => 'MaxMaxMax',
                'session' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract session can not be blank", (string)$response->getBody());
    }

    public function testNoContent() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-contract.php', [
            'form_params' => [
                'type' => 'wedding',
                'name' => 'MaxMaxMax',
                'session' => 'funsies'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract content is required", (string)$response->getBody());
    }

    public function testBlankContent() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-contract.php', [
            'form_params' => [
                'type' => 'wedding',
                'name' => 'MaxMaxMax',
                'session' => 'funsies',
                'content' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract content can not be blank", (string)$response->getBody());
    }

    public function testNoDetails() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/create-contract.php', [
                'form_params' => [
                    'type' => 'wedding',
                    'name' => 'MaxMaxMax',
                    'session' => 'funsies',
                    'content' => 'my awesome contract!'
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $contractId = (string)$response->getBody();
            $contractDetails = $this->sql->getRow("SELECT * FROM `contracts` WHERE `contracts`.`id` = $contractId;");
            $this->assertEquals($contractId, $contractDetails['id']);
            $this->assertEquals(md5($contractId . "weddingMaxMaxMaxfunsies"), $contractDetails['link']);
            $this->assertEquals('wedding', $contractDetails['type']);
            $this->assertEquals('MaxMaxMax', $contractDetails['name']);
            $this->assertNull($contractDetails['address']);
            $this->assertNull($contractDetails['number']);
            $this->assertNull($contractDetails['email']);
            $this->assertNull($contractDetails['date']);
            $this->assertNull($contractDetails['location']);
            $this->assertEquals('funsies', $contractDetails['session']);
            $this->assertNull($contractDetails['details']);
            $this->assertEquals(0, $contractDetails['amount']);
            $this->assertEquals(0, $contractDetails['deposit']);
            $this->assertNull($contractDetails['invoice']);
            $this->assertEquals('my awesome contract!', $contractDetails['content']);
            $this->assertNull($contractDetails['signature']);
            $this->assertNull($contractDetails['initial']);
            $this->assertNull($contractDetails['file']);
            $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `contract_line_items` WHERE `contract_line_items`.`contract` = $contractId;"));
        } finally {
            $this->sql->executeStatement("DELETE FROM `contracts` WHERE `contracts`.`id` = $contractId;");
            $this->sql->executeStatement("DELETE FROM `contract_line_items` WHERE `contract_line_items`.`contract` = $contractId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `contracts`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `contracts` AUTO_INCREMENT = $count;");
        }
    }

    public function testBadDate() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('POST', 'api/create-contract.php', [
            'form_params' => [
                'type' => 'wedding',
                'name' => 'MaxMaxMax',
                'session' => 'funsies',
                'content' => 'my awesome contract!',
                'date' => '1234'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Contract date is not the correct format", (string)$response->getBody());
    }

    public function testAllDetails() {
        try {
            $cookieJar = CookieJar::fromArray([
                'hash' => '1d7505e7f434a7713e84ba399e937191'
            ], getenv('DB_HOST'));
            $response = $this->http->request('POST', 'api/create-contract.php', [
                'form_params' => [
                    'type' => 'wedding',
                    'name' => 'MaxMaxMax',
                    'session' => 'funsies',
                    'content' => 'my awesome contract!',
                    'amount' => '$25.25',
                    'deposit' => 9.267,
                    'address' => '123 Seasame Street',
                    'number' => '12345 F Off',
                    'email' => 'msaperst+sstest@gmail.com',
                    'date' => '2020-12-01',
                    'location' => 'Universal Studios',
                    'details' => 'None you care about',
                    'invoice' => 'link here!!!',
                    'lineItems' => [
                        0 => [
                            'amount' => 12.0,
                            'item' => 'snuggles',
                            'unit' => 'hugs'
                        ],
                        1 => [
                            'amount' => '$12.45'
                        ]
                    ]
                ],
                'cookies' => $cookieJar
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $contractId = $response->getBody();
            $contractDetails = $this->sql->getRow("SELECT * FROM `contracts` WHERE `contracts`.`id` = $contractId;");
            $this->assertEquals($contractId, $contractDetails['id']);
            $this->assertEquals(md5($contractId . "weddingMaxMaxMaxfunsies"), $contractDetails['link']);
            $this->assertEquals('wedding', $contractDetails['type']);
            $this->assertEquals('MaxMaxMax', $contractDetails['name']);
            $this->assertEquals('123 Seasame Street', $contractDetails['address']);
            $this->assertEquals('12345 F Off', $contractDetails['number']);
            $this->assertEquals('msaperst+sstest@gmail.com', $contractDetails['email']);
            $this->assertEquals('2020-12-01', $contractDetails['date']);
            $this->assertEquals('Universal Studios', $contractDetails['location']);
            $this->assertEquals('funsies', $contractDetails['session']);
            $this->assertEquals('None you care about', $contractDetails['details']);
            $this->assertEquals(25.25, $contractDetails['amount']);
            $this->assertEquals(9.27, $contractDetails['deposit']);
            $this->assertEquals('link here!!!', $contractDetails['invoice']);
            $this->assertEquals('my awesome contract!', $contractDetails['content']);
            $this->assertNull($contractDetails['signature']);
            $this->assertNull($contractDetails['initial']);
            $this->assertNull($contractDetails['file']);
            $contractLineItems = $this->sql->getRows("SELECT * FROM `contract_line_items` WHERE `contract_line_items`.`contract` = $contractId;");
            $this->assertEquals(2, sizeOf($contractLineItems));
            $this->assertEquals($contractId, $contractLineItems[0]['contract']);
            $this->assertEquals(12.0, $contractLineItems[0]['amount']);
            $this->assertEquals('snuggles', $contractLineItems[0]['item']);
            $this->assertEquals('hugs', $contractLineItems[0]['unit']);
            $this->assertEquals($contractId, $contractLineItems[1]['contract']);
            $this->assertEquals(12.45, $contractLineItems[1]['amount']);
            $this->assertEquals('', $contractLineItems[1]['item']);
            $this->assertEquals('', $contractLineItems[1]['unit']);
        } finally {
            $this->sql->executeStatement("DELETE FROM `contracts` WHERE `contracts`.`id` = $contractId;");
            $this->sql->executeStatement("DELETE FROM `contract_line_items` WHERE `contract_line_items`.`contract` = $contractId;");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `contracts`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `contracts` AUTO_INCREMENT = $count;");
        }
    }
}

?>