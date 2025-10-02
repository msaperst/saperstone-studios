<?php

namespace coverage\integration;

use BadProductException;
use BadProductTypeException;
use BadUserException;
use Exception;
use PHPUnit\Framework\TestCase;
use Product;
use ProductException;
use Sql;
use SqlException;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ProductIntegrationTest extends TestCase {

    /**
     * @throws SqlException
     */
    public function tearDown(): void {
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

    /**
     * @throws BadProductTypeException
     * @throws BadProductException
     */
    public function testWithIdGetDataArray() {
        $product = Product::withId(1);
        $productInfo = $product->getDataArray();
        $this->assertEquals('1', $productInfo['id']);
        $this->assertEquals('1', $productInfo['product_type']);
        $this->assertEquals('12x12', $productInfo['size']);
        $this->assertEquals(300, $productInfo['price']);
        $this->assertEquals('100.00', $productInfo['cost']);
    }

    /**
     * @throws BadProductTypeException
     * @throws BadProductException
     */
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


    /**
     * @throws BadUserException
     * @throws BadProductException
     * @throws SqlException
     * @throws ProductException
     * @throws BadProductTypeException
     */
    public function testWithParamsString() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $params = [
            'type' => '1',
            'size' => '1x1',
            'price' => 'abcd',
            'cost' => 'df4l'
        ];
        $productId = 0;
        try {
            $product = Product::withParams($params);
            $productId = $product->create();
            $productInfo = $product->getDataArray();
            $this->assertEquals($productId, $productInfo['id']);
            $this->assertEquals('1', $productInfo['product_type']);
            $this->assertEquals('1x1', $productInfo['size']);
            $this->assertEquals('0.00', $productInfo['price']);
            $this->assertEquals(0, $productInfo['cost']);
        } finally {
            unset($_SESSION ['hash']);
            $sql = new Sql();
            $sql->executeStatement("DELETE FROM products WHERE id = $productId");
            $sql->disconnect();
        }
    }

    /**
     * @throws BadUserException
     * @throws BadProductException
     * @throws SqlException
     * @throws ProductException
     * @throws BadProductTypeException
     */
    public function testWithParamsDollar() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $params = [
            'type' => '1',
            'size' => '1x1',
            'price' => '$12.2345',
            'cost' => '1ge23rt'
        ];
        $productId = 0;
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
            unset($_SESSION ['hash']);
            $sql = new Sql();
            $sql->executeStatement("DELETE FROM products WHERE id = $productId");
            $sql->disconnect();
        }
    }

    /**
     * @throws BadUserException
     * @throws BadProductException
     * @throws ProductException
     * @throws SqlException
     * @throws BadProductTypeException
     */
    public function testWithParamsGetDataArray() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $productId = 0;
        try {
            $params = [
                'type' => '1',
                'size' => '1x1',
                'price' => '$12.2345',
                'cost' => '12.5'
            ];
            $product = Product::withParams($params);
            $productId = $product->create();
            $productInfo = $product->getDataArray();
            $this->assertEquals($productId, $productInfo['id']);
            $this->assertEquals('1', $productInfo['product_type']);
            $this->assertEquals('1x1', $productInfo['size']);
            $this->assertEquals(12.23, $productInfo['price']);
            $this->assertEquals('12.50', $productInfo['cost']);
        } finally {
            unset($_SESSION['hash']);
            $sql = new Sql();
            $sql->executeStatement("DELETE FROM products WHERE id = $productId");
            $sql->disconnect();
        }
    }

    public function testUpdateNoPermissions() {
        try {
            $product = Product::withId(1);
            $product->update(array());
        } catch (Exception $e) {
            $this->assertEquals('User not authorized to update product', $e->getMessage());
        }
    }

    /**
     * @throws BadUserException
     * @throws BadProductException
     * @throws SqlException
     * @throws ProductException
     * @throws BadProductTypeException
     */
    public function testUpdate() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $params = [
                'type' => '1',
                'size' => '1x1',
                'price' => '$12.2345',
                'cost' => '12.5'
            ];
            $product = Product::withId(1);
            $product->update($params);
            $productInfo = $product->getDataArray();
            $this->assertEquals(1, $productInfo['id']);
            $this->assertEquals('1', $productInfo['product_type']);
            $this->assertEquals('1x1', $productInfo['size']);
            $this->assertEquals(12.23, $productInfo['price']);
            $this->assertEquals('12.50', $productInfo['cost']);
            $sql = new Sql();
            $productInfo = $sql->getRow("SELECT * FROM products WHERE id = 1");
            $this->assertEquals(1, $productInfo['id']);
            $this->assertEquals('1', $productInfo['product_type']);
            $this->assertEquals('1x1', $productInfo['size']);
            $this->assertEquals(12.23, $productInfo['price']);
            $this->assertEquals('12.50', $productInfo['cost']);
        } finally {
            unset($_SESSION['hash']);
            $sql = new Sql();
            $sql->executeStatement("UPDATE `products` SET `product_type` = '1', `size` = '12x12', `cost` = '100' , `price` = '300' WHERE `products`.`id` = 1;");
            $sql->disconnect();
        }
    }

    /**
     * @throws SqlException
     */
    public function testCreateNoPermissionsDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $productId = 0;
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

    /**
     * @throws SqlException
     * @throws BadProductTypeException
     * @throws BadUserException
     * @throws BadProductException
     * @throws ProductException
     */
    public function testCreateDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $sql = new Sql();
        $productId = 0;
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
            $this->assertEquals(0, $sql->getRowCount("SELECT * FROM products WHERE id = $productId"));
        } finally {
            $sql->executeStatement("DELETE FROM products WHERE id = $productId");
            $sql->disconnect();
        }
    }
}