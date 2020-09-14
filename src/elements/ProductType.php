<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class ProductType {

    private $raw;
    private $id;
    private $category;
    private $name;

    public function __construct() {
    }

    static function withId($id) {
        if (!isset ($id)) {
            throw new Exception("Product id is required");
        } elseif ($id == "") {
            throw new Exception("Product id can not be blank");
        }
        $productType = new ProductType();
        $id = (int)$id;
        $sql = new Sql();
        $productType->raw = $sql->getRow("SELECT * FROM product_types WHERE id = $id;");
        $sql->disconnect();
        if (!$productType->raw ['id']) {
            throw new Exception("Product id does not match any products");
        }
        $productType->id = $productType->raw['id'];
        $productType->category = $productType->raw['category'];
        $productType->name = $productType->raw['name'];
        return $productType;
    }

    static function withParams($params) {
        return self::setVals(new ProductType(), $params);
    }

    static function setVals($productType, $params) {
        $sql = new Sql();
        //product category
        if (!isset ($params['category'])) {
            $sql->disconnect();
            throw new Exception("Product category is required");
        } elseif ($params['category'] == "") {
            $sql->disconnect();
            throw new Exception("Product category can not be blank");
        } elseif (!in_array($params['category'], $sql->getEnumValues('product_types', 'category'))) {
            $sql->disconnect();
            throw new Exception ("Product category is not valid");
        }
        $productType->category = $sql->escapeString($params ['category']);
        //product name
        if (!isset ($params['name'])) {
            $sql->disconnect();
            throw new Exception("Product name is required");
        } elseif ($params['name'] == "") {
            $sql->disconnect();
            throw new Exception("Product name can not be blank");
        }
        $productType->name = $sql->escapeString($params ['name']);
        $sql->disconnect();
        return $productType;
    }

    function getId() {
        return $this->id;
    }

    function getDataArray() {
        return $this->raw;
    }

    function create() {
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new Exception("User not authorized to create product type");
        }
        $sql = new Sql();
        $lastId = $sql->executeStatement("INSERT INTO `product_types` (`id`, `category`, `name`) VALUES (NULL, '{$this->category}', '{$this->name}');");
        $sql->disconnect();
        $this->id = $lastId;
        $productType = self::withId($lastId);
        $this->raw = $productType->getDataArray();
        return $lastId;
    }

    function update($params) {
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new Exception("User not authorized to update product type");
        }
        self::setVals($this, $params);
        $sql = new Sql();
        $sql->executeStatement("UPDATE `product_types` SET `category` = '{$this->category}', `name` = '{$this->name}' WHERE `product_types`.`id` = {$this->id};");
        $this->raw = $sql->getRow("SELECT * FROM product_types WHERE id = {$this->getId()};");
        $sql->disconnect();
    }

    function delete() {
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new Exception("User not authorized to delete product type");
        }
        $sql = new Sql();
        $sql->executeStatement("DELETE FROM product_types WHERE id='{$this->id}';");
        $sql->executeStatement("DELETE FROM products WHERE product_type='{$this->id}';");
        $sql->executeStatement("DELETE FROM product_options WHERE product_type='{$this->id}';");
        $sql->disconnect();
    }
}