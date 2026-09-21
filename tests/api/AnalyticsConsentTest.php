<?php

namespace api;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class AnalyticsConsentTest extends TestCase {
    private Client $http;

    public function setUp(): void {
        $this->http = new Client([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/',
            'headers' => ['Host' => 'saperstonestudios.com']
        ]);
    }

    public function testAnalyticsIsNotLoadedBeforeConsent(): void {
        $this->assertAnalyticsNotLoaded([]);
    }

    public function testAnalyticsIsNotLoadedWhenRejected(): void {
        $this->assertAnalyticsNotLoaded([
            'headers' => $this->preferenceCookieHeader(['preferences'])
        ]);
    }

    public function testAnalyticsIsNotLoadedForInvalidPreferences(): void {
        $this->assertAnalyticsNotLoaded([
            'headers' => ['Cookie' => 'CookiePreferences=invalid']
        ]);
    }

    public function testAnalyticsIsLoadedAfterConsent(): void {
        $response = $this->http->request('GET', '/', [
            'headers' => $this->preferenceCookieHeader(['analytics'])
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

    private function preferenceCookieHeader(array $preferences): array {
        return [
            'Cookie' => 'CookiePreferences=' . rawurlencode(json_encode($preferences))
        ];
    }
}
