<?php

namespace api;

use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__, 2) . '/src/autoloader.php';

class AlbumCollectionApiTest extends TestCase {
    private ApiTestClient $http;
    private Sql $sql;
    private array $created = [];

    protected function setUp(): void {
        $this->http = new ApiTestClient([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/'
        ]);
        $this->sql = new Sql();
        foreach ([98860, 98861, 98862, 98863] as $id) {
            $this->sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM album_images WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM albums WHERE id = ?', [$id]);
        }
        $this->sql->executeStatement(
            "INSERT INTO albums (id, name, description, location, owner, isCollection, code)
             VALUES (98860, 'Mitzvah collection', '', '', 4, 1, 'EVENT98860'),
                    (98863, 'Other collection', '', '', 5, 1, NULL)"
        );
        $this->sql->executeStatement(
            "INSERT INTO albums (id, name, description, location, owner, code, parent, collectionOrder)
             VALUES (98861, 'Bimah', '', 'api-bimah', 4, 'BIMAH98861', 98860, 0),
                    (98862, 'Party', '', 'api-party', 4, NULL, 98860, 1)"
        );
    }

    protected function tearDown(): void {
        foreach (array_merge($this->created, [98860, 98861, 98862, 98863]) as $id) {
            $this->sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM album_images WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM albums WHERE id = ?', [$id]);
        }
        $this->sql->disconnect();
    }

    private function role(string $role): CookieJar {
        $hashes = [
            'admin' => '1d7505e7f434a7713e84ba399e937191',
            'uploader' => 'c90788c0e409eac6a95f6c6360d8dbf7',
            'client' => '5510b5e6fffd897c234cafe499f76146',
        ];
        return CookieJar::fromArray(['hash' => $hashes[$role]], getenv('DB_HOST'));
    }

    public function testOnlyAdminAndUploaderCanCreateCollections(): void {
        foreach (['admin', 'uploader'] as $role) {
            $response = $this->http->request('POST', 'api/create-collection.php', [
                'cookies' => $this->role($role),
                'form_params' => ['name' => 'New grouping']
            ]);
            self::assertSame(200, $response->getStatusCode());
            $id = (int)(string)$response->getBody();
            self::assertGreaterThan(0, $id);
            $this->created[] = $id;
            $album = $this->sql->getRow('SELECT isCollection, location, images, owner FROM albums WHERE id = ?', [$id]);
            self::assertSame(1, (int)$album['isCollection']);
            self::assertSame('', $album['location']);
            self::assertSame(0, (int)$album['images']);
            self::assertSame($role === 'admin' ? 1 : 4, (int)$album['owner']);
        }
        $denied = $this->http->request('POST', 'api/create-collection.php', [
            'cookies' => $this->role('client'), 'form_params' => ['name' => 'Denied'],
            'http_errors' => false
        ]);
        self::assertSame(403, $denied->getStatusCode());
    }

    public function testCreateCollectionRequiresNameAndPost(): void {
        self::assertSame(405, $this->http->request('GET', 'api/create-collection.php')->getStatusCode());
        $response = $this->http->request('POST', 'api/create-collection.php', [
            'cookies' => $this->role('admin')
        ]);
        self::assertSame(400, $response->getStatusCode());
    }

