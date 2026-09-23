<?php

namespace api;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HttpMethodTest extends TestCase {
    private ApiTestClient $http;

    public function setUp(): void {
        $this->http = new ApiTestClient([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/'
        ]);
    }

    public function tearDown(): void {
        unset($this->http);
    }

    public static function postOnlyEndpoints(): array {
        return [
            ['add-notification-email.php'],
            ['contact-me.php'],
            ['create-album.php'],
            ['create-blog-comment.php'],
            ['create-blog-post.php'],
            ['create-blog-tag.php'],
            ['create-contract.php'],
            ['create-user.php'],
            ['crop-image.php'],
            ['delete-album-image.php'],
            ['delete-album.php'],
            ['delete-blog-comment.php'],
            ['delete-blog.php'],
            ['delete-gallery-image.php'],
            ['delete-user.php'],
            ['download-selected-images.php'],
            ['download-send-email.php'],
            ['login-as-user.php'],
            ['login.php'],
            ['make-thumbs.php'],
            ['publish-blog-post.php'],
            ['register-user.php'],
            ['reset-password.php'],
            ['schedule-blog-post.php'],
            ['send-error.php'],
            ['send-notification-email.php'],
            ['send-reset-code.php'],
            ['send-selected-images.php'],
            ['set-favorite.php'],
            ['sign-contract.php'],
            ['unset-favorite.php'],
            ['update-album-users.php'],
            ['update-album.php'],
            ['update-blog-post.php'],
            ['update-contract.php'],
            ['update-gallery-image.php'],
            ['update-gallery-order.php'],
            ['update-gallery.php'],
            ['update-image-downloaders.php'],
            ['update-image-sharers.php'],
            ['update-profile.php'],
            ['update-user-albums.php'],
            ['update-user-password.php'],
            ['update-user.php'],
            ['upload-album-images.php'],
            ['upload-blog-images.php'],
            ['upload-gallery-images.php'],
            ['upload-image.php'],
        ];
    }

    #[DataProvider('postOnlyEndpoints')]
    public function testStateChangingEndpointsRejectGet(string $endpoint): void {
        $response = $this->http->request('GET', 'api/' . $endpoint, [
            'http_errors' => false
        ]);

        $this->assertSame(405, $response->getStatusCode(), $endpoint);
        $this->assertSame('POST', $response->getHeaderLine('Allow'), $endpoint);
        $this->assertSame('', (string)$response->getBody(), $endpoint);
    }
}
