<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "autoloader.php";

class Album {
    private const SELECT_BY_ID = "SELECT * FROM albums WHERE id = ?";

    private $raw;
    private $id;
    private $name;
    private $description;
    private $date;
    private $lastAccessed;
    private $location;
    private $code;
    private $owner;
    private $images = array();
    private $thumbsCreated;
    private bool $isCollection = false;
    private ?int $parentId = null;
    private ?int $collectionOrder = null;
    private ?int $coverImage = null;
    private $users = array();

    /**
     * Album constructor.
     */
    function __construct() {
    }

    /**
     * @param $params
     * @return Album
     * @throws BadAlbumException
     */
    static function withParams($params): Album {
        return self::setVals(new Album(), $params);
    }

    /**
     * @param Album $album
     * @param $params
     * @return Album
     * @throws BadAlbumException
     */
    private static function setVals(Album $album, $params): Album {
        $sql = new Sql();
        //album name
        if (!isset ($params['name'])) {
            $sql->disconnect();
            throw new BadAlbumException("Album name is required");
        } elseif ($params['name'] == "") {
            $sql->disconnect();
            throw new BadAlbumException("Album name can not be blank");
        }
        $album->name = $params['name'];
        //album description
        if (isset ($params ['description']) && $params ['description'] != "") {
            $album->description = $params['description'];
        } else {
            $album->description = '';
        }
        // album date
        if (isset ($params ['date']) && $params ['date'] != "") {
            if (!Strings::isDateFormatted($params['date'])) {
                $sql->disconnect();
                throw new BadAlbumException("Album date is not the correct format");
            }
            $album->date = $params['date'];
        } else {
            $album->date = null;
        }
        $sql->disconnect();
        return $album;
    }

    function getName() {
        return $this->name;
    }

    public function isCollection(): bool {
        return $this->isCollection;
    }

    public function getParentId(): ?int {
        return $this->parentId;
    }

    /**
     * A collection contains albums, never photos. Returned order is the
     * persisted display order, not an incidental database or upload order.
     * @return Album[]
     */
    public function getChildren(): array {
        if (!$this->isCollection) {
            throw new BadAlbumException('Only collections have child albums');
        }
        $sql = new Sql();
        try {
            $rows = $sql->getRows(
                'SELECT id FROM albums WHERE parent = ? ORDER BY collectionOrder, id',
                [$this->id]
            );
        } finally {
            $sql->disconnect();
        }
        return array_map(static fn(array $row): Album => self::withId($row['id']), $rows);
    }

    function getDescription() {
        return $this->description;
    }

    public function getOwner() {
        return $this->owner;
    }

    public function getLocation() {
        return $this->location;
    }

    /**
     * @return bool
     */
    public function hasCode(): bool {
        return $this->code != NULL;
    }

    public function getCode() {
        return $this->code;
    }

    public function hasThumbnails(): bool {
        return (bool)$this->thumbsCreated;
    }

    public function needsThumbnails(): bool {
        return (int)$this->images > 0 && !$this->hasThumbnails();
    }

