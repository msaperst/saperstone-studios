<?php

namespace coverage\integration;

use BadProductTypeException;
use BadUserException;
use Exception;
use PHPUnit\Framework\TestCase;
use ProductType;
use ProductTypeException;
use Sql;
use SqlException;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ProductTypeIntegrationTest extends TestCase {

    /**
     * @throws SqlException
     */
    public function tearDown(): void {
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

    /**
     * @throws BadProductTypeException
     */
    public function testWithIdGetDataArray() {
        $productType = ProductType::withId(1);
        $productTypeInfo = $productType->getDataArray();
        $this->assertEquals('1', $productTypeInfo['id']);
        $this->assertEquals('signature', $productTypeInfo['category']);
        $this->assertEquals('Acrylic Prints', $productTypeInfo['name']);
    }

    /**
     * @throws BadProductTypeException
     */
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

    /**
     * @throws BadUserException
     * @throws SqlException
     * @throws BadProductTypeException
     * @throws ProductTypeException
     */
    public function testWithParamsGetDataArray() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $productId = 0;
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

    public function testUpdateNoPermissions() {
        try {
            $product = ProductType::withId(1);
            $product->update(array());
        } catch (Exception $e) {
            $this->assertEquals('User not authorized to update product type', $e->getMessage());
        }
    }

    /**
     * @throws BadUserException
     * @throws SqlException
     * @throws ProductTypeException
     * @throws BadProductTypeException
     */
    public function testUpdate() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        try {
            $params = [
                'category' => 'signature',
                'name' => 'name'
            ];
            $productType = ProductType::withId(1);
            $productType->update($params);
            $productTypeInfo = $productType->getDataArray();
            $this->assertEquals(1, $productTypeInfo['id']);
            $this->assertEquals('signature', $productTypeInfo['category']);
            $this->assertEquals('name', $productTypeInfo['name']);
            $sql = new Sql();
            $productTypeInfo = $sql->getRow("SELECT * FROM product_types WHERE id = 1");
            $this->assertEquals(1, $productTypeInfo['id']);
            $this->assertEquals('signature', $productTypeInfo['category']);
            $this->assertEquals('name', $productTypeInfo['name']);
        } finally {
            unset($_SESSION['hash']);
            $sql = new Sql();
            $sql->executeStatement("UPDATE `product_types` SET `category` = 'signature', `name` = 'Acrylic Prints' WHERE `id` = 1;");
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

    /**
     * @throws BadUserException
     * @throws SqlException
     * @throws BadProductTypeException
     * @throws ProductTypeException
     */
    public function testCreateDelete() {
        $_SESSION ['hash'] = "1d7505e7f434a7713e84ba399e937191";
        $sql = new Sql();
        $productId = 0;
        try {
            $params = [
                'category' => 'signature',
                'name' => 'name'
            ];
            $product = ProductType::withParams($params);
            $productId = $product->create();
            $product->delete();
            unset($_SESSION['hash']);
            $this->assertEquals(0, $sql->getRowCount("SELECT * FROM product_types WHERE id = $productId"));
        } finally {
            $sql->executeStatement("DELETE FROM product_types WHERE id = $productId");
            $sql->disconnect();
        }
    }
}