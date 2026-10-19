<?php

namespace ui\http;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'CustomAsserts.php';

use DOMDocument;
use DOMXPath;
use CustomAsserts;
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
        $this->copyright = 'Copyright ' . html_entity_decode('&' . 'copy;') . ' Saperstone Studios ' . date('Y');
        $this->cookies = new CookieJar();
        CustomAsserts::clearAllEmails();
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

    protected function submitErrorReport(ResponseInterface $errorResponse): void {
        $xpath = $this->xpath($errorResponse);
        $nodes = $xpath->query("//*[@id='error-report-config']");
        self::assertNotFalse($nodes);
        self::assertSame(1, $nodes->length, 'Error page did not expose error-report configuration');

        $config = $nodes->item(0);
        $response = $this->client->post('api/send-error.php', [
            'form_params' => [
                'error' => $config->attributes?->getNamedItem('data-error')?->nodeValue ?? '',
                'page' => $config->attributes?->getNamedItem('data-page')?->nodeValue ?? '',
                'referrer' => $config->attributes?->getNamedItem('data-referrer')?->nodeValue ?? '',
                'resolution' => '0x0',
            ],
        ]);
        self::assertSame(200, $response->getStatusCode());
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
