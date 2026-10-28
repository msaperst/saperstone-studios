<?php

namespace api;

use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__, 2) . '/src/autoloader.php';

/**
 * Exercise actual cover image bytes, path validation and auth through Apache.
 * Mocking image URLs alone allowed the blank-cover regression to escape.
 */
class AlbumCoverApiTest extends TestCase {
    private const ALBUM_ID = 98841;
    private const FOLDER = 'test-cover-98841';
    private ApiTestClient $http;
    private Sql $sql;
    private string $albumFolder;
    private int $imageId;

    protected function setUp(): void {
        $this->http = new ApiTestClient([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/'
        ]);
        $this->sql = new Sql();
        $this->sql->executeStatement('DELETE FROM album_images WHERE album = ?', [self::ALBUM_ID]);
        $this->sql->executeStatement('DELETE FROM albums WHERE id = ?', [self::ALBUM_ID]);
        $this->sql->executeStatement(
            "INSERT INTO albums (id, name, description, location, owner, images, thumbsCreated)
             VALUES (?, 'Cover preview fixture', '', ?, 1, 1, 0)",
            [self::ALBUM_ID, self::FOLDER]
        );
        $this->sql->executeStatement(
            "INSERT INTO album_images (album, title, sequence, caption, location, width, height, active)
             VALUES (?, 'Cover', 0, '', ?, 400, 300, 1)",
            [self::ALBUM_ID, '/albums/' . self::FOLDER . '/sample.gif']
        );
        $this->imageId = (int)$this->sql->getRow(
            'SELECT id FROM album_images WHERE album = ? ORDER BY id DESC LIMIT 1',
            [self::ALBUM_ID]
        )['id'];
        $this->albumFolder = dirname(__DIR__, 2) . '/content/albums/' . self::FOLDER;
        if (!is_dir($this->albumFolder)) {
            self::assertTrue(mkdir($this->albumFolder, 0775, true));
        }
    }

    protected function tearDown(): void {
        foreach ([
            $this->albumFolder . '/thumbs/400/sample.gif',
            $this->albumFolder . '/thumbs/800/sample.gif',
            $this->albumFolder . '/full/sample.gif',
            $this->albumFolder . '/sample.gif',
        ] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        foreach ([
            $this->albumFolder . '/thumbs/400',
            $this->albumFolder . '/thumbs/800',
            $this->albumFolder . '/thumbs',
            $this->albumFolder . '/full',
            $this->albumFolder,
        ] as $directory) {
            if (is_dir($directory)) {
                rmdir($directory);
            }
        }
        $this->sql->executeStatement('DELETE FROM album_images WHERE album = ?', [self::ALBUM_ID]);
        $this->sql->executeStatement('DELETE FROM albums WHERE id = ?', [self::ALBUM_ID]);
        $this->sql->disconnect();
    }

    private function adminCookies(): CookieJar {
        return CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));
    }

    private function requestCover(bool $candidate = false, ?CookieJar $cookies = null) {
        $url = 'api/get-album-cover.php?album=' . self::ALBUM_ID;
        if ($candidate) {
            $url .= '&candidate=' . $this->imageId;
        }
        return $this->http->request('GET', $url, [
            'cookies' => $cookies ?? $this->adminCookies(),
            'http_errors' => false
        ]);
    }

    private function installImage(string $relativePath): void {
        $target = $this->albumFolder . '/' . $relativePath;
        $directory = dirname($target);
        if (!is_dir($directory)) {
            self::assertTrue(mkdir($directory, 0775, true));
        }
        self::assertNotFalse(file_put_contents(
            $target,
            base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==')
        ));
    }

    public function testGeneratedThumbnailIsReturnedAsImageBytesForCoverAndPickerPreview(): void {
        $this->installImage('thumbs/400/sample.gif');
        foreach ([false, true] as $candidate) {
            $response = $this->requestCover($candidate);
            self::assertSame(200, $response->getStatusCode());
            self::assertSame('image/gif', $response->getHeaderLine('Content-Type'));
            self::assertSame(
                file_get_contents($this->albumFolder . '/thumbs/400/sample.gif'),
                (string)$response->getBody()
            );
            self::assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
        }
    }

    public function testPickerAndImageEndpointAgreeThatThePreviewIsAvailable(): void {
        $this->installImage('thumbs/400/sample.gif');
        $response = $this->http->request(
            'GET',
            'api/get-cover-candidates.php?album=' . self::ALBUM_ID,
            ['cookies' => $this->adminCookies()]
        );
        self::assertSame(200, $response->getStatusCode());
        $data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $data['candidates']);
        self::assertTrue($data['candidates'][0]['previewAvailable']);
        $preview = $this->http->request('GET', $data['candidates'][0]['preview'], [
            'cookies' => $this->adminCookies()
        ]);
        self::assertSame(200, $preview->getStatusCode());
        self::assertSame('image/gif', $preview->getHeaderLine('Content-Type'));
        self::assertNotSame('', (string)$preview->getBody());
    }

    public function testPickerDoesNotOfferAnUnavailableOriginalAsASelectableCover(): void {
        $this->installImage('sample.gif');
        $response = $this->http->request(
            'GET',
            'api/get-cover-candidates.php?album=' . self::ALBUM_ID,
            ['cookies' => $this->adminCookies()]
        );
        $data = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(1, $data['candidates']);
        self::assertFalse($data['candidates'][0]['previewAvailable']);
        self::assertSame(404, $this->requestCover(true)->getStatusCode());
    }

    public function testOriginalIsNeverServedWhenNoWebDerivativeExists(): void {
        $this->installImage('sample.gif');
        self::assertSame(404, $this->requestCover()->getStatusCode());
        self::assertSame(404, $this->requestCover(true)->getStatusCode());
    }

    public function testCompletedMigrationCanUseProtected1600DerivativeWhenSmallThumbnailIsMissing(): void {
        $this->installImage('full/sample.gif');
        $this->installImage('sample.gif');
        file_put_contents($this->albumFolder . '/sample.gif', "\n", FILE_APPEND);
        $this->sql->executeStatement(
            'UPDATE albums SET thumbsCreated = 1 WHERE id = ?', [self::ALBUM_ID]
        );
        $response = $this->requestCover(true);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('image/gif', $response->getHeaderLine('Content-Type'));
        self::assertSame(
            file_get_contents($this->albumFolder . '/sample.gif'),
            (string)$response->getBody()
        );
    }

    public function testIncorrectCompletedFlagDoesNotExposeBitIdenticalOriginal(): void {
        $this->installImage('full/sample.gif');
        $this->installImage('sample.gif');
        $this->sql->executeStatement(
            'UPDATE albums SET thumbsCreated = 1 WHERE id = ?', [self::ALBUM_ID]
        );
        self::assertSame(404, $this->requestCover(true)->getStatusCode());
    }

    public function testOriginalRootIsNotAcceptedAsAProcessedDerivativeWithoutThumbnailCompletion(): void {
        $this->installImage('full/sample.gif');
        $this->installImage('sample.gif');
        self::assertSame(404, $this->requestCover(true)->getStatusCode());
    }

    public function testUnauthorizedUserCannotRetrieveCoverOrCandidate(): void {
        $this->installImage('thumbs/400/sample.gif');
        $anonymous = new CookieJar();
        foreach ([false, true] as $candidate) {
            $response = $this->requestCover($candidate, $anonymous);
            self::assertSame(403, $response->getStatusCode());
            self::assertSame('', (string)$response->getBody());
        }
    }

    public function testCandidatePreviewRejectsAnImageFromAnotherAlbum(): void {
        $this->installImage('thumbs/400/sample.gif');
        $response = $this->http->request(
            'GET',
            'api/get-album-cover.php?album=' . self::ALBUM_ID . '&candidate=' . ($this->imageId + 100000),
            ['cookies' => $this->adminCookies(), 'http_errors' => false]
        );
        self::assertSame(404, $response->getStatusCode());
    }
}
