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
        } elseif ($id === "") {
            throw new Exception("Gallery id can not be blank");
        }
        $gallery = new Gallery();
        $sql = new Sql();
        $id = (int)$id;
        $gallery->raw = $sql->getRow("SELECT * FROM galleries WHERE id = $id;");
        if (!isset($gallery->raw) || !isset($gallery->raw['id'])) {
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

    function getTitle() {
        return $this->title;
    }

    function getComment() {
        return $this->comment;
    }

    function getImage() {
        return $this->image;
    }

    function getNav() {
        $nav = $this->getTitle();
        $mostParent = $this->parent;
        while ($mostParent != NULL) {
            $nav = $mostParent->getTitle();
            $mostParent = $mostParent->parent;
        }
        return strtolower($nav);
    }

    function getBreadcrumbs() {
        $nav[0] = ['title' => $this->getTitle(), 'link' => 'gallery.php?w=' . $this->id];
        $mostParent = $this->parent;
        while ($mostParent != NULL) {
            $nav[] = ['title' => $mostParent->getTitle(), 'link' => 'gallery.php?w=' . $mostParent->getId()];
            $mostParent = $mostParent->parent;
        }
        for ($i = sizeof($nav) - 1; $i >= 0; $i--) {
            if ($nav[$i]['title'] == 'Product') {
                $crumbs[] = ['title' => 'Products', 'link' => 'products.php'];
                $crumbs[] = ['title' => 'Gallery', 'link' => $nav[sizeof($nav) - 2]['link']];
            } else {
                $crumbs[] = ['title' => $nav[$i]['title'], 'link' => $nav[$i]['link']];
            }
            if ($i == sizeof($nav) - 1 && $this->crumbsHasProduct($nav)) {
                $crumbs[] = ['title' => 'Services', 'link' => 'details.php'];
            }
            if ($i == sizeof($nav) - 1 && !$this->crumbsHasProduct($nav)) {
                $crumbs[] = ['title' => 'Gallery', 'link' => $nav[sizeof($nav) - 1]['link']];
            }
        }
        $crumbs[0]['link'] = 'index.php';
        $crumbs[sizeof($crumbs) - 1]['link'] = '';
        return $crumbs;
    }

    private function crumbsHasProduct($nav) {
        foreach ($nav as $n) {
            if ($n['title'] == 'Product') {
                return true;
            }
        }
        return false;
    }

    function getDataArray() {
        return $this->raw;
    }

    function getParent(): Gallery {
        return $this->parent;
    }

    function getImageLocation() {
        $location = $this->title . DIRECTORY_SEPARATOR;
        $mostParent = $this->parent;
        while ($mostParent != NULL) {
            if ($mostParent->parent == NULL) {
                $location = 'img' . DIRECTORY_SEPARATOR . $location;
            }
            $location = $mostParent->getTitle() . DIRECTORY_SEPARATOR . $location;
            $mostParent = $mostParent->parent;
        }
        if ($location == $this->title . DIRECTORY_SEPARATOR) {
            $location = "img/main/" . $location;
        }
        return DIRECTORY_SEPARATOR . str_replace(" ", "-", strtolower($location));
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