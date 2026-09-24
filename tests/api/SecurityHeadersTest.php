<?php

namespace api;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

class SecurityHeadersTest extends TestCase {
    private ApiTestClient $http;
    private CookieJar $adminCookies;

    public function setUp(): void {
        $this->http = new ApiTestClient([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/',
            'http_errors' => false,
        ]);
        $this->adminCookies = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191',
        ], getenv('DB_HOST'));
    }

    /**
     * @throws GuzzleException
     */
    public function testResponseHardeningIsAppliedSiteWide(): void {
        $requests = [
            ['GET', 'index.php', []],
            ['GET', 'user/index.php', ['cookies' => $this->adminCookies]],
            ['GET', 'api/get-roles.php', ['cookies' => $this->adminCookies]],
            ['GET', 'css/saperstone-studios.css', []],
            ['GET', 'does-not-exist', []],
        ];

        foreach ($requests as [$method, $path, $options]) {
            $response = $this->http->request($method, $path, $options);

            $this->assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'), $path);
            $this->assertSame('Apache', $response->getHeaderLine('Server'), $path);
        }

        $html = $this->http->request('GET', 'index.php');
        $css = $this->http->request('GET', 'css/saperstone-studios.css');

        $this->assertStringStartsWith('text/html', $html->getHeaderLine('Content-Type'));
        $this->assertStringStartsWith('text/css', $css->getHeaderLine('Content-Type'));
    }

    /**
     * @throws GuzzleException
     */
    public function testSpellingCorrectionOnlyNormalizesFilenameCase(): void {
        $valid = $this->http->request('GET', 'Privacy-Policy.php');
        $this->assertSame(200, $valid->getStatusCode());

        foreach (['privacy-policy.php', 'Privacy-Policy.PHP'] as $path) {
            $response = $this->http->request('GET', $path, ['allow_redirects' => false]);

            $this->assertSame(301, $response->getStatusCode(), $path);
            $this->assertStringEndsWith('/Privacy-Policy.php', $response->getHeaderLine('Location'), $path);
            $this->assertStringNotContainsString('Multiple Choices', (string) $response->getBody(), $path);

            $followed = $this->http->request('GET', $path);
            $this->assertSame(200, $followed->getStatusCode(), $path);
        }

        $misspelled = $this->http->request('GET', 'Privacy-Policy.ph');
        $body = (string) $misspelled->getBody();

        $this->assertSame(404, $misspelled->getStatusCode());
        $this->assertStringNotContainsString('Multiple Choices', $body);
        $this->assertStringNotContainsString('Available documents', $body);
        $this->assertStringNotContainsString('/Privacy-Policy.php', $body);
    }

    /**
     * @throws GuzzleException
     */
    public function testPhpVersionIsNotExposed(): void {
        $requests = [
            ['GET', 'index.php', []],
            ['GET', 'user/index.php', ['cookies' => $this->adminCookies]],
            ['GET', 'api/get-roles.php', ['cookies' => $this->adminCookies]],
            ['GET', 'does-not-exist', []],
        ];

        foreach ($requests as [$method, $path, $options]) {
            $response = $this->http->request($method, $path, $options);

            $this->assertFalse($response->hasHeader('X-Powered-By'), $path);
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testContentSecurityPolicyIsAppliedSiteWide(): void {
        $requests = [
            ['GET', 'index.php', []],
            ['GET', 'user/index.php', ['cookies' => $this->adminCookies]],
            ['GET', 'api/get-roles.php', ['cookies' => $this->adminCookies]],
            ['GET', 'does-not-exist', []],
        ];

        foreach ($requests as [$method, $path, $options]) {
            $response = $this->http->request($method, $path, $options);
            $headers = $response->getHeader('Content-Security-Policy');

            $this->assertCount(1, $headers, $path);
            $this->assertStringContainsString("default-src 'self'", $headers[0], $path);
            $this->assertStringContainsString("object-src 'none'", $headers[0], $path);
            $this->assertStringContainsString("frame-ancestors 'none'", $headers[0], $path);
            $this->assertStringContainsString("form-action 'self'", $headers[0], $path);
            $this->assertStringContainsString('frame-src https://www.google.com', $headers[0], $path);
            $this->assertSame(1, preg_match('/(?:^|;\\s*)style-src\\s+([^;]+)/', $headers[0], $styleSource), $path);
            $this->assertStringNotContainsString("'unsafe-inline'", $styleSource[1], $path);
            $this->assertStringContainsString("style-src-attr 'unsafe-inline'", $headers[0], $path);
            $this->assertMatchesRegularExpression(
                '/img-src[^;]*https:\\/\\/cdn\\.datatables\\.net/',
                $headers[0],
                $path
            );
        }
    }
}
