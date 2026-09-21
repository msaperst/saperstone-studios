<?php

namespace api;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

class ErrorDisclosureTest extends TestCase {
    private ApiTestClient $http;
    private CookieJar $adminCookies;

    public function setUp(): void {
        $this->http = new ApiTestClient([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/',
        ]);
        $this->adminCookies = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191',
        ], getenv('DB_HOST'));
    }

    /**
     * @throws GuzzleException
     */
    public function testMalformedLoginParametersDoNotDisclosePhpErrors(): void {
        $paths = [
            'b-nai-mitzvah/index.php',
            'b-nai-mitzvah/photobooth.php',
            'b-nai-mitzvah/process.php',
            'b-nai-mitzvah/products.php',
            'b-nai-mitzvah/sessions.php',
        ];
        $queries = [
            '?pass=ZAP&=',
            '?pass=&user=ZAP',
        ];

        foreach ($paths as $path) {
            foreach ($queries as $query) {
                $response = $this->http->request('GET', $path . $query, [
                    'cookies' => $this->adminCookies,
                ]);
                $body = (string)$response->getBody();

                $this->assertSame(200, $response->getStatusCode(), $path . $query);
                $this->assertStringNotContainsString(' on line <b>', $body, $path . $query);
                $this->assertStringNotContainsString('Warning:', $body, $path . $query);
                $this->assertStringNotContainsString('Fatal error:', $body, $path . $query);
                $this->assertStringNotContainsString('/var/www/', $body, $path . $query);
            }
        }
    }
}
