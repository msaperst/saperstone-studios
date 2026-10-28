<?php

namespace coverage\integration;

use Album;
use AlbumException;
use BadAlbumException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__, 3) . '/src/autoloader.php';

class AlbumCollectionIntegrationTest extends TestCase {
    private Sql $sql;
    private array $savedSession;

    protected function setUp(): void {
        $this->savedSession = $_SESSION ?? [];
        $_SESSION = [];
        $this->sql = new Sql();

        foreach ([98880, 98881, 98882, 98883, 98884] as $id) {
            $this->sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM album_images WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM albums WHERE id = ?', [$id]);
        }
        $this->sql->executeStatement(
            "INSERT INTO albums (id, name, description, location, owner, code, isCollection)
             VALUES (98880, 'Mitzvah collection', '', '', 4, 'EVENT98880', 1),
                    (98884, 'Other collection', '', '', 5, NULL, 1)"
        );
        $this->sql->executeStatement(
            "INSERT INTO albums (id, name, description, location, owner, code, parent, collectionOrder)
             VALUES (98881, 'Bimah', '', 'test-bimah', 4, 'BIMAH98881', 98880, 0),
                    (98882, 'Party', '', 'test-party', 4, NULL, 98880, 1),
                    (98883, 'Standalone', '', 'test-standalone', 5, NULL, NULL, NULL)"
        );
        $this->sql->executeStatement(
            "INSERT INTO album_images (album, title, sequence, caption, location, width, height, active)
             VALUES (98881, '', 1, '', '/albums/test-bimah/sample.jpeg', 400, 300, 1)"
        );
    }

    protected function tearDown(): void {
        foreach ([98880, 98881, 98882, 98883, 98884] as $id) {
            $this->sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM album_images WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM albums WHERE id = ?', [$id]);
        }
        $this->sql->disconnect();
        $_SESSION = $this->savedSession;
    }

    public function testCollectionIsOrganizationalAndChildrenAreOrdered(): void {
        $collection = Album::withId(98880);
        self::assertTrue($collection->isCollection());
        self::assertSame('', $collection->getLocation());
        self::assertSame([98881, 98882], array_map(
            static fn(Album $album): int => (int)$album->getId(),
            $collection->getChildren()
        ));
        self::assertFalse(Album::withId(98881)->isCollection());
        self::assertSame(98880, Album::withId(98881)->getParentId());
    }

    public function testParentCodeUnlocksChildrenButChildCodeDoesNotUnlockParent(): void {
        $_SESSION['searched'][98880] = hash('sha256', 'albumEVENT98880');
        self::assertTrue(Album::withId(98880)->canUserAccess());
        self::assertTrue(Album::withId(98881)->canUserAccess());
        self::assertTrue(Album::withId(98882)->canUserAccess());

        unset($_SESSION['searched'][98880]);
        $_SESSION['searched'][98881] = hash('sha256', 'albumBIMAH98881');
        self::assertTrue(Album::withId(98881)->canUserAccess());
        self::assertFalse(Album::withId(98880)->canUserAccess());
        self::assertFalse(Album::withId(98882)->canUserAccess());
    }

    public function testAssignedParentAccessAlsoUnlocksChildButNotDownloadRights(): void {
        $this->sql->executeStatement('INSERT INTO albums_for_users (user, album) VALUES (3, 98880)');
        $_SESSION['hash'] = '5510b5e6fffd897c234cafe499f76146';
        self::assertTrue(Album::withId(98882)->canUserAccess());
        self::assertSame(0, $this->sql->getRowCount('SELECT 1 FROM download_rights WHERE album = 98882'));
    }

    public function testCollectionDeletionPreservesImagesCodesAndDetachesChildren(): void {
        $_SESSION['hash'] = '1d7505e7f434a7713e84ba399e937191';
        Album::withId(98880)->delete();
        self::assertNull($this->sql->getRow('SELECT id FROM albums WHERE id = 98880'));
        self::assertSame(2, $this->sql->getRowCount('SELECT id FROM albums WHERE id IN (98881, 98882)'));
        self::assertNull($this->sql->getRow('SELECT parent FROM albums WHERE id = 98881')['parent']);
        self::assertSame('BIMAH98881', $this->sql->getRow('SELECT code FROM albums WHERE id = 98881')['code']);
        self::assertSame(1, $this->sql->getRowCount('SELECT id FROM album_images WHERE album = 98881'));
    }

    public function testUploaderCannotMoveChildOutOfSharedCollection(): void {
        $_SESSION['hash'] = 'c90788c0e409eac6a95f6c6360d8dbf7';
        $this->expectException(AlbumException::class);
        Album::withId(98881)->setParent(null);
    }

    public function testUploaderCannotAttachSomebodyElsesAlbum(): void {
        $this->sql->executeStatement('UPDATE albums SET code = NULL WHERE id = 98880');
        $_SESSION['hash'] = 'c90788c0e409eac6a95f6c6360d8dbf7';
        $this->expectException(AlbumException::class);
        Album::withId(98883)->setParent(98880);
    }

    public function testUploaderCanOrganizeUnsharedOwnedAlbums(): void {
        $this->sql->executeStatement('UPDATE albums SET code = NULL WHERE id = 98880');
        $_SESSION['hash'] = 'c90788c0e409eac6a95f6c6360d8dbf7';
        Album::withId(98881)->setParent(null);
        self::assertNull(Album::withId(98881)->getParentId());
        Album::withId(98881)->setParent(98880);
        self::assertSame(98880, Album::withId(98881)->getParentId());
    }

    public function testARegularAlbumCannotBecomeAParent(): void {
        $_SESSION['hash'] = '1d7505e7f434a7713e84ba399e937191';
        $this->expectException(BadAlbumException::class);
        Album::withId(98882)->setParent(98881);
    }
}
