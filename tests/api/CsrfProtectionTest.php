<?php

namespace api;

use Api;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class CsrfProtectionTest extends TestCase {
    private const TEST_ALBUM_ID = 98998;
    private const TEST_ALBUM_CODE = 'csrf-code';
    private const TEST_TAG = 'csrf-protection-test-tag';

    private ApiTestClient $http;
    private Sql $sql;

    public function setUp(): void {
        $this->http = new ApiTestClient([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/',
        ]);
        $this->sql = new Sql();
        $this->sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [self::TEST_ALBUM_ID]);
        $this->sql->executeStatement('DELETE FROM albums WHERE id = ?', [self::TEST_ALBUM_ID]);
        $this->sql->executeStatement('DELETE FROM tags WHERE tag = ?', [self::TEST_TAG]);
        $this->sql->executeStatement(
            "INSERT INTO albums (id, name, description, location, owner, code) VALUES (?, 'CSRF test album', '', 'csrf-test', 4, ?)",
            [self::TEST_ALBUM_ID, self::TEST_ALBUM_CODE]
        );
    }

    public function tearDown(): void {
        $this->sql->executeStatement('DELETE FROM albums_for_users WHERE album = ?', [self::TEST_ALBUM_ID]);
        $this->sql->executeStatement('DELETE FROM albums WHERE id = ?', [self::TEST_ALBUM_ID]);
        $this->sql->executeStatement('DELETE FROM tags WHERE tag = ?', [self::TEST_TAG]);
        $this->sql->executeStatement('DELETE FROM remember_tokens WHERE user IN (1, 4)');

        $nextAlbumId = ((int)$this->sql->getRow('SELECT MAX(id) AS maxId FROM albums')['maxId']) + 1;
        $this->sql->executeStatement("ALTER TABLE albums AUTO_INCREMENT = $nextAlbumId");
        $nextTagId = ((int)$this->sql->getRow('SELECT MAX(id) AS maxId FROM tags')['maxId']) + 1;
        $this->sql->executeStatement("ALTER TABLE tags AUTO_INCREMENT = $nextTagId");

        $this->sql->disconnect();
        unset($this->http);
    }

    public function testCrossOriginAdminWriteIsRejectedWithoutSideEffect(): void {
        $response = $this->http->request('POST', 'api/create-blog-tag.php', [
            'http_errors' => false,
            'headers' => ['Origin' => 'https://cross-origin.example'],
            'form_params' => ['tag' => self::TEST_TAG],
            'cookies' => $this->adminCookies(),
        ]);

        self::assertSame(403, $response->getStatusCode());
        self::assertSame(
            Api::CSRF_ERROR,
            (string)$response->getBody()
        );
        self::assertSame(0, $this->sql->getRowCount(
            'SELECT * FROM tags WHERE tag = ?',
            [self::TEST_TAG]
        ));
    }

    public function testAuthenticatedWriteWithoutOriginOrTokenIsRejected(): void {
        $response = $this->http->request('POST', 'api/create-blog-tag.php', [
            'http_errors' => false,
            'headers' => ['Origin' => ''],
            'form_params' => ['tag' => self::TEST_TAG],
            'cookies' => $this->adminCookies(),
        ]);

        self::assertSame(403, $response->getStatusCode());
        self::assertSame(0, $this->sql->getRowCount(
            'SELECT * FROM tags WHERE tag = ?',
            [self::TEST_TAG]
        ));
    }

    public function testCrossOriginLoggedInWriteIsRejectedWithoutSideEffect(): void {
        $response = $this->http->request('POST', 'api/add-album.php', [
            'http_errors' => false,
            'headers' => ['Origin' => 'https://cross-origin.example'],
            'form_params' => ['code' => self::TEST_ALBUM_CODE],
            'cookies' => $this->uploaderCookies(),
        ]);

        self::assertSame(403, $response->getStatusCode());
        self::assertSame(0, $this->sql->getRowCount(
            'SELECT * FROM albums_for_users WHERE user = 4 AND album = ?',
            [self::TEST_ALBUM_ID]
        ));
    }

    public function testCrossOriginAlbumCreationIsRejectedBeforeValidation(): void {
        $response = $this->http->request('POST', 'api/create-album.php', [
            'http_errors' => false,
            'headers' => ['Origin' => 'https://cross-origin.example'],
            'cookies' => $this->uploaderCookies(),
        ]);

        self::assertSame(403, $response->getStatusCode());
        self::assertSame(Api::CSRF_ERROR, (string)$response->getBody());
    }

    public function testCrossOriginAlbumUpdateIsRejectedWithoutSideEffect(): void {
        $response = $this->http->request('POST', 'api/update-album.php', [
            'http_errors' => false,
            'headers' => ['Origin' => 'https://cross-origin.example'],
            'form_params' => ['id' => self::TEST_ALBUM_ID],
            'cookies' => $this->uploaderCookies(),
        ]);

        self::assertSame(403, $response->getStatusCode());
        self::assertSame(
            'CSRF test album',
            $this->sql->getRow(
                'SELECT name FROM albums WHERE id = ?',
                [self::TEST_ALBUM_ID]
            )['name']
        );
    }

    public function testValidSessionTokenAllowsWriteWithoutOrigin(): void {
        $cookies = $this->adminCookies();
        $page = $this->http->request('GET', '/', [
            'cookies' => $cookies,
        ]);
        self::assertSame(
            1,
            preg_match(
                '/name="csrf_token"[^>]*value="([a-f0-9]{64})"/',
                (string)$page->getBody(),
                $matches
            )
        );

        $response = $this->http->request('POST', 'api/create-blog-tag.php', [
            'headers' => [
                'Origin' => '',
                'X-CSRF-Token' => $matches[1],
            ],
            'form_params' => ['tag' => self::TEST_TAG],
            'cookies' => $cookies,
        ]);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(1, $this->sql->getRowCount(
            'SELECT * FROM tags WHERE tag = ?',
            [self::TEST_TAG]
        ));
    }

    public function testSameOriginAdminWriteRemainsAllowed(): void {
        $response = $this->http->request('POST', 'api/create-blog-tag.php', [
            'form_params' => ['tag' => self::TEST_TAG],
            'cookies' => $this->adminCookies(),
        ]);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(1, $this->sql->getRowCount(
            'SELECT * FROM tags WHERE tag = ?',
            [self::TEST_TAG]
        ));
    }

    private function adminCookies(): CookieJar {
        return CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191',
        ], getenv('DB_HOST'));
    }

    private function uploaderCookies(): CookieJar {
        return CookieJar::fromArray([
            'hash' => 'c90788c0e409eac6a95f6c6360d8dbf7',
        ], getenv('DB_HOST'));
    }
}