    public function hasAnyThumbnails(): bool {
        if ((int)$this->images === 0) {
            return false;
        }
        if ($this->hasThumbnails()) {
            return true;
        }

        $fullDirectory = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR . 'public'
            . DIRECTORY_SEPARATOR . 'albums'
            . DIRECTORY_SEPARATOR . $this->location
            . DIRECTORY_SEPARATOR . 'full';

        if (!is_dir($fullDirectory)) {
            return false;
        }

        $sql = new Sql();
        $images = $sql->getRows("SELECT location FROM album_images WHERE album = ?", [$this->id]);
        $sql->disconnect();

        foreach ($images as $image) {
            $filename = basename((string)($image['location'] ?? ''));
            if ($filename !== '' && is_file($fullDirectory . DIRECTORY_SEPARATOR . $filename)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Only return basic information
     * name, description, date, code
     */
    function getDataBasic(): array {
        return array_diff_key($this->raw, ['id' => '', 'lastAccessed' => '', 'location' => '', 'owner' => '', 'images' => '', 'thumbsCreated' => '']);
    }

    /**
     * @return bool
     * @throws BadUserException
     */
    function canUserAccess(): bool {
        $user = User::fromSystem();
        //(it's your own stuff) || (it's stored in your cookies) || (your user is authorized to view the album)
        if ($this->canUserGetData() || $this->isSearchedFor()
            || ($user->isLoggedIn() && in_array($user->getId(), $this->users))) {
            return true;
        }
        // Inherit viewing permission only. Download and sharing grants are
        // always resolved against the particular child album.
        return $this->parentId !== null && self::withId($this->parentId)->canUserAccess();
    }

    /**
     * @return bool
     * @throws BadUserException
     */
    function canUserGetData(): bool {
        $user = User::fromSystem();
        // only admin users and uploader users who own the album can get all data
        return ($user->isAdmin() || ($user->getRole() == "uploader" && $user->getId() == $this->owner));
    }

    /**
     * @return bool
     */
    function isSearchedFor(): bool {
        if ($this->code == NULL) {
            // if no code, can't be searched for
            return false;
        } elseif ($this->doesSessionHaveAlbumCode() || $this->doesCookieHaveAlbumCode()) {
            return true;
        } else {
            return false;
        }
    }

    private function doesSessionHaveAlbumCode(): bool {
        // if the search is stored in your session, we're good
        return isset($_SESSION ['searched'][$this->id])
            && is_string($_SESSION ['searched'][$this->id])
            && hash_equals(hash('sha256', "album" . $this->code), $_SESSION ['searched'] [$this->id]);
    }

    private function doesCookieHaveAlbumCode(): bool {
        if (!isset($_COOKIE['searched']) || !is_string($_COOKIE['searched'])) {
            return false;
        }
        $searched = json_decode($_COOKIE['searched'], true);
        return is_array($searched)
            && isset($searched[$this->id])
            && is_string($searched[$this->id])
            && hash_equals(hash('sha256', "album" . $this->code), $searched[$this->id]);
    }

    /**
     * @return int
     * @throws BadUserException
     * @throws AlbumException
     * @throws SqlException
     */
    function create(): int {
        $user = User::fromSystem();
        if (!$user->isAdmin() && $user->getRole() !== "uploader") {
            throw new AlbumException("User not authorized to create album");
        }
        $sql = new Sql();
        // generate our location for the files
        $location = preg_replace("/[^A-Za-z0-9]/", '', $this->name);
        $location = $location . "_" . time();
        $this->location = $location;
        try {
            $oldMask = umask(0);
            if (!mkdir(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'albums' . DIRECTORY_SEPARATOR . $location, 0775)) {
                throw new Exception("Mkdir failed: " . error_get_last()['message']);
            }
            umask($oldMask);
        } catch (Exception $e) {
            $sql->disconnect();
            throw new AlbumException($e->getMessage() . "<br/>Unable to create album");
        }
        $lastId = $sql->executeStatement("INSERT INTO `albums` (`name`, `description`, `date`, `location`, `owner`, `thumbsCreated`) VALUES (?, ?, ?, ?, ?, FALSE)", [$this->name, $this->description, $this->date, $this->location, $user->getId()]);
        if ($user->getRole() == "uploader") {
            $sql->executeStatement("INSERT INTO `albums_for_users` (`user`, `album`) VALUES (?, ?)", [$user->getId(), $lastId]);
            $sql->executeStatement("INSERT INTO `user_logs` (`user`, `time`, `action`, `what`, `album`) VALUES (?, CURRENT_TIMESTAMP, 'Created Album', NULL, ?)", [$user->getId(), $lastId]);
        }
        $sql->disconnect();
        $this->id = $lastId;
        $album = static::withId($lastId);
        $this->raw = $album->getDataArray();
        return $lastId;
    }


    /**
     * Resolve the selected cover if it is still in this album or one of
     * this collection's CURRENT children, otherwise fall back by display
     * order. Never return public paths without the request's permission check.
     */
    public function resolveCoverImage(): ?array {
        $sql = new Sql();
        try {
            if ($this->isCollection) {
                return $sql->getRow(
                    'SELECT ai.id, ai.album, ai.location, child.location AS albumLocation
                     FROM album_images ai
                     JOIN albums child ON child.id = ai.album
                     WHERE child.parent = ?
                     ORDER BY (ai.id = ?) DESC, child.collectionOrder, child.id,
                              ai.sequence, ai.id LIMIT 1',
                    [$this->id, $this->coverImage]
                );
            }
            return $sql->getRow(
                'SELECT ai.id, ai.album, ai.location, a.location AS albumLocation
                 FROM album_images ai JOIN albums a ON a.id = ai.album
                 WHERE ai.album = ?
                 ORDER BY (ai.id = ?) DESC, ai.sequence, ai.id LIMIT 1',
                [$this->id, $this->coverImage]
            );
        } finally {
            $sql->disconnect();
        }
    }

    /**
     * Change only the reference to an existing image. Never copy, move,
     * upload, or expose an album original while choosing a cover.
     */
    public function setCoverImage(?int $imageId): void {
        $user = User::fromSystem();
        if (!$this->canUserGetData()
            || ($this->isCollection && !$user->isAdmin() && $this->isShared())) {
            throw new AlbumException('User not authorized to change this cover');
        }
        $sql = new Sql();
        try {
            if ($imageId !== null) {
                if ($this->isCollection) {
                    $count = $sql->getRowCount(
                        'SELECT 1 FROM album_images ai JOIN albums a ON a.id = ai.album
                         WHERE ai.id = ? AND a.parent = ?',
                        [$imageId, $this->id]
                    );
                } else {
                    $count = $sql->getRowCount(
                        'SELECT 1 FROM album_images WHERE id = ? AND album = ?',
                        [$imageId, $this->id]
                    );
                }
                if ($count !== 1) {
                    throw new BadAlbumException('Cover must be an image in the album or collection');
                }
            }
            $sql->executeStatement('UPDATE albums SET coverImage = ? WHERE id = ?', [$imageId, $this->id]);
            $this->coverImage = $imageId;
        } finally {
            $sql->disconnect();
        }
    }

    /**
     * Create a non-photographic collection. No directory or image record is
     * created; album codes share the existing unique database index.
     */
    public function createCollection(): int {
        $user = User::fromSystem();
        if (!$user->isAdmin() && !$user->isUploader()) {
            throw new AlbumException('User not authorized to create collection');
        }
        $sql = new Sql();
        try {
            $id = (int)$sql->executeStatement(
                'INSERT INTO albums (name, description, date, location, owner, isCollection, images, thumbsCreated) VALUES (?, ?, ?, ?, ?, 1, 0, 0)',
                [$this->name, $this->description, $this->date, '', $user->getId()]
            );
        } finally {
            $sql->disconnect();
        }
        $this->id = $id;
        $this->isCollection = true;
        $this->raw = self::withId($id)->getDataArray();
        return $id;
    }

    /**
     * Update collection metadata without changing other albums or images.
     * Only administrators may change a code. The albums table's unique index
     * enforces uniqueness across collections and ordinary albums.
     */
    public function updateCollection(array $params): void {
        if (!$this->isCollection) {
            throw new BadAlbumException('Album is not a collection');
        }
        $user = User::fromSystem();
        if (!AlbumCollectionPolicy::canEdit(
            $user->isAdmin(), $user->isUploader(), (int)$user->getId(), (int)$this->owner
        ) || (!$user->isAdmin() && $this->isShared())) {
            throw new AlbumException('User not authorized to edit collection');
        }
        if (array_key_exists('code', $params) && !AlbumCollectionPolicy::canSetCode($user->isAdmin())) {
            throw new AlbumException('User not authorized to set collection codes');
        }
        self::setVals($this, $params);
        $code = $this->code;
        if (array_key_exists('code', $params)) {
            $code = trim((string)$params['code']);
            $code = $code === '' ? null : $code;
        }
        $sql = new Sql();
        try {
            if ($code !== null && $sql->getRowCount(
                'SELECT 1 FROM albums WHERE code = ? AND id != ?', [$code, $this->id]
            ) > 0) {
                throw new BadAlbumException('Album code already exists');
            }
            $sql->executeStatement(
                'UPDATE albums SET name = ?, description = ?, date = ?, code = ? WHERE id = ?',
                [$this->name, $this->description, $this->date, $code, $this->id]
            );
            $this->code = $code;
            $this->raw = $sql->getRow(self::SELECT_BY_ID, [$this->id]);
        } finally {
            $sql->disconnect();
        }
    }

    /**
     * A collection is shared when its code or a downloader account's explicit
     * grant makes its children accessible outside the owner/admin workflow.
     * The uploader's own albums_for_users entry is not a sharing grant.
     */
    public function isShared(): bool {
        if (!$this->isCollection) {
            throw new BadAlbumException('Only collections have sharing state');
        }
        if ($this->hasCode()) {
            return true;
        }
        $sql = new Sql();
        try {
            return $sql->getRowCount(
                "SELECT 1 FROM albums_for_users au JOIN users u ON u.id = au.user
                 WHERE au.album = ? AND u.role NOT IN ('admin', 'uploader')",
                [$this->id]
            ) > 0;
        } finally {
            $sql->disconnect();
        }
    }

    /**
     * Move an existing photo album without changing its owner, images, code
     * or image permissions. Uploaders may only touch their own unshared
     * collections; administrators may move between shared collections.
     */
    public function setParent(?int $parentId): void {
        if ($this->isCollection) {
            throw new BadAlbumException('A collection cannot belong to another collection');
        }
        $target = $parentId === null ? null : self::withId($parentId);
        if ($target !== null && !$target->isCollection()) {
            throw new BadAlbumException('Parent must be a collection');
        }
        $source = $this->parentId === null ? null : self::withId($this->parentId);
        $user = User::fromSystem();
        $sourceShared = $source !== null && $source->isShared();
        $targetShared = $target !== null && $target->isShared();
        $collectionOwner = (int)($target ?? $source)?->getOwner();
        $canChange = AlbumCollectionPolicy::canChangeMembership(
            $user->isAdmin(),
            $user->isUploader(),
            (int)$user->getId(),
            $collectionOwner,
            (int)$this->owner,
            $sourceShared,
            $targetShared
        );
        if ($source !== null && !$user->isAdmin() && (int)$source->getOwner() !== (int)$user->getId()) {
            $canChange = false;
        }
        if (!$canChange) {
            throw new AlbumException('User not authorized to organize this album');
        }
        if ($parentId === $this->parentId) {
            return;
        }
        $sql = new Sql();
        try {
            $position = null;
            if ($parentId !== null) {
                $row = $sql->getRow(
                    'SELECT COALESCE(MAX(collectionOrder), -1) + 1 AS nextPosition FROM albums WHERE parent = ?',
                    [$parentId]
                );
                $position = (int)$row['nextPosition'];
            }
            $sql->executeStatement(
                'UPDATE albums SET parent = ?, collectionOrder = ? WHERE id = ?',
                [$parentId, $position, $this->id]
            );
            $this->parentId = $parentId;
            $this->collectionOrder = $position;
        } finally {
            $sql->disconnect();
        }
    }

    /**
     * Require an exact permutation of the collection's existing children,
     * then update every position with a single atomic SQL statement.
     */
    public function reorderChildren(array $orderedIds): void {
        if (!$this->isCollection) {
            throw new BadAlbumException('Only collections can order albums');
        }
        $existing = array_map(static fn(Album $album): int => (int)$album->getId(), $this->getChildren());
        $requested = array_map('intval', $orderedIds);
        $sortedExisting = $existing;
        $sortedRequested = $requested;
        sort($sortedExisting);
        sort($sortedRequested);
        if ($sortedExisting !== $sortedRequested || count($requested) !== count(array_unique($requested))) {
            throw new BadAlbumException('Order must contain every child album exactly once');
        }
        $user = User::fromSystem();
        if (!AlbumCollectionPolicy::canEdit(
            $user->isAdmin(), $user->isUploader(),
            (int)$user->getId(), (int)$this->owner
        ) || (!$user->isAdmin() && $this->isShared())) {
            throw new AlbumException('User not authorized to reorder collection');
        }
        foreach ($this->getChildren() as $child) {
            if (!AlbumCollectionPolicy::canChangeMembership(
                $user->isAdmin(), $user->isUploader(), (int)$user->getId(),
                (int)$this->owner, (int)$child->getOwner(), $this->isShared(), $this->isShared()
            )) {
                throw new AlbumException('User not authorized to reorder collection');
            }
        }
        if ($requested === []) {
            return;
        }
        $cases = [];
        $values = [];
        foreach ($requested as $position => $id) {
            $cases[] = 'WHEN ? THEN ?';
            $values[] = $id;
            $values[] = $position;
        }
        $values[] = $this->id;
        $sql = new Sql();
        try {
            $sql->executeStatement(
                'UPDATE albums SET collectionOrder = CASE id ' . implode(' ', $cases)
                . ' END WHERE parent = ?',
                $values
            );
        } finally {
            $sql->disconnect();
        }
    }

    /**
     * @param $code
     * @return Album
     * @throws BadAlbumException
     */
    public static function withCode($code): Album {
        if (!isset($code)) {
            throw new BadAlbumException("Album code is required");
        } elseif ($code === "") {
            throw new BadAlbumException("Album code can not be blank");
        }

        $sql = new Sql();
        $row = $sql->getRow("SELECT id FROM albums WHERE BINARY code = ?", [$code]);
        $sql->disconnect();

        if (!isset($row['id'])) {
            throw new BadAlbumException("That code does not match any albums");
        }

        return static::withId($row['id']);
    }

    /**
     * @param $id
     * @return Album
     * @throws BadAlbumException
     */
    static function withId($id): Album {
        if (!isset ($id)) {
            throw new BadAlbumException("Album id is required");
        } elseif ($id == "") {
            throw new BadAlbumException("Album id can not be blank");
        }
        $album = new Album();
        $id = (int)$id;
        $sql = new Sql();
        $album->raw = $sql->getRow(self::SELECT_BY_ID, [$id]);
        if (!isset($album->raw) || !isset($album->raw['id'])) {
            $sql->disconnect();
            throw new BadAlbumException("Album id does not match any albums");
        }
        $album->id = $album->raw['id'];
        $album->name = $album->raw['name'];
        $album->description = $album->raw['description'];
        $album->date = $album->raw['date'];
        $album->lastAccessed = $album->raw['lastAccessed'];
        $album->location = $album->raw['location'];
        $album->code = $album->raw['code'];
        //consider changing this to a user class
        $album->owner = $album->raw['owner'];
        //consider changing this to an array of matching images
        $album->images = $album->raw['images'];
        $album->thumbsCreated = $album->raw['thumbsCreated'];
        $album->isCollection = (bool)$album->raw['isCollection'];
        $album->parentId = $album->raw['parent'] === null ? null : (int)$album->raw['parent'];
        $album->collectionOrder = $album->raw['collectionOrder'] === null ? null : (int)$album->raw['collectionOrder'];
        $album->coverImage = $album->raw['coverImage'] === null ? null : (int)$album->raw['coverImage'];
        $album->users = array_column($sql->getRows("SELECT user FROM albums_for_users WHERE album = ?", [$album->id]), 'user');
        $sql->disconnect();
        return $album;
    }

    function getDataArray() {
        return $this->raw;
    }

    /**
     * @param $params
     * @throws AlbumException
     * @throws BadAlbumException
     * @throws BadUserException
     * @throws SqlException
     */
    function update($params) {
        if ($this->isCollection) {
            throw new BadAlbumException('Use collection management for collections');
        }
        $user = User::fromSystem();
        if (!$this->canUserGetData()) {
            throw new AlbumException('User not authorized to update album');
        }
        if (array_key_exists('code', $params) && !$user->isAdmin()) {
            throw new AlbumException('User not authorized to set album codes');
        }
        self::setVals($this, $params);
        $sql = new Sql();
        try {
            if (array_key_exists('code', $params)) {
                $code = trim((string)$params['code']);
                $code = $code === '' ? null : $code;
                if ($code !== null && $sql->getRowCount(
                    'SELECT 1 FROM albums WHERE code = ? AND id != ?',
                    [$code, $this->getId()]
                ) > 0) {
                    throw new BadAlbumException('Album code already exists');
                }
                $sql->executeStatement(
                    'UPDATE albums SET name = ?, description = ?, date = ?, code = ? WHERE id = ?',
                    [$this->name, $this->description, $this->date, $code, $this->getId()]
                );
                $this->code = $code;
            } else {
                // Uploaders editing names/dates must not revoke an admin's
                // access code. Only an explicit admin edit can change it.
                $sql->executeStatement(
                    'UPDATE albums SET name = ?, description = ?, date = ? WHERE id = ?',
                    [$this->name, $this->description, $this->date, $this->getId()]
                );
            }
            $this->raw = $sql->getRow(self::SELECT_BY_ID, [$this->getId()]);
        } finally {
            $sql->disconnect();
        }
    }

    function getId() {
        return $this->id;
    }

    /**
     * @throws AlbumException
     * @throws SqlException
     * @throws BadUserException
     */
    function delete() {
        if (!$this->canUserGetData()) {
            throw new AlbumException("User not authorized to delete album");
        }
        if (!$this->isCollection && $this->parentId !== null && !User::fromSystem()->isAdmin()
            && self::withId($this->parentId)->isShared()) {
            throw new AlbumException('Only administrators may delete albums from shared collections');
        }
        if ($this->isCollection) {
            $user = User::fromSystem();
            if (!AlbumCollectionPolicy::canDelete(
                $user->isAdmin(), $user->isUploader(), (int)$user->getId(),
                (int)$this->owner, $this->isShared()
            )) {
                throw new AlbumException('User not authorized to delete a shared collection');
            }
            $sql = new Sql();
            try {
                // fk_album_collection_parent uses ON DELETE SET NULL. Deleting
                // the collection cannot delete its child albums or photos.
                $sql->executeStatement('DELETE FROM albums WHERE id = ?', [$this->id]);
                $sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [$this->id]);
            } finally {
                $sql->disconnect();
            }
            return;
        }
        $sql = new Sql();
        $sql->executeStatement("DELETE FROM albums WHERE id = ?", [$this->id]);
        $sql->executeStatement("DELETE FROM album_images WHERE album = ?", [$this->id]);
        $sql->executeStatement("DELETE FROM albums_for_users WHERE album = ?", [$this->id]);
        $sql->disconnect();
        if ($this->location != "") {
            system("rm -rf " . escapeshellarg(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'albums' . DIRECTORY_SEPARATOR . $this->location));
        }
    }
}
