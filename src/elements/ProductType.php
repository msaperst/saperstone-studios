<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class ProductType {

    private $raw;
    private $id;
    private $category;
    private $name;

    public function __construct() {
    }

    /**
     * @param $id
     * @return ProductType
     * @throws BadProductTypeException
     */
    static function withId($id): ProductType {
        if (!isset ($id)) {
            throw new BadProductTypeException("Product id is required");
        } elseif ($id == "") {
            throw new BadProductTypeException("Product id can not be blank");
        }
        $productType = new ProductType();
        $id = (int)$id;
        $sql = new Sql();
        $productType->raw = $sql->getRow("SELECT * FROM product_types WHERE id = $id;");
        $sql->disconnect();
        if (!isset($productType->raw) || !isset($productType->raw['id'])) {
            throw new BadProductTypeException("Product id does not match any products");
        }
        $productType->id = $productType->raw['id'];
        $productType->category = $productType->raw['category'];
        $productType->name = $productType->raw['name'];
        return $productType;
    }

    /**
     * @param $params
     * @return ProductType
     * @throws BadProductTypeException
     */
    static function withParams($params): ProductType {
        return self::setVals(new ProductType(), $params);
    }

    /**
     * @param ProductType $productType
     * @param $params
     * @return ProductType
     * @throws BadProductTypeException
     */
    private static function setVals(ProductType $productType, $params): ProductType {
        $sql = new Sql();
        //product category
        if (!isset ($params['category'])) {
            $sql->disconnect();
            throw new BadProductTypeException("Product category is required");
        } elseif ($params['category'] == "") {
            $sql->disconnect();
            throw new BadProductTypeException("Product category can not be blank");
        } elseif (!in_array($params['category'], $sql->getEnumValues('product_types', 'category'))) {
            $sql->disconnect();
            throw new BadProductTypeException ("Product category is not valid");
        }
        $productType->category = $sql->escapeString($params ['category']);
        //product name
        if (!isset ($params['name'])) {
            $sql->disconnect();
            throw new BadProductTypeException("Product name is required");
        } elseif ($params['name'] == "") {
            $sql->disconnect();
            throw new BadProductTypeException("Product name can not be blank");
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

    /**
     * @return int
     * @throws BadUserException
     * @throws ProductTypeException
     * @throws SqlException
     */
    function create(): int {
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new ProductTypeException("User not authorized to create product type");
        }
        $sql = new Sql();
        $lastId = $sql->executeStatement("INSERT INTO `product_types` (`id`, `category`, `name`) VALUES (NULL, '{$this->category}', '{$this->name}');");
        $sql->disconnect();
        $this->id = $lastId;
        $productType = static::withId($lastId);
        $this->raw = $productType->getDataArray();
        return $lastId;
    }

    /**
     * @param $params
     * @throws BadUserException
     * @throws ProductTypeException
     * @throws SqlException
     */
    function update($params) {
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new ProductTypeException("User not authorized to update product type");
        }
        self::setVals($this, $params);
        $sql = new Sql();
        $sql->executeStatement("UPDATE `product_types` SET `category` = '{$this->category}', `name` = '{$this->name}' WHERE `product_types`.`id` = {$this->id};");
        $this->raw = $sql->getRow("SELECT * FROM product_types WHERE id = {$this->getId()};");
        $sql->disconnect();
    }

    /**
     * @throws BadUserException
     * @throws BadProductTypeException
     * @throws SqlException
     */
    function delete() {
        $user = User::fromSystem();
        if (!$user->isAdmin()) {
            throw new BadProductTypeException("User not authorized to delete product type");
        }
        $sql = new Sql();
        $sql->executeStatement("DELETE FROM product_types WHERE id='{$this->id}';");
        $sql->executeStatement("DELETE FROM products WHERE product_type='{$this->id}';");
        $sql->executeStatement("DELETE FROM product_options WHERE product_type='{$this->id}';");
        $sql->disconnect();
    }
}