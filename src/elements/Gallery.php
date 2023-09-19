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
    private $children = array();

    function __construct() {
    }

    /**
     * @param $id
     * @return Gallery
     * @throws BadGalleryException
     */
    static function withId($id): Gallery {
        if (!isset ($id)) {
            throw new BadGalleryException("Gallery id is required");
        } elseif ($id === "") {
            throw new BadGalleryException("Gallery id can not be blank");
        }
        $gallery = new Gallery();
        $sql = new Sql();
        $id = (int)$id;
        $gallery->raw = $sql->getRow("SELECT * FROM galleries WHERE id = $id;");
        if (!isset($gallery->raw) || !isset($gallery->raw['id'])) {
            $sql->disconnect();
            throw new BadGalleryException("Gallery id does not match any galleries");
        }
        $gallery = self::setGalleryData($gallery);
        $sql->disconnect();
        return $gallery;
    }

    /**
     * @param $title
     * @return Gallery
     * @throws BadGalleryException
     */
    static function withTitle($title): Gallery {
        if (!isset($title)) {
            throw new BadGalleryException("Gallery title is required");
        } elseif ($title === "") {
            throw new BadGalleryException("Gallery title can not be blank");
        }
        $gallery = new Gallery();
        $sql = new Sql();
        $title = (string)$title;
        $gallery->raw = $sql->getRow("SELECT * FROM galleries WHERE title = " . $sql->escapeString($title) . ";");
        if (!isset($gallery->raw) || !isset($gallery->raw['id'])) {
            $sql->disconnect();
            throw new BadGalleryException("Gallery title does not match any galleries");
        }
        $gallery = self::setGalleryData($gallery);
        $sql->disconnect();
        return $gallery;
    }

    private static function setGalleryData($gallery): Gallery {
        $gallery->id = $gallery->raw['id'];
        $gallery->parent = $gallery->raw['parent'];
        if ($gallery->parent != null) {
            $gallery->parent = Gallery::withId($gallery->parent);
        }
        $gallery->image = $gallery->raw['image'];
        $gallery->title = $gallery->raw['title'];
        $gallery->comment = $gallery->raw['comment'];
        //consider changing this to an array of matching images
        $gallery->images = array();
        //get children if any
        $sql = new Sql();
        $children = $sql->getRows(
            "SELECT * FROM `galleries` WHERE parent = '" . $gallery->id . "' AND title != 'Product';"
        );
        $gallery->children = $children;
        $sql->disconnect();
        return $gallery;
    }

    /**
     * @param $params
     * @throws GalleryException
     */
    static function withParams($params) {
        throw new GalleryException('Not yet implemented: ' . json_encode($params));
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

    function hasChildren() {
        return sizeof($this->children) != 0;
    }

    /**
     * @return Gallery[]
     * @throws BadGalleryException
     */
    function getChildren(): array {
        $children = array();
        for ($i = 0; $i < count($this->children); $i++) {
            $child = $this->children[$i];
            $gallery = Gallery::withId($child['id']);
            $children[] = $gallery;
        }
        return $children;
    }

    /**
     * @return string
     */
    function getNav(): string {
        $nav = $this->getTitle();
        $mostParent = $this->parent;
        while ($mostParent != null) {
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

    /**
     * @param $nav
     * @return bool
     */
    private function crumbsHasProduct($nav): bool {
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

    /**
     * @return string
     */
    function getImageLocation(): string {
        $location = $this->title . DIRECTORY_SEPARATOR;
        $mostParent = $this->parent;
        while ($mostParent != null) {
            if ($mostParent->parent == null) {
                $location = 'img' . DIRECTORY_SEPARATOR . $location;
            }
            $location = $mostParent->getTitle() . DIRECTORY_SEPARATOR . $location;
            $mostParent = $mostParent->parent;
        }
        if ($location == $this->title . DIRECTORY_SEPARATOR) {
            $location = "img/main/" . $location;
        }
        return DIRECTORY_SEPARATOR . str_replace("'", "-", str_replace(" ", "-", strtolower($location)));
    }

    /**
     * @param $params
     * @throws SqlException
     */
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
