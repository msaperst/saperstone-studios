<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class Product {

    private $raw;
    private $id;
    private $type;
    private $size;
    private $price;
    private $cost;

    public function __construct() {
    }

    static function withId($id) {
        if (!isset ($id)) {
            throw new Exception("Product id is required");
        } elseif ($id == "") {
            throw new Exception("Product id can not be blank");
        }
        $product = new Product();
        $id = (int)$id;
        $sql = new Sql();
        $product->raw = $sql->getRow("SELECT * FROM products WHERE id = $id;");
        $sql->disconnect();
        if (!$product->raw ['id']) {
            throw new Exception("Product id does not match any products");
        }
        $product->id = $product->raw['id'];
        $product->type = ProductType::withId($product->raw['product_type']);
        $product->size = $product->raw['size'];
        $product->price = $product->raw['price'];
        $product->cost = $product->raw['cost'];
        return $product;
    }

    static function withParams($params) {
        return self::setVals(new Product(), $params);
    }

    private static function setVals(Product $product, $params) {
        $sql = new Sql();
        //product type
        if (!isset ($params['type'])) {
            $sql->disconnect();
            throw new Exception("Product type is required");
        } elseif ($params['type'] == "") {
            $sql->disconnect();
            throw new Exception("Product type can not be blank");
        }
        $product->type = ProductType::withId($params ['type']);
        //product size
        if (!isset ($params['size'])) {
            $sql->disconnect();
            throw new Exception("Product size is required");
        } elseif ($params['size'] == "") {
            $sql->disconnect();
            throw new Exception("Product size can not be blank");
        }
        $product->size = $sql->escapeString($params ['size']);
        //product price
        if (!isset ($params['price'])) {
            $sql->disconnect();
            throw new Exception("Product price is required");
        } elseif ($params['price'] == "") {
            $sql->disconnect();
            throw new Exception("Product price can not be blank");
        }
        $product->price = floatval(str_replace('$', '', $params['price']));
        //product cost
        if (!isset ($params['cost'])) {
            $sql->disconnect();
            throw new Exception("Product cost is required");
        } elseif ($params['cost'] == "") {
            $sql->disconnect();
            throw new Exception("Product cost can not be blank");
        }
        $product->cost = floatval(str_replace('$', '', $params['cost']));
        $sql->disconnect();
        return $product;
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
            throw new Exception("User not authorized to create product");
        }
        $sql = new Sql();
        $lastId = $sql->executeStatement("INSERT INTO `products` (`id`, `product_type`, `size`, `price`, `cost`) VALUES (NULL, '{$this->type->getId()}', '{$this->size}', '{$this->price}', '{$this->cost}');");
        $sql->disconnect();
        $this->id = $lastId;
        $product = self::withId($lastId);
        $this->raw = $product->getDataArray();
        return $lastId;
    }

    function update($params) {
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new Exception("User not authorized to update product");
        }
        self::setVals($this, $params);
        $sql = new Sql();
        $sql->executeStatement("UPDATE `products` SET `product_type` = '{$this->type->getId()}', `size` = '{$this->size}', `cost` = '{$this->cost}' , `price` = '{$this->price}' WHERE `products`.`id` = {$this->id};");
        $this->raw = $sql->getRow("SELECT * FROM products WHERE id = {$this->getId()};");
        $sql->disconnect();
    }

    function delete() {
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new Exception("User not authorized to delete product");
        }
        $sql = new Sql();
        $sql->executeStatement("DELETE FROM products WHERE id='{$this->id}';");
        $sql->disconnect();
    }
}