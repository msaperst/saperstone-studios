<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class Image {

    /**
     * @var array|null
     */
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

    /**
     * Image constructor.
     * @param $container
     * @param $sequence
     * @throws BadImageException
     */
    function __construct($container, $sequence) {
        if (!isset ($sequence)) {
            throw new BadImageException("Image id is required");
        } elseif ($sequence == "") {
            throw new BadImageException("Image id can not be blank");
        }
        $sql = new Sql();
        $sequence = (int)$sequence;
        if ($container instanceof Album) {
            $this->raw = $sql->getRow("SELECT * FROM album_images WHERE album = {$container->getId()} AND sequence = $sequence;");
            if (!isset($this->raw) || !isset($this->raw['id'])) {
                $sql->disconnect();
                throw new BadImageException("Image id does not match any images");
            }
            $this->album = $this->raw['album'];
        } elseif ($container instanceof Gallery) {
            $this->raw = $sql->getRow("SELECT * FROM gallery_images WHERE gallery = {$container->getId()} AND id = $sequence;");
            if (!isset($this->raw) || !isset($this->raw['id'])) {
                $sql->disconnect();
                throw new BadImageException("Image id does not match any images");
            }
            $this->gallery = $this->raw['gallery'];
        } else {
            $sql->disconnect();
            throw new BadImageException("Parent (album or gallery) is required");
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

    function getTitle() {
        return $this->title;
    }

    function getLocation() {
        return $this->location;
    }

    function getDataArray(): ?array {
        return $this->raw;
    }

    /**
     * @return bool
     * @throws BadAlbumException
     * @throws BadUserException
     */
    function canUserGetData(): bool {
        $user = User::fromSystem();
        if ($this->album != NULL) {
            $album = Album::withId($this->album);
            // only admin users and uploader users who own the album can get all data
            return ($user->isAdmin() || (NULL != $this->album && $user->getRole() == "uploader" && $user->getId() == $album->getOwner()));
        } else {
            return $user->isAdmin();
        }
    }

    /**
     * @throws BadAlbumException
     * @throws BadUserException
     * @throws ImageException
     * @throws SqlException
     */
    function delete() {
        if (!$this->canUserGetData()) {
            throw new ImageException("User not authorized to delete image");
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
            system("rm -f " . escapeshellarg(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'public' . implode("/", $parts)));
        }
    }
}