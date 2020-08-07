<?php

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;

$_SERVER ['DOCUMENT_ROOT'] = dirname(__DIR__);
require_once dirname($_SERVER ['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . "src/sql.php";

class DeleteProductTest extends TestCase {
    private $http;
    private $sql;

    public function setUp() {
        $this->http = new Client(['base_uri' => 'http://localhost:90/']);
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO `product_types` (`id`, `category`, `name`) VALUES (999, 'other', 'Pants')");
        $this->sql->executeStatement("INSERT INTO `product_options` (`product_type`, `opt`) VALUES ('999', 'Purple')");
        $this->sql->executeStatement("INSERT INTO `products` (`product_type`, `size`, `price`, `cost`) VALUES ('999', '12x19', 100, 10)");
    }

    public function tearDown() {
        $this->http = NULL;
        $this->sql->executeStatement("DELETE FROM `product_types` WHERE `product_types`.`id` = 999;");
        $this->sql->executeStatement("DELETE FROM `product_options` WHERE `product_options`.`product_type` = 999;");
        $this->sql->executeStatement("DELETE FROM `products` WHERE `products`.`product_type` = 999;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `product_types`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `product_types` AUTO_INCREMENT = $count;");
        $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `products`;")['count'];
        $count++;
        $this->sql->executeStatement("ALTER TABLE `products` AUTO_INCREMENT = $count;");
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

    public function testNoProduct() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-product.php', [
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product id is required", (string)$response->getBody());
    }

    public function testBlankProduct() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-product.php', [
            'form_params' => [
                'id' => ''
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product id can not be blank", (string)$response->getBody());
    }

    public function testLetterProduct() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-product.php', [
            'form_params' => [
                'id' => 'a'
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product id does not match any products", (string)$response->getBody());
    }

    public function testBadProduct() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-product.php', [
            'form_params' => [
                'id' => 9999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Product id does not match any products", (string)$response->getBody());
    }

    public function testDeleteProduct() {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], 'localhost');
        $response = $this->http->request('POST', 'api/delete-product.php', [
            'form_params' => [
                'id' => 999
            ],
            'cookies' => $cookieJar
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("", (string)$response->getBody());
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `product_types` WHERE `product_type`.`id` = 999;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `product_options` WHERE `product_options`.`product_type` = 999;"));
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM `products` WHERE `products`.`product_type` = 999;"));
    }
}

?>