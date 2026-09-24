<?php

namespace api;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;

class UserControlledAttributeTest extends TestCase {
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
    public function testZapReportedNumericParametersCannotEscapeHtmlAttributes(): void {
        $marker = 'zap-xss-140';
        $routes = [
            ['b-nai-mitzvah/galleries.php', 'w', '95'],
            ['b-nai-mitzvah/gallery.php', 'w', '72'],
            ['commercial/gallery.php', 'w', '57'],
            ['portrait/galleries.php', 'w', '2'],
            ['portrait/gallery.php', 'w', '1'],
            ['portrait/reviews.php', 'c', '1'],
            ['wedding/galleries.php', 'w', '12'],
            ['wedding/gallery.php', 'w', '11'],
            ['wedding/reviews.php', 'c', '2'],
        ];

        foreach ($routes as [$path, $parameter, $validId]) {
            $response = $this->http->request('GET', $path, [
                'query' => [
                    $parameter => $validId . '\" onmouseover=\"' . $marker,
                ],
            ]);
            $body = (string)$response->getBody();

            $this->assertSame(200, $response->getStatusCode(), $path);
            $this->assertStringNotContainsString($marker, $body, $path);
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testOriginalUserAndPassCorrelationsDoNotReflectQueryInput(): void {
        $marker = 'zap-xss-140';
        $query = [
            'user' => 'ZAP\" autofocus onfocus=\"' . $marker,
            'pass' => 'ZAP\" autofocus onfocus=\"' . $marker,
        ];

        $contact = $this->http->request('GET', 'contact.php', [
            'query' => $query,
        ]);
        $profile = $this->http->request('GET', 'user/profile.php', [
            'query' => $query,
            'cookies' => $this->adminCookies,
        ]);

        $this->assertSame(200, $contact->getStatusCode());
        $this->assertSame(200, $profile->getStatusCode());
        $this->assertStringNotContainsString($marker, (string)$contact->getBody());
        $this->assertStringNotContainsString($marker, (string)$profile->getBody());
    }
}
