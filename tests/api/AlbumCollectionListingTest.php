<?php

namespace api;

use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__, 2) . '/src/autoloader.php';

class AlbumCollectionListingTest extends TestCase {
    private ApiTestClient $http;
    private Sql $sql;

    protected function setUp(): void {
        $this->http = new ApiTestClient([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/'
        ]);
        $this->sql = new Sql();
        foreach ([98850, 98851, 98852, 98853] as $id) {
            $this->sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM albums WHERE id = ?', [$id]);
        }
        $this->sql->executeStatement(
            "INSERT INTO albums (id, name, description, location, owner, code, images, isCollection)
             VALUES (98850, 'Mitzvah', '', '', 1, 'COLL98850', 0, 1),
                    (98853, 'Standalone', '', 'test-standalone', 1, NULL, 4, 0)"
        );
        $this->sql->executeStatement(
            "INSERT INTO albums (id, name, description, location, owner, code, images, parent, collectionOrder)
             VALUES (98851, 'Bimah', '', 'test-bimah', 1, 'CHILD98851', 7, 98850, 0),
                    (98852, 'Party', '', 'test-party', 1, 'CHILD98852', 15, 98850, 1)"
        );
    }

    protected function tearDown(): void {
        foreach ([98850, 98851, 98852, 98853] as $id) {
            $this->sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [$id]);
            $this->sql->executeStatement('DELETE FROM albums WHERE id = ?', [$id]);
        }
        $this->sql->disconnect();
    }

    private function getRows(): array {
        $cookieJar = CookieJar::fromArray([
            'hash' => '5510b5e6fffd897c234cafe499f76146'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/get-albums.php', ['cookies' => $cookieJar]);
        self::assertSame(200, $response->getStatusCode());
        return json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR)['data'];
    }

    private function ids(array $rows): array {
        return array_map('intval', array_column($rows, 'id'));
    }

    public function testParentGrantDisplaysOneCollectionInsteadOfChildren(): void {
        foreach ([98850, 98851, 98852] as $id) {
            $this->sql->executeStatement('INSERT INTO albums_for_users (user, album) VALUES (3, ?)', [$id]);
        }
        $rows = $this->getRows();
        self::assertContains(98850, $this->ids($rows));
        self::assertNotContains(98851, $this->ids($rows));
        self::assertNotContains(98852, $this->ids($rows));
        $parent = current(array_filter($rows, static fn(array $row): bool => (int)$row['id'] === 98850));
        self::assertSame(22, (int)$parent['images']);
        self::assertTrue($parent['isCollection']);
    }

    public function testChildOnlyGrantKeepsChildIndependentAndHidesParent(): void {
        $this->sql->executeStatement('INSERT INTO albums_for_users (user, album) VALUES (3, 98851)');
        $rows = $this->getRows();
        self::assertContains(98851, $this->ids($rows));
        self::assertNotContains(98850, $this->ids($rows));
        self::assertNotContains(98852, $this->ids($rows));
    }

    public function testAdminListingExposesParentWithoutDuplicatingChildren(): void {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
        $response = $this->http->request('GET', 'api/get-albums.php', ['cookies' => $cookieJar]);
        $rows = json_decode((string)$response->getBody(), true)['data'];
        self::assertContains(98850, $this->ids($rows));
        self::assertNotContains(98851, $this->ids($rows));
        self::assertNotContains(98852, $this->ids($rows));
        self::assertContains(98853, $this->ids($rows));
    }
}
