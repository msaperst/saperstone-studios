-- Collections are albums with a distinct, non-photographic type. Using the
-- same table preserves the existing database-enforced unique code namespace.
-- Only a regular album can reference a parent; enforce that in the model/API.
ALTER TABLE albums
    ADD COLUMN isCollection TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN parent INT(11) NULL DEFAULT NULL,
    ADD COLUMN collectionOrder INT(11) NULL DEFAULT NULL,
    ADD COLUMN coverImage INT(11) NULL DEFAULT NULL;

CREATE INDEX idx_album_collection_order ON albums (parent, collectionOrder, id);

-- A parent deletion can never cascade-delete child albums.
ALTER TABLE albums
    ADD CONSTRAINT fk_album_collection_parent
    FOREIGN KEY (parent) REFERENCES albums(id) ON DELETE SET NULL;
