<?php

namespace api;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Sql;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class HtmlAttributeEscapingTest extends TestCase {
    private ApiTestClient $http;
    private CookieJar $adminCookies;
    private Sql $sql;
    private string $originalFirstName;

    public function setUp(): void {
        $this->http = new ApiTestClient([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/',
            'http_errors' => false,
        ]);
        $this->adminCookies = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191',
        ], getenv('DB_HOST'));
        $this->sql = new Sql();
        $this->originalFirstName = $this->sql->getRow(
            "SELECT firstName FROM users WHERE id = 1"
        )['firstName'];
    }

    public function tearDown(): void {
        $this->sql->executeStatement(
            "UPDATE users SET firstName = ? WHERE id = 1",
            [$this->originalFirstName]
        );
        $this->sql->disconnect();
    }

    /**
     * @throws GuzzleException
     */
    public function testStoredUserValuesAreEscapedInHtmlAttributes(): void {
        $payload = 'Max" autofocus onfocus="alert(1)';
        $this->sql->executeStatement(
            "UPDATE users SET firstName = ? WHERE id = 1",
            [$payload]
        );

        $requests = [
            ['contact.php', ['cookies' => $this->adminCookies]],
            ['user/profile.php', ['cookies' => $this->adminCookies]],
        ];

        foreach ($requests as [$path, $options]) {
            $response = $this->http->request('GET', $path, $options);
            $body = (string)$response->getBody();

            $this->assertSame(200, $response->getStatusCode(), $path);
            $this->assertStringContainsString(
                'Max&quot; autofocus onfocus=&quot;alert(1)',
                $body,
                $path
            );
            $this->assertStringNotContainsString($payload, $body, $path);
        }
    }

    /**
     * @throws GuzzleException
     */
    public function testZapProbeParametersAreNotReflectedIntoAttributes(): void {
        $payload = '" autofocus onfocus="alert(1)';

        $requests = [
            ['contact.php', [
                'cookies' => $this->adminCookies,
                'query' => ['user' => $payload, 'pass' => $payload],
            ]],
            ['user/profile.php', [
                'cookies' => $this->adminCookies,
                'query' => ['user' => $payload, 'pass' => $payload],
            ]],
            ['wedding/gallery.php', ['query' => ['w' => $payload]]],
            ['portrait/reviews.php', ['query' => ['c' => $payload]]],
        ];

        foreach ($requests as [$path, $options]) {
            $response = $this->http->request('GET', $path, $options);
            $body = (string)$response->getBody();

            $this->assertSame(200, $response->getStatusCode(), $path);
            $this->assertStringNotContainsString($payload, $body, $path);
        }
    }

    public function testKnownUserValueAttributesUseEscaping(): void {
        $files = [
            'public/contact.php',
            'public/user/profile.php',
            'public/blog/post.php',
            'public/user/album.php',
        ];
        $pattern = '/value\\s*=\\s*"[^"]*<\\?php\\s+echo\\s+\\$user->get(?:Name|Email|FirstName|LastName|Username)\\(\\);\\s*\\?>/s';

        foreach ($files as $file) {
            $source = file_get_contents(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $file);
            $this->assertDoesNotMatchRegularExpression($pattern, $source, $file);
        }
    }
}
