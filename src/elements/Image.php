<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class Image {

    private $raw;
    private $id;
    private $album = NULL;
    private $gallery = NULL;
    private $title;
    private $sequence;
    private $caption;
    private $location;
    private $width;
    private $height;
    private $active;

    function __construct($container, $sequence) {
        if (!isset ($sequence)) {
            throw new Exception("Image id is required");
        } elseif ($sequence == "") {
            throw new Exception("Image id can not be blank");
        }
        $sql = new Sql();
        $sequence = (int)$sequence;
        if ($container instanceof Album) {
            $this->raw = $sql->getRow("SELECT * FROM album_images WHERE album = {$container->getId()} AND sequence = $sequence;");
            $this->album = $this->raw['album'];
        } elseif ($container instanceof Gallery) {
            $this->raw = $sql->getRow("SELECT * FROM gallery_images WHERE gallery = {$container->getId()} AND id = $sequence;");  //TODO should align the JS on this and albums
            $this->gallery = $this->raw['gallery'];
        } else {
            $sql->disconnect();
            throw new Exception("Parent (album or gallery) is required");
        }
        if (!$this->raw ['id']) {
            $sql->disconnect();
            throw new Exception("Image id does not match any images");
        }
        $this->id = $this->raw['id'];
        $this->title = $this->raw['title'];
        $this->sequence = $this->raw['sequence'];
        $this->caption = $this->raw['caption'];
        $this->location = $this->raw['location'];
        $this->width = $this->raw['width'];
        $this->height = $this->raw['height'];
        $this->active = $this->raw['active'];
        $sql->disconnect();
    }

    function getId() {
        return $this->id;
    }

    function getDataArray() {
        return $this->raw;
    }

    function canUserGetData() {
        $user = User::fromSystem();
        if ($this->album != NULL) {
            $album = new Album($this->album);
            // only admin users and uploader users who own the album can get all data
            return ($user->isAdmin() || (NULL != $this->album && $user->getRole() == "uploader" && $user->getId() == $album->getOwner()));
        } else {
            return $user->isAdmin();
        }
    }

    function delete() {
        if (!$this->canUserGetData()) {
            throw new Exception("User not authorized to delete image");
        }
        $sql = new Sql();
        if ($this->album != NULL) {
            // if we're in an album, delete from the table
            $sql->executeStatement("DELETE FROM album_images WHERE album='{$this->album}' AND id='{$this->getId()}';");
            $sql->executeStatement("UPDATE albums SET images = images - 1 WHERE id='{$this->album}';");
            // need to re-sequence images in mysql table
            $sql->executeStatement("SET @seq:=-1;");
            $sql->executeStatement("UPDATE album_images SET sequence=(@seq:=@seq+1) WHERE album='{$this->album}' ORDER BY `sequence`;");
        }
        if ($this->gallery != NULL) {
            // if we're in a gallery, delete from the table
            $sql->executeStatement("DELETE FROM gallery_images WHERE gallery='{$this->gallery}' AND id='{$this->getId()}';");
            // need to re-sequence images in mysql table
            $sql->executeStatement("SET @seq:=-1;");
            $sql->executeStatement("UPDATE gallery_images SET sequence=(@seq:=@seq+1) WHERE gallery='{$this->gallery}' ORDER BY `sequence`;");
        }
        $sql->disconnect();

        // remove the file
        if ($this->location != "") {
            system("rm -f " . escapeshellarg(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . $this->location));
            $parts = explode("/", $this->location);
            array_splice($parts, count($parts) - 1, 0, "full");
            system("rm -f " . escapeshellarg(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public'. implode("/", $parts)));
        }
    }
}