<?php

namespace api;

use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class HtmlAttributeEscapingTest extends TestCase {
    private ApiTestClient $http;
    private Sql $sql;
    private CookieJar $adminCookies;

    public function setUp(): void {
        $this->http = new ApiTestClient([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/',
            'http_errors' => false,
        ]);
        $this->sql = new Sql();

        $this->sql->executeStatement("DELETE FROM users WHERE id = 997");
        $this->sql->executeStatement(
            "INSERT INTO users (id, usr, pass, firstName, lastName, email, role, hash, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                997,
                'escape_test',
                md5('unused'),
                "Max\" autofocus onfocus=\"alert(140)",
                "Saperstone\" onclick=\"alert(141)",
                'escape-test@example.org',
                'admin',
                '14014014014014014014014014014014',
                1,
            ]
        );

        $this->adminCookies = CookieJar::fromArray([
            'hash' => '14014014014014014014014014014014',
        ], getenv('DB_HOST'));
    }

    public function tearDown(): void {
        $this->sql->executeStatement("DELETE FROM users WHERE id = 997");
        $count = (int)$this->sql->getRow("SELECT MAX(id) AS count FROM users")['count'] + 1;
        $this->sql->executeStatement("ALTER TABLE users AUTO_INCREMENT = $count");
        $this->sql->disconnect();
    }

    public function testZapStyleQueryValuesAreNotReflectedIntoHtmlAttributes(): void {
        $marker = 'ZAP140_QUERY_MARKER';
        $payload = rawurlencode("\" data-zap140=\"" . $marker);

        $paths = [
            'contact.php?user=' . $payload . '&pass=' . $payload,
            'wedding/gallery.php?w=' . $payload,
            'portrait/reviews.php?c=1' . $payload,
        ];

        foreach ($paths as $path) {
            $response = $this->http->request('GET', $path);
            $body = (string)$response->getBody();

            $this->assertSame(200, $response->getStatusCode(), $path);
            $this->assertStringNotContainsString($marker, $body, $path);
            $this->assertStringNotContainsString('data-zap140=', $body, $path);
        }
    }

    public function testStoredUserValuesAreEscapedInContactAttributes(): void {
        $response = $this->http->request('GET', 'contact.php', [
            'cookies' => $this->adminCookies,
        ]);
        $body = (string)$response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString(
            'value="Max&quot; autofocus onfocus=&quot;alert(140) Saperstone&quot; onclick=&quot;alert(141)"',
            $body
        );
        $this->assertStringNotContainsString('onfocus="alert(140)"', $body);
        $this->assertStringNotContainsString('onclick="alert(141)"', $body);
    }

    public function testStoredUserValuesAreEscapedInProfileAttributes(): void {
        $response = $this->http->request('GET', 'user/profile.php', [
            'cookies' => $this->adminCookies,
        ]);
        $body = (string)$response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString(
            'value="Max&quot; autofocus onfocus=&quot;alert(140)"',
            $body
        );
        $this->assertStringContainsString(
            'value="Saperstone&quot; onclick=&quot;alert(141)"',
            $body
        );
        $this->assertStringNotContainsString('onfocus="alert(140)"', $body);
        $this->assertStringNotContainsString('onclick="alert(141)"', $body);
    }
}
