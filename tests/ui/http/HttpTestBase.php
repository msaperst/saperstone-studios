<?php

namespace ui\http;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

abstract class HttpTestBase extends TestCase {
    protected Client $client;
    protected CookieJar $cookies;
    protected string $baseUrl;
    protected string $copyright;

    protected function setUp(): void {
        $this->baseUrl = 'http://' . getenv('APP_URL') . ':' . getenv('HTTP_PORT') . '/';
        $this->copyright = 'Copyright (c) Saperstone Studios ' . date('Y');
        $this->cookies = new CookieJar();
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'cookies' => $this->cookies,
            'http_errors' => false,
            'allow_redirects' => false,
            'headers' => ['User-Agent' => 'SaperstoneStudios-PageTests/1.0'],
        ]);
    }

    protected function get(string $path = ''): ResponseInterface {
        return $this->client->get($path);
    }

    protected function loginAs(string $hash): void {
        $this->cookies->setCookie(new SetCookie([
            'Name' => 'hash',
            'Value' => $hash,
            'Domain' => getenv('APP_URL'),
            'Path' => '/',
        ]));
    }

    protected function adminLogin(): void {
        $this->loginAs('1d7505e7f434a7713e84ba399e937191');
    }

    protected function xpath(ResponseInterface $response): DOMXPath {
        $document = new DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML((string) $response->getBody());
        libxml_clear_errors();
        return new DOMXPath($document);
    }

    protected function text(ResponseInterface $response, string $expression): string {
        $nodes = $this->xpath($response)->query($expression);
        self::assertNotFalse($nodes);
        self::assertGreaterThan(0, $nodes->length, "No element matched XPath: $expression");
        return trim(preg_replace('/\\s+/', ' ', $nodes->item(0)->textContent));
    }

    protected function attribute(ResponseInterface $response, string $expression, string $attribute): string {
        $nodes = $this->xpath($response)->query($expression);
        self::assertNotFalse($nodes);
        self::assertGreaterThan(0, $nodes->length, "No element matched XPath: $expression");
        return $nodes->item(0)->attributes?->getNamedItem($attribute)?->nodeValue ?? '';
    }

    protected function elementCount(ResponseInterface $response, string $expression): int {
        $nodes = $this->xpath($response)->query($expression);
        self::assertNotFalse($nodes);
        return $nodes->length;
    }

    protected function assertPage(ResponseInterface $response, string $heading, string $tag = 'h1'): void {
        self::assertSame(200, $response->getStatusCode());
        self::assertSame($heading, $this->text($response, "//$tag"));
        self::assertSame($this->copyright, $this->text($response, "//*[contains(concat(' ', normalize-space(@class), ' '), ' copyright ')]"));
    }
}