    public function testCollectionReadRequiresViewingAuthorization(): void {
        $denied = $this->http->request('GET', 'api/get-collection.php?id=98860', ['http_errors' => false]);
        self::assertSame(403, $denied->getStatusCode());

        $response = $this->http->request('GET', 'api/get-collection.php?id=98860', [
            'cookies' => $this->role('admin')
        ]);
        self::assertSame(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Mitzvah collection', $data['name']);
        self::assertSame([98861, 98862], array_map(
            static fn(array $row): int => (int)$row['id'], $data['children']
        ));
    }

    public function testManagerSeesThumbnailStatusForEveryChildWithoutChangingTheImageCounts(): void {
        $this->sql->executeStatement('UPDATE albums SET images = 7, thumbsCreated = 1 WHERE id = 98861');
        $this->sql->executeStatement('UPDATE albums SET images = 131, thumbsCreated = 0 WHERE id = 98862');
        $response = $this->http->request('GET', 'api/get-collection.php?id=98860', [
            'cookies' => $this->role('admin')
        ]);
        self::assertSame(200, $response->getStatusCode());
        $children = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR)['children'];
        self::assertSame(7, $children[0]['imageCount']);
        self::assertTrue($children[0]['thumbsCreated']);
        self::assertSame(131, $children[1]['imageCount']);
        self::assertFalse($children[1]['thumbsCreated']);
    }

