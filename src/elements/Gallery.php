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

    function __construct($id) {
        if (!isset ($id)) {
            throw new Exception("Gallery id is required");
        } elseif ($id == "") {
            throw new Exception("Gallery id can not be blank");
        }
        $sql = new Sql();
        $id = (int)$id;
        $this->raw = $sql->getRow("SELECT * FROM galleries WHERE id = $id;");
        if (!$this->raw ['id']) {
            $sql->disconnect();
            throw new Exception("Gallery id does not match any galleries");
        }
        $this->id = $this->raw['id'];
        $this->parent = $this->raw['parent'];
        if ($this->parent != NULL) {
            $this->parent = new Gallery($this->parent);
        }
        $this->image = $this->raw['image'];
        $this->title = $this->raw['title'];
        $this->comment = $this->raw['comment'];
        $this->images = array();    //TODO - change this to an array of matching images
        $sql->disconnect();
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
}