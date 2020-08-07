<?php

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;

$_SERVER ['DOCUMENT_ROOT'] = dirname(__DIR__);
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "src/sql.php";

class DeleteProductOptionTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `product_options` (`product_type`, `opt`) VALUES ('999', 'Purple')");
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `product_options` WHERE `product_options`.`product_type` = 999;");
        $this->sql->disconnect();
    }

    public function testNotLoggedIn() {
        $response;
        try {
            $response = $this->http->request('POST', 'api/delete-product.php');
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("", $e->getResponse()->getBody());
        }
    }

    public function testLoggedInAsDownloader() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], 'localhost');
        $response;
        try {
            $response = $this->http->request('POST', 'api/delete-product.php', [
                'cookies' => $cookieJar
            ]);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals("You do not have appropriate rights to perform this action", $e->getResponse()->getBody());
        }
    }

    public function testNoProductType() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-product-option.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product type is required", (string)$response->getBody());
    }

    public function testBlankProductType() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-product-option.php', [
            'form_params' => [
                'type' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product type can not be blank", (string)$response->getBody());
    }

    public function testNoProductOption() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-product-option.php', [
            'form_params' => [
                'type' => 999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product option is required", (string)$response->getBody());
    }

    public function testBlankProductOption() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-product-option.php', [
            'form_params' => [
                'type' => 999,
                'option' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product option can not be blank", (string)$response->getBody());
    }

    public function testDeleteProductOption() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-product-option.php', [
            'form_params' => [
                'type' => 999,
                'option' => 'Purple'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `product_options` WHERE `product_options`.`product_type` = 999;"));
    }

    public function testDeleteProductOptionNoMatch() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-product-option.php', [
            'form_params' => [
                'type' => 999,
                'option' => '123'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $this->assertEquals(1, $this->sql->getRowCount("SELECT * FROM `product_options` WHERE `product_options`.`product_type` = 999;"));
    }
}

?>