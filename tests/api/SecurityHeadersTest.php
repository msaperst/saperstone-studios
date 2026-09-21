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
        }
    }
}
