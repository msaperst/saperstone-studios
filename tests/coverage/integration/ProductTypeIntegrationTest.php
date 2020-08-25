<?php

namespace coverage\integration;

use Exception;
use PHPUnit\Framework\TestCase;
use ProductType;
use Sql;

require_once dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ProductTypeIntegrationTest extends TestCase {

    public function tearDown() {
        $sql = new Sql();
        $count = $sql->getRow("SELECT MAX(`id`) AS `count` FROM `product_types`;")['count'];
        $count++;
        $sql->executeStatement("ALTER TABLE `product_types` AUTO_INCREMENT = $count;");
        $sql->disconnect();
    }

    public function testWithIdNoProductTypeId() {
        try {
            ProductType::withId(NULL);
        } catch (Exception $e) {
            $this->assertEquals('Product id is required', $e->getMessage());
        }
    }

    public function testWithIdBlankProductTypeId() {
        try {
            ProductType::withId("");
        } catch (Exception $e) {
            $this->assertEquals('Product id can not be blank', $e->getMessage());
        }
    }

    public function testWithIdBadProductTypeId() {
        try {
            ProductType::withId(999);
        } catch (Exception $e) {
            $this->assertEquals('Product id does not match any products', $e->getMessage());
        }
    }

    public function testWithIdGetDataArray() {
        $productType = ProductType::withId(1);
        $productTypeInfo = $productType->getDataArray();
        $this->assertEquals('1', $productTypeInfo['id']);
        $this->assertEquals('signature', $productTypeInfo['category']);
        $this->assertEquals('Acrylic Prints', $productTypeInfo['name']);
    }

    public function testGetId() {
        $productType = ProductType::withId(1);
        $this->assertEquals(1, $productType->getId());
    }

    public function testWithParamsNotArray() {
        try {
            ProductType::withParams(NULL);
        } catch (Exception $e) {
            $this->assertEquals('Product category is required', $e->getMessage());
        }
    }

    public function testWithParamsNoCategory() {
        try {
            ProductType::withParams(array());
        } catch (Exception $e) {
            $this->assertEquals('Product category is required', $e->getMessage());
        }
    }

    public function testWithParamsBlankCategory() {
        $params = [
            'category' => ''
        ];
        try {
            ProductType::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Product category can not be blank', $e->getMessage());
        }
    }

    public function testWithParamsBadCategory() {
        $params = [
            'category' => '123'
        ];
        try {
            ProductType::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Product category is not valid', $e->getMessage());
        }
    }

    public function testWithParamsNoName() {
        $params = [
            'category' => 'signature'
        ];
        try {
            ProductType::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Product name is required', $e->getMessage());
        }
    }

    public function testWithParamsBlankName() {
        $params = [
            'category' => 'signature',
            'name' => ''
        ];
        try {
            ProductType::withParams($params);
        } catch (Exception $e) {
            $this->assertEquals('Product name can not be blank', $e->getMessage());
        }
    }

    public function testWithParamsNoAccess() {
        try {
            $params = [
                'category' => 'signature',
                'name' => 'name'
            ];
            $product = ProductType::withParams($params);
            $product->create();
        } catch (Exception $e) {
            $this->assertEquals('User not authorized to create product type', $e->getMessage());
        }
    }

    public function testWithParamsGetDataArray() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $params = [
                'category' => 'signature',
                'name' => 'name'
            ];
            $product = ProductType::withParams($params);
            $productId = $product->create();
            unset($_SESSION['hash']);
            $productTypeInfo = $product->getDataArray();
            $this->assertEquals($productId, $productTypeInfo['id']);
            $this->assertEquals('signature', $productTypeInfo['category']);
            $this->assertEquals('name', $productTypeInfo['name']);
        } finally {
            $sql = new Sql();
            $sql->executeStatement("DELETE FROM product_types WHERE id = $productId");
            $sql->disconnect();
        }
    }

    public function testCreateNoPermissionsDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $params = [
                'category' => 'signature',
                'name' => 'name'
            ];
            $product = ProductType::withParams($params);
            $productId = $product->create();
            unset($_SESSION['hash']);
            $product->delete();
        } catch (Exception $e) {
            $this->assertEquals('User not authorized to delete product type', $e->getMessage());
        } finally {
            $sql = new Sql();
            $sql->executeStatement("DELETE FROM product_types WHERE id = $productId");
            $sql->disconnect();
        }
    }

    public function testCreateDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $params = [
                'category' => 'signature',
                'name' => 'name'
            ];
            $product = ProductType::withParams($params);
            $productId = $product->create();
            $product->delete();
            unset($_SESSION['hash']);
            $sql = new Sql();
            $this->assertEquals(0, $sql->getRowCount("SELECT * FROM product_types WHERE id = $productId"));
        } finally {
            $sql->executeStatement("DELETE FROM product_types WHERE id = $productId");
            $sql->disconnect();
        }
    }
}