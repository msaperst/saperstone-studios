<?php

namespace ui\http;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'HttpTestBase.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

use GuzzleHttp\Cookie\SetCookie;
use Sql;

class AlbumPagesHttpTest extends HttpTestBase {
    private Sql $sql;

    protected function setUp(): void {
        parent::setUp();
        $this->sql = new Sql();
        $this->sql->executeStatement("INSERT INTO albums (id, name, description, location, owner, code) VALUES (99999, 'sample-album', 'sample album for testing', 'sample', 1, '2345')");
        foreach ([9997, 9998, 9999] as $sequence => $id) {
            $this->sql->executeStatement(
                "INSERT INTO album_images (id, album, title, sequence, caption, location, width, height, active) VALUES (?, 99999, '', ?, '', '', 300, 400, 1)",
                [$id, $sequence + 1]
            );
        }
    }

    protected function tearDown(): void {
        $this->sql->executeStatement('DELETE FROM user_logs WHERE album = 99999');
        $this->sql->executeStatement('DELETE FROM download_rights WHERE album = 99999');
        $this->sql->executeStatement('DELETE FROM favorites WHERE album = 99999');
        $this->sql->executeStatement('DELETE FROM notification_emails WHERE album = 99999');
        $this->sql->executeStatement('DELETE FROM albums_for_users WHERE album = 99999');
        $this->sql->executeStatement('DELETE FROM album_images WHERE album = 99999');
        $this->sql->executeStatement('DELETE FROM albums WHERE id = 99999');
        $this->sql->disconnect();
    }

    /**
     * @dataProvider invalidAlbumProvider
     */
    public function testInvalidAlbumReturnsNotFound(string $path): void {
        $response = $this->get($path);
        self::assertSame(404, $response->getStatusCode());
        self::assertSame('404 Not Found', $this->text($response, '//h1'));
        $this->submitErrorReport($response);
        \CustomAsserts::assertEmailSubjectExists('404 Error');
    }

    public static function invalidAlbumProvider(): array {
        return [
            'missing album' => ['user/album.php?'],
            'blank album' => ['user/album.php?album='],
            'unknown album' => ['user/album.php?album=998'],
        ];
    }

    public function testAdminCanAccessAlbumAndGetsAdminControls(): void {
        $this->sql->executeStatement('UPDATE albums SET owner = 4 WHERE id = 99999');
        $this->adminLogin();

        $response = $this->get('user/album.php?album=99999');
        $this->assertAlbumPage($response);
        self::assertSame(1, $this->elementCount($response, "//*[@id='edit-album-btn']"));
        self::assertSame(1, $this->elementCount($response, "//*[@id='access-btn']"));
        self::assertSame(1, $this->elementCount($response, "//*[@id='access-image-btn']"));
        self::assertSame('true', $this->attribute($response, "//*[@id='album-page-config']", 'data-show-title'));
    }

    public function testUploaderOwnerCanAccessAlbumAndGetDataControls(): void {
        $this->sql->executeStatement('UPDATE albums SET owner = 4 WHERE id = 99999');
        $this->loginAs('c90788c0e409eac6a95f6c6360d8dbf7');

        $response = $this->get('user/album.php?album=99999');
        $this->assertAlbumPage($response);
        self::assertSame(1, $this->elementCount($response, "//*[@id='edit-album-btn']"));
        self::assertSame(0, $this->elementCount($response, "//*[@id='access-btn']"));
    }

    public function testUnauthorizedLoggedInUserGetsUnauthorizedAndReport(): void {
        $this->loginAs('5510b5e6fffd897c234cafe499f76146');
        $response = $this->get('user/album.php?album=99999');

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('401 Unauthorized', $this->text($response, '//h1'));
        $this->submitErrorReport($response);
        \CustomAsserts::assertEmailSubjectExists('401 Error');
    }

    public function testSearchedCookieGrantsGuestAccessWithoutAdminControls(): void {
        $this->setSearchedCookie();
        $response = $this->get('user/album.php?album=99999');

        $this->assertAlbumPage($response);
        self::assertSame(0, $this->elementCount($response, "//*[@id='edit-album-btn']"));
        self::assertSame(0, $this->elementCount($response, "//*[@id='access-btn']"));
        self::assertSame(1, $this->elementCount($response, "//*[@id='disabled-downloadable-all-btn']"));
        self::assertSame('false', $this->attribute($response, "//*[@id='album-page-config']", 'data-can-download'));
    }

    public function testAssignedUserCanAccessAlbumAndAccessIsRecorded(): void {
        $this->sql->executeStatement('INSERT INTO albums_for_users (user, album) VALUES (3, 99999)');
        $this->loginAs('5510b5e6fffd897c234cafe499f76146');

        $response = $this->get('user/album.php?album=99999');
        $this->assertAlbumPage($response);

        $album = $this->sql->getRow('SELECT lastAccessed FROM albums WHERE id = 99999');
        self::assertNotEmpty($album['lastAccessed']);

        $log = $this->sql->getRow("SELECT user, action, what, album FROM user_logs WHERE album = 99999 ORDER BY time DESC, id DESC");
        self::assertSame(3, (int) $log['user']);
        self::assertSame('Visited Album', $log['action']);
        self::assertNull($log['what']);
        self::assertSame(99999, (int) $log['album']);

        self::assertSame(1, $this->elementCount($response, "//*[@id='downloadable-all-btn']"));
        self::assertSame('true', $this->attribute($response, "//*[@id='album-page-config']", 'data-can-download'));
    }

    public function testEmptyAlbumRendersNotificationFormWithAdminIdentity(): void {
        $this->sql->executeStatement('DELETE FROM album_images WHERE album = 99999');
        $this->adminLogin();

        $response = $this->get('user/album.php?album=99999');
        self::assertSame(1, $this->elementCount($response, "//*[@id='album-empty-state']"));
        self::assertSame('msaperst@gmail.com', $this->attribute($response, "//*[@id='notify-email']", 'value'));
        self::assertSame('0', $this->attribute($response, "//*[@id='album-page-config']", 'data-total'));
    }

    public function testAlbumServerRenderedModalStateForGuest(): void {
        $this->setSearchedCookie();
        $response = $this->get('user/album.php?album=99999');

        self::assertSame('99999', $this->attribute($response, "//*[@id='album-viewer-overlay']", 'album-id'));
        self::assertSame('99999', $this->attribute($response, "//*[@id='submit']", 'album-id'));
        self::assertSame('', $this->attribute($response, "//*[@id='submit-name']", 'value'));
        self::assertSame('', $this->attribute($response, "//*[@id='submit-email']", 'value'));
        self::assertSame(0, $this->elementCount($response, "//*[@id='favorites']"));
        self::assertSame('3', $this->attribute($response, "//*[@id='album-page-config']", 'data-total'));
    }

    public function testAlbumServerRenderedModalStateForAdmin(): void {
        $this->adminLogin();
        $response = $this->get('user/album.php?album=99999');

        self::assertSame(1, $this->elementCount($response, "//*[@id='favorites']"));
        self::assertSame('Max Saperstone', $this->attribute($response, "//*[@id='submit-name']", 'value'));
        self::assertSame('msaperst@gmail.com', $this->attribute($response, "//*[@id='submit-email']", 'value'));
        self::assertSame(1, $this->elementCount($response, "//*[@id='delete-image-btn']"));
    }

    private function setSearchedCookie(): void {
        $this->cookies->setCookie(new SetCookie([
            'Name' => 'searched',
            'Value' => json_encode([99999 => hash('sha256', 'album2345')]),
            'Domain' => getenv('APP_URL'),
            'Path' => '/',
        ]));
    }

    private function assertAlbumPage(\Psr\Http\Message\ResponseInterface $response): void {
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('sample-album sample album for testing', $this->text($response, '//h1'));
        self::assertSame($this->copyright, $this->text($response, "//*[contains(concat(' ', normalize-space(@class), ' '), ' copyright ')]"));
        self::assertSame('99999', $this->attribute($response, "//*[@id='album-page-config']", 'data-album-id'));
    }
}
