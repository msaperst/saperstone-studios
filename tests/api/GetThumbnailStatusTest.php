<?php

namespace api;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

class GetThumbnailStatusTest extends TestCase {
    private $http;

    public function setUp(): void {
        $this->http = new ApiTestClient([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/'
        ]);
        $this->removeStatusFile();
    }

    public function tearDown(): void {
        $this->removeStatusFile();
        $this->http = NULL;
    }

    private function runPhpInContainer(string $code): void {
        exec(
            'docker compose exec -T php php -r ' . escapeshellarg($code),
            $output,
            $exitCode
        );
        $this->assertEquals(0, $exitCode);
    }

    private function writeStatusFile(string $status): void {
        $encodedStatus = base64_encode($status);
        $this->runPhpInContainer(
            "is_dir('/var/www/status') || mkdir('/var/www/status', 0777, true); " .
            "file_put_contents('/var/www/status/thumbnail-status.txt', base64_decode('$encodedStatus'));"
        );
    }

    private function removeStatusFile(): void {
        $this->runPhpInContainer(
            "if (is_file('/var/www/status/thumbnail-status.txt')) { unlink('/var/www/status/thumbnail-status.txt'); }"
        );
    }

    /**
     * @throws GuzzleException
     */
    public function testNotLoggedIn(): void {
        try {
            $this->http->request('GET', 'api/get-thumbnail-status.php');
            $this->fail('Expected unauthenticated thumbnail status request to fail');
        } catch (ClientException $e) {
            $this->assertEquals(401, $e->getResponse()->getStatusCode());
            $this->assertEquals(
                'You must be logged in to perform this action',
                (string)$e->getResponse()->getBody()
            );
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testNoStatusFile(): void {
        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));

        $response = $this->http->request('GET', 'api/get-thumbnail-status.php', [
            'cookies' => $cookieJar
        ]);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('', (string)$response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function testLoggedInUserCanReadStatus(): void {
        $this->writeStatusFile('Creating thumbnail flower1.jpeg...');

        $cookieJar = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191'
        ], getenv('DB_HOST'));

        $response = $this->http->request('GET', 'api/get-thumbnail-status.php', [
            'cookies' => $cookieJar
        ]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringStartsWith('text/plain', $response->getHeaderLine('Content-Type'));
        $this->assertEquals(
            'Creating thumbnail flower1.jpeg...',
            (string)$response->getBody()
        );
    }
}
