<?php

namespace coverage\integration;

use Exception;
use PHPUnit\Framework\TestCase;
use Product;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ProductIntegrationTest extends TestCase {

    public function tearDown() {
        $sql = new Sql();
        $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `products`;")['count'];
        $count++;
        $sql->executeStatement("ALTER TABLE `products` AUTO_INCREMENT = $count;");
        $sql->disconnect();
    }

    public function testWithIdNoProductId() {
        try {
            Product::withId(NULL);
        } catch (Exception $e) {
            $this->assertEquals('Product id is required', $e->getMessage());
        }
    }

    public function testWithIdBlankProductId() {
        try {
            Product::withId("");
        } catch (Exception $e) {
            $this->assertEquals('Product id can not be blank', $e->getMessage());
        }
    }

    public function testWithIdBadProductId() {
        try {
            Product::withId(999);
        } catch (Exception $e) {
            $this->assertEquals('Product id does not match any products', $e->getMessage());
        }
    }

    public function testWithIdGetDataArray() {
        $product = Product::withId(1);
        $productInfo = $product->getDataArray();
        $this->assertEquals('1', $productInfo['id']);
        $this->assertEquals('1', $productInfo['product_type']);
        $this->assertEquals('12x12', $productInfo['size']);
        $this->assertEquals(300, $productInfo['price']);
        $this->assertEquals('100.00', $productInfo['cost']);
    }

    public function testGetId() {
        $product = Product::withId(1);
        $this->assertEquals(1, $product->getId());
    }

    public function testWithParamsNotArray() {
        try {
            Product::withParams(NULL);
        } catch (Exception $e) {
            $this->assertEquals('Product type is required', $e->getMessage());
        }
    }

    public function testWithParamsNoType() {
        try {
            Product::withParams(array());
        } catch (Exception $e) {
            $this->assertEquals('Product type is required', $e->getMessage());
        }
    }

    public function testWithParamsBlankType() {
        $params = [
            'type' => ''
        ];
        try {
            Product::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Product type can not be blank', $e->getMessage());
        }
    }

    public function testWithParamsBadType() {
        $params = [
            'type' => '123'
        ];
        try {
            Product::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Product id does not match any products', $e->getMessage());
        }
    }

    public function testWithParamsNoSize() {
        $params = [
            'type' => '1'
        ];
        try {
            Product::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Product size is required', $e->getMessage());
        }
    }

    public function testWithParamsBlankSize() {
        $params = [
            'type' => '1',
            'size' => ''
        ];
        try {
            Product::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Product size can not be blank', $e->getMessage());
        }
    }

    public function testWithParamsNoPrice() {
        $params = [
            'type' => '1',
            'size' => '1x1'
        ];
        try {
            Product::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Product price is required', $e->getMessage());
        }
    }

    public function testWithParamsBlankPrice() {
        $params = [
            'type' => '1',
            'size' => '1x1',
            'price' => ''
        ];
        try {
            Product::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Product price can not be blank', $e->getMessage());
        }
    }

    public function testWithParamsNoCost() {
        $params = [
            'type' => '1',
            'size' => '1x1',
            'price' => '300'
        ];
        try {
            Product::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Product cost is required', $e->getMessage());
        }
    }

    public function testWithParamsBlankCost() {
        $params = [
            'type' => '1',
            'size' => '1x1',
            'price' => '300',
            'cost' => ''
        ];
        try {
            Product::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Product cost can not be blank', $e->getMessage());
        }
    }

    public function testWithParamsNoAccess() {
        try {
            $params = [
                'type' => '1',
                'size' => '1x1',
                'price' => 'abcd',
                'cost' => 'df4l'
            ];
            $product = Product::withParams($params);
            $product->create();
        } catch (Exception $e) {
            $this->assertEquals('User not authorized to create product', $e->getMessage());
        }
    }


    public function testWithParamsString() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $params = [
            'type' => '1',
            'size' => '1x1',
            'price' => 'abcd',
            'cost' => 'df4l'
        ];
        try {
            $product = Product::withParams($params);
            $productId = $product->create();
            $productInfo = $product->getDataArray();
            $this->assertEquals($productId, $productInfo['id']);
            $this->assertEquals('1', $productInfo['product_type']);
            $this->assertEquals('1x1', $productInfo['size']);
            $this->assertEquals('0.00', $productInfo['price']);
            $this->assertEquals(0, $productInfo['cost']);
        } finally{
            unset( $_SESSION ['hash'] );
            $sql = new Sql();
            $sql->executeStatement("DELETE FROM products WHERE id = $productId");
            $sql->disconnect();
        }
    }

    public function testWithParamsDollar() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $params = [
            'type' => '1',
            'size' => '1x1',
            'price' => '$12.2345',
            'cost' => '1ge23rt'
        ];
        try {
            $product = Product::withParams($params);
            $productId = $product->create();
            $productInfo = $product->getDataArray();
            $this->assertEquals($productId, $productInfo['id']);
            $this->assertEquals('1', $productInfo['product_type']);
            $this->assertEquals('1x1', $productInfo['size']);
            $this->assertEquals('12.23', $productInfo['price']);
            $this->assertEquals('1.00', $productInfo['cost']);
        } finally {
            unset( $_SESSION ['hash'] );
            $sql = new Sql();
            $sql->executeStatement("DELETE FROM products WHERE id = $productId");
            $sql->disconnect();
        }
    }

    public function testWithParamsGetDataArray() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $params = [
                'type' => '1',
                'size' => '1x1',
                'price' => '$12.2345',
                'cost' => '12.5'
            ];
            $product = Product::withParams($params);
            $productId = $product->create();
            unset($_SESSION['hash']);
            $productInfo = $product->getDataArray();
            $this->assertEquals($productId, $productInfo['id']);
            $this->assertEquals('1', $productInfo['product_type']);
            $this->assertEquals('1x1', $productInfo['size']);
            $this->assertEquals(12.23, $productInfo['price']);
            $this->assertEquals('12.50', $productInfo['cost']);
        } finally {
            $sql = new Sql();
            $sql->executeStatement("DELETE FROM products WHERE id = $productId");
            $sql->disconnect();
        }
    }

    public function testCreateNoPermissionsDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $params = [
                'type' => '1',
                'size' => '1x1',
                'price' => '$12.2345',
                'cost' => '12.5'
            ];
            $product = Product::withParams($params);
            $productId = $product->create();
            unset($_SESSION['hash']);
            $product->delete();
        } catch (Exception $e) {
            $this->assertEquals('User not authorized to delete product', $e->getMessage());
        } finally {
            $sql = new Sql();
            $sql->executeStatement("DELETE FROM products WHERE id = $productId");
            $sql->disconnect();
        }
    }

    public function testCreateDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $params = [
                'type' => '1',
                'size' => '1x1',
                'price' => '$12.2345',
                'cost' => '12.5'
            ];
            $product = Product::withParams($params);
            $productId = $product->create();
            $product->delete();
            unset($_SESSION['hash']);
            $sql = new Sql();
            $this->assertEquals(0, $sql->getRowCount("SELECT * FROM products WHERE id = $productId"));
        } finally {
            $sql->executeStatement("DELETE FROM products WHERE id = $productId");
            $sql->disconnect();
        }
    }
}