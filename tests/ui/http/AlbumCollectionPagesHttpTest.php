<?php

namespace ui\http;

require_once __DIR__ . '/HttpTestBase.php';
require_once dirname(__DIR__, 3) . '/src/autoloader.php';

use GuzzleHttp\Cookie\SetCookie;
use Sql;

class AlbumCollectionPagesHttpTest extends HttpTestBase {
    private Sql $sql;

    protected function setUp(): void {
        parent::setUp();
        $this->sql = new Sql();
        foreach ([98870, 98871] as $id) {
            $this->sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM album_images WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM albums WHERE id = ?', [$id]);
        }
        $this->sql->executeStatement(
            "INSERT INTO albums (id, name, description, location, owner, code, isCollection)
             VALUES (98870, 'Family Event', 'A special day', '', 1, 'EVENT98870', 1)"
        );
        $this->sql->executeStatement(
            "INSERT INTO albums (id, name, description, location, owner, code, parent, collectionOrder)
             VALUES (98871, 'Celebration', 'Reception', 'collection-test', 1, 'CHILD98871', 98870, 0)"
        );
    }

    protected function tearDown(): void {
        foreach ([98870, 98871] as $id) {
            $this->sql->executeStatement('DELETE FROM user_logs WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM album_images WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM albums WHERE id = ?', [$id]);
        }
        $this->sql->disconnect();
    }

    private function grantCode(int $id, string $code): void {
        $this->cookies->setCookie(new SetCookie([
            'Name' => 'searched',
            'Value' => json_encode([$id => hash('sha256', 'album' . $code)]),
            'Domain' => getenv('APP_URL'),
            'Path' => '/',
        ]));
    }

    public function testParentUsesSiteHeaderBreadcrumbsAndProtectedChildCards(): void {
        $this->adminLogin();
        $response = $this->get('user/collection.php?collection=98870');
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(1, $this->elementCount($response, '//nav'));
        self::assertSame(1, $this->elementCount($response, '//h1[contains(., "Family Event")]'));
        self::assertSame(1, $this->elementCount($response, "//ol[contains(@class,'breadcrumb')]"));
        self::assertSame(1, $this->elementCount(
            $response, "//*[@data-child-album-id='98871']"
        ));
        self::assertSame(1, $this->elementCount(
            $response, "//a[@href='/user/album.php?album=98871']"
        ));
        self::assertStringContainsString(
            '/api/get-album-cover.php?album=98871',
            (string)$response->getBody()
        );
        self::assertStringNotContainsString('<img src="/albums/', (string)$response->getBody());
    }

    public function testChildPageShowsParentBreadcrumbOnlyWithParentAccess(): void {
        $this->grantCode(98870, 'EVENT98870');
        $response = $this->get('user/album.php?album=98871');
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(1, $this->elementCount(
            $response, "//ol[contains(@class,'breadcrumb')]//a[@href='collection.php?collection=98870']"
        ));
        self::assertSame(1, $this->elementCount($response, "//*[@id='album-grid']"));
    }

    public function testChildOnlyCodeCannotRevealParentOrItsUrl(): void {
        $this->grantCode(98871, 'CHILD98871');
        $response = $this->get('user/album.php?album=98871');
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(0, $this->elementCount(
            $response, "//ol[contains(@class,'breadcrumb')]//a[contains(@href,'collection.php')]"
        ));
        self::assertStringNotContainsString('Family Event', (string)$response->getBody());

        $parentResponse = $this->get('user/collection.php?collection=98870');
        self::assertSame(401, $parentResponse->getStatusCode());
    }

    public function testAlbumPageRedirectsCollectionIdsToTheirDistinctUrl(): void {
        $this->adminLogin();
        $response = $this->get('user/album.php?album=98870', ['allow_redirects' => false]);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/user/collection.php?collection=98870', $response->getHeaderLine('Location'));
    }

    public function testMissingOrNonCollectionParentIsRejected(): void {
        $this->adminLogin();
        foreach (['user/collection.php?collection=98871', 'user/collection.php?collection=99900'] as $path) {
            self::assertSame(404, $this->get($path)->getStatusCode());
        }
    }

    public function testEmptyCollectionExplainsThatNoGalleriesExist(): void {
        $this->sql->executeStatement('UPDATE albums SET parent = NULL, collectionOrder = NULL WHERE id = 98871');
        $this->adminLogin();
        $response = $this->get('user/collection.php?collection=98870');
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(1, $this->elementCount($response, "//*[@id='collection-empty-state']"));
    }
}
