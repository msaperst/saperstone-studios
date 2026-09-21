<?php

namespace api;

use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;

class AnalyticsConsentTest extends TestCase {
    private ApiTestClient $http;

    public function setUp(): void {
        $this->http = new ApiTestClient([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/',
            'headers' => ['X-Forwarded-Host' => 'saperstonestudios.com']
        ]);
    }

    public function testAnalyticsIsNotLoadedBeforeConsent(): void {
        $this->assertAnalyticsNotLoaded([]);
    }

    public function testAnalyticsIsNotLoadedWhenRejected(): void {
        $this->assertAnalyticsNotLoaded([
            'cookies' => $this->preferenceCookies(['preferences'])
        ]);
    }

    public function testAnalyticsIsNotLoadedForInvalidPreferences(): void {
        $this->assertAnalyticsNotLoaded([
            'cookies' => CookieJar::fromArray([
                'CookiePreferences' => 'invalid'
            ], getenv('DB_HOST'))
        ]);
    }

    public function testAnalyticsIsLoadedAfterConsent(): void {
        $response = $this->http->request('GET', '/', [
            'cookies' => $this->preferenceCookies(['analytics'])
        ]);
        $body = (string)$response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $body);
        $this->assertStringContainsString('connect.facebook.net/en_US/fbevents.js', $body);
    }

    private function assertAnalyticsNotLoaded(array $options): void {
        $response = $this->http->request('GET', '/', $options);
        $body = (string)$response->getBody();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringNotContainsString('googletagmanager.com/gtag/js', $body);
        $this->assertStringNotContainsString('connect.facebook.net/en_US/fbevents.js', $body);
        $this->assertStringNotContainsString('facebook.com/tr?id=', $body);
    }

    private function preferenceCookies(array $preferences): CookieJar {
        return CookieJar::fromArray([
            'CookiePreferences' => json_encode($preferences)
        ], getenv('DB_HOST'));
    }
}