    public function testClientCannotRetrieveInternalThumbnailStatusFromCollectionApi(): void {
        $this->sql->executeStatement('INSERT INTO albums_for_users (user, album) VALUES (3, 98860)');
        $response = $this->http->request('GET', 'api/get-collection.php?id=98860', [
            'cookies' => $this->role('client')
        ]);
        self::assertSame(200, $response->getStatusCode());
        $children = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR)['children'];
        self::assertArrayNotHasKey('thumbsCreated', $children[0]);
        self::assertArrayNotHasKey('thumbsCreated', $children[1]);
    }

    public function testUploaderCanReadCollectionStructureButNotItsAccessCode(): void {
        $response = $this->http->request('GET', 'api/get-collection.php?id=98860', [
            'cookies' => $this->role('uploader')
        ]);
        self::assertSame(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertTrue($data['shared']);
        self::assertArrayNotHasKey('code', $data);

        $admin = $this->http->request('GET', 'api/get-collection.php?id=98860', [
            'cookies' => $this->role('admin')
        ]);
        $adminData = json_decode((string)$admin->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('EVENT98860', $adminData['code']);
    }

    public function testUploaderCannotModifySharedCollectionMembership(): void {
        $response = $this->http->request('POST', 'api/set-album-parent.php', [
            'cookies' => $this->role('uploader'),
            'form_params' => ['album' => 98861, 'parent' => ''],
            'http_errors' => false
        ]);
        self::assertSame(403, $response->getStatusCode());
        self::assertSame(98860, (int)$this->sql->getRow('SELECT parent FROM albums WHERE id = 98861')['parent']);
    }

    public function testUploaderCanOrganizeOwnedUnsharedCollection(): void {
        $this->sql->executeStatement('UPDATE albums SET code = NULL WHERE id = 98860');
        $response = $this->http->request('POST', 'api/set-album-parent.php', [
            'cookies' => $this->role('uploader'),
            'form_params' => ['album' => 98861, 'parent' => '']
        ]);
        self::assertSame(200, $response->getStatusCode());
        self::assertNull($this->sql->getRow('SELECT parent FROM albums WHERE id = 98861')['parent']);
    }

    public function testAdminCanMoveChildBetweenCollections(): void {
        $response = $this->http->request('POST', 'api/set-album-parent.php', [
            'cookies' => $this->role('admin'),
            'form_params' => ['album' => 98861, 'parent' => 98863]
        ]);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(98863, (int)$this->sql->getRow('SELECT parent FROM albums WHERE id = 98861')['parent']);
        self::assertSame('BIMAH98861', $this->sql->getRow('SELECT code FROM albums WHERE id = 98861')['code']);
    }

    public function testReorderMustContainAllAndOnlyTheChildren(): void {
        $invalid = $this->http->request('POST', 'api/reorder-collection.php', [
            'cookies' => $this->role('admin'),
            'form_params' => ['collection' => 98860, 'order' => json_encode([98861, 98861])]
        ]);
        self::assertSame(400, $invalid->getStatusCode());

        $valid = $this->http->request('POST', 'api/reorder-collection.php', [
            'cookies' => $this->role('admin'),
            'form_params' => ['collection' => 98860, 'order' => json_encode([98862, 98861])]
        ]);
        self::assertSame(200, $valid->getStatusCode());
        self::assertSame([98862, 98861], array_map(
            'intval', array_column($this->sql->getRows(
                'SELECT id FROM albums WHERE parent = 98860 ORDER BY collectionOrder'
            ), 'id')
        ));
    }

    public function testUnauthorizedReorderOfEmptyCollectionIsForbidden(): void {
        $this->sql->executeStatement('UPDATE albums SET parent = NULL, collectionOrder = NULL WHERE parent = 98863');
        $response = $this->http->request('POST', 'api/reorder-collection.php', [
            'cookies' => $this->role('client'),
            'form_params' => ['collection' => 98863, 'order' => '[]'],
            'http_errors' => false
        ]);
        self::assertSame(403, $response->getStatusCode());
    }

    public function testUnauthorizedNoopMoveIsForbidden(): void {
        $response = $this->http->request('POST', 'api/set-album-parent.php', [
            'cookies' => $this->role('client'),
            'form_params' => ['album' => 98861, 'parent' => 98860],
            'http_errors' => false
        ]);
        self::assertSame(403, $response->getStatusCode());
    }

    public function testCodeCollisionDoesNotReplaceExistingCollectionCode(): void {
        $response = $this->http->request('POST', 'api/update-collection.php', [
            'cookies' => $this->role('admin'),
            'form_params' => ['id' => 98860, 'name' => 'Mitzvah collection', 'code' => 'BIMAH98861']
        ]);
        self::assertSame(400, $response->getStatusCode());
        self::assertSame('EVENT98860', $this->sql->getRow('SELECT code FROM albums WHERE id = 98860')['code']);
    }

    public function testUploaderCannotSetCollectionCode(): void {
        $this->sql->executeStatement('UPDATE albums SET code = NULL WHERE id = 98860');
        $response = $this->http->request('POST', 'api/update-collection.php', [
            'cookies' => $this->role('uploader'),
            'form_params' => ['id' => 98860, 'name' => 'New Name', 'code' => 'SECRET'],
            'http_errors' => false
        ]);
        self::assertSame(403, $response->getStatusCode());
        self::assertNull($this->sql->getRow('SELECT code FROM albums WHERE id = 98860')['code']);
    }

    public function testCollectionCannotBeUsedAsPhotoUploadOrThumbnailTarget(): void {
        foreach (['upload-album-images.php', 'make-thumbs.php'] as $path) {
            $response = $this->http->request('POST', 'api/' . $path, [
                'cookies' => $this->role('admin'),
                'form_params' => ['album' => 98860, 'id' => 98860, 'markup' => 'none'],
                'http_errors' => false,
            ]);
            self::assertSame(400, $response->getStatusCode(), $path);
        }
        self::assertSame(0, $this->sql->getRowCount(
            'SELECT id FROM album_images WHERE album = 98860'
        ));
    }

    public function testProtectedCoverCannotBeReadThroughCollectionCodeWithoutChildViewingAccess(): void {
        $response = $this->http->request('GET', 'api/get-album-cover.php?album=98861', [
            'http_errors' => false
        ]);
        self::assertSame(403, $response->getStatusCode());
    }

    public function testMutationEndpointsRejectMissingCsrfProtection(): void {
        $response = $this->http->request('POST', 'api/delete-collection.php', [
            'cookies' => $this->role('admin'),
            'headers' => ['Origin' => 'https://attacker.example', 'X-CSRF-Token' => 'invalid'],
            'form_params' => ['id' => 98860],
            'http_errors' => false
        ]);
        self::assertSame(403, $response->getStatusCode());
        self::assertNotNull($this->sql->getRow('SELECT id FROM albums WHERE id = 98860'));
    }

    public function testOnlyAnIndividualAlbumCanChooseItsExistingCoverImage(): void {
        $this->sql->executeStatement(
            "INSERT INTO album_images (album, title, sequence, caption, location, width, height, active)
             VALUES (98861, '', 1, '', '/albums/api-bimah/test.jpg', 400, 300, 1)"
        );
        $imageId = (int)$this->sql->getRow(
            'SELECT id FROM album_images WHERE album = 98861 ORDER BY id DESC LIMIT 1'
        )['id'];
        $response = $this->http->request('POST', 'api/set-album-cover.php', [
            'cookies' => $this->role('admin'),
            'form_params' => ['album' => 98861, 'image' => $imageId]
        ]);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame($imageId, (int)$this->sql->getRow(
            'SELECT coverImage FROM albums WHERE id = 98861'
        )['coverImage']);
        foreach ([98860, 98862] as $otherAlbum) {
            $invalid = $this->http->request('POST', 'api/set-album-cover.php', [
                'cookies' => $this->role('admin'),
                'form_params' => ['album' => $otherAlbum, 'image' => $imageId]
            ]);
            self::assertSame(400, $invalid->getStatusCode());
        }
        $denied = $this->http->request('POST', 'api/set-album-cover.php', [
            'cookies' => $this->role('client'),
            'form_params' => ['album' => 98861, 'image' => $imageId],
            'http_errors' => false
        ]);
        self::assertSame(403, $denied->getStatusCode());
        $this->sql->executeStatement('DELETE FROM album_images WHERE id = ?', [$imageId]);
    }

    public function testCoverCandidatesAreAlbumOnlyManagerOnlyAndPaginated(): void {
        $this->sql->executeStatement(
            "INSERT INTO album_images (album, title, sequence, caption, location, width, height, active)
             VALUES (98861, 'First', 1, '', '/albums/api-bimah/first.jpg', 400, 300, 1),
                    (98861, 'Second', 2, '', '/albums/api-bimah/second.jpg', 400, 300, 1)"
        );
        $response = $this->http->request('GET', 'api/get-cover-candidates.php?album=98861&start=0&count=1', [
            'cookies' => $this->role('admin')
        ]);
        self::assertSame(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $data['candidates']);
        self::assertSame(1, $data['nextStart']);
        self::assertStringContainsString('/api/get-album-cover.php?album=98861&candidate=', $data['candidates'][0]['preview']);
        self::assertStringNotContainsString('/albums/api-bimah/', (string)$response->getBody());
        $parent = $this->http->request('GET', 'api/get-cover-candidates.php?album=98860', [
            'cookies' => $this->role('admin')
        ]);
        self::assertSame(400, $parent->getStatusCode());
        $denied = $this->http->request('GET', 'api/get-cover-candidates.php?album=98861', [
            'cookies' => $this->role('client'),
            'http_errors' => false
        ]);
        self::assertSame(403, $denied->getStatusCode());
        $this->sql->executeStatement('DELETE FROM album_images WHERE album = 98861');
    }

    public function testUnauthorizedCandidateCannotAccessProtectedThumbnail(): void {
        $denied = $this->http->request('GET', 'api/get-album-cover.php?album=98861&candidate=12345', [
            'http_errors' => false
        ]);
        self::assertSame(403, $denied->getStatusCode());
    }

    public function testDeleteCollectionNeverDeletesChildAlbums(): void {
        $response = $this->http->request('POST', 'api/delete-collection.php', [
            'cookies' => $this->role('admin'),
            'form_params' => ['id' => 98860]
        ]);
        self::assertSame(200, $response->getStatusCode());
        self::assertNull($this->sql->getRow('SELECT id FROM albums WHERE id = 98860'));
        self::assertSame(2, $this->sql->getRowCount('SELECT id FROM albums WHERE id IN (98861, 98862)'));
        self::assertNull($this->sql->getRow('SELECT parent FROM albums WHERE id = 98861')['parent']);
    }
}
