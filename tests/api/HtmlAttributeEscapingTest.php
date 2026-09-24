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
    private string $firstName;
    private string $lastName;

    public function setUp(): void {
        $this->http = new ApiTestClient([
            'base_uri' => 'http://' . getenv('DB_HOST') . ':' . getenv('HTTP_PORT') . '/',
            'http_errors' => false,
        ]);
        $this->adminCookies = CookieJar::fromArray([
            'hash' => '1d7505e7f434a7713e84ba399e937191',
        ], getenv('DB_HOST'));
        $this->sql = new Sql();

        $user = $this->sql->getRow("SELECT firstName, lastName FROM users WHERE id = 1");
        $this->firstName = $user['firstName'];
        $this->lastName = $user['lastName'];
    }

    public function tearDown(): void {
        $this->sql->executeStatement(
            "UPDATE users SET firstName = ?, lastName = ? WHERE id = 1",
            [$this->firstName, $this->lastName]
        );
        $this->sql->disconnect();
    }

    /**
     * @throws GuzzleException
     */
    public function testStoredUserNameIsEscapedInsideAttributeValues(): void {
        $payload = 'Max" autofocus onfocus="alert(1)';
        $this->sql->executeStatement(
            "UPDATE users SET firstName = ?, lastName = 'Saperstone' WHERE id = 1",
            [$payload]
        );

        $contact = (string)$this->http->request('GET', 'contact.php', [
            'cookies' => $this->adminCookies,
        ])->getBody();
        $profile = (string)$this->http->request('GET', 'user/profile.php', [
            'cookies' => $this->adminCookies,
        ])->getBody();

        $this->assertStringContainsString(
            'value="Max&quot; autofocus onfocus=&quot;alert(1) Saperstone"',
            $contact
        );
        $this->assertStringContainsString(
            'value="Max&quot; autofocus onfocus=&quot;alert(1)"',
            $profile
        );
        $this->assertStringNotContainsString('value="Max" autofocus', $contact);
        $this->assertStringNotContainsString('value="Max" autofocus', $profile);
    }
}
