<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class Gallery {

    private $raw;
    private $id;
    private $parent;
    private $image;
    private $title;
    private $comment;
    private $images = array();

    function __construct() {
    }

    static function withId($id) {
        if (!isset ($id)) {
            throw new Exception("Gallery id is required");
        } elseif ($id == "") {
            throw new Exception("Gallery id can not be blank");
        }
        $gallery = new Gallery();
        $sql = new Sql();
        $id = (int)$id;
        $gallery->raw = $sql->getRow("SELECT * FROM galleries WHERE id = $id;");
        if (!$gallery->raw ['id']) {
            $sql->disconnect();
            throw new Exception("Gallery id does not match any galleries");
        }
        $gallery->id = $gallery->raw['id'];
        $gallery->parent = $gallery->raw['parent'];
        if ($gallery->parent != NULL) {
            $gallery->parent = Gallery::withId($gallery->parent);
        }
        $gallery->image = $gallery->raw['image'];
        $gallery->title = $gallery->raw['title'];
        $gallery->comment = $gallery->raw['comment'];
        $gallery->images = array();    //TODO - change this to an array of matching images
        $sql->disconnect();
        return $gallery;
    }

    static function withParams($params) {
        throw new Exception('Not yet implemented');
    }

    function getId() {
        return $this->id;
    }

    function getDataArray() {
        return $this->raw;
    }

    function getParent() {
        return $this->parent;
    }

    function update($params) {
        $sql = new Sql();
        if (isset ($params ['title'])) {
            $this->title = $sql->escapeString($params ['title']);
        }
        $sql->executeStatement("UPDATE galleries SET title='{$this->title}' WHERE id='{$this->id}';");
        $this->raw = $sql->getRow("SELECT * FROM galleries WHERE id = {$this->id};");
        $sql->disconnect();
    }
}