<?php

namespace ui\http;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'HttpTestBase.php';

class ContractTemplatesHttpTest extends HttpTestBase {
    /**
     * @dataProvider contractTemplateProvider
     */
    public function testContractTemplateRejectsAnonymousUser(string $path, string $heading, string $type): void {
        $response = $this->get($path);
        self::assertSame(401, $response->getStatusCode());
        self::assertStringNotContainsString($heading, (string) $response->getBody());
    }

    /**
     * @dataProvider contractTemplateProvider
     */
    public function testAdminCanRenderContractTemplate(string $path, string $heading, string $type): void {
        $this->adminLogin();
        $response = $this->get($path);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame($heading, $this->text($response, '//h2'));
        self::assertSame($type, $this->attribute($response, "//*[@id='contract-type']", 'value'));
        self::assertSame(1, $this->elementCount($response, "//*[@id='contract-name']"));
        self::assertSame(1, $this->elementCount($response, "//*[@id='contract-email']"));
    }

    public static function contractTemplateProvider(): array {
        return [
            'commercial' => ['user/contract/commercial.php', 'Saperstone Studios LLC. Commercial Contract', 'commercial'],
            'contractor' => ['user/contract/contractor.php', 'Saperstone Studios LLC. Contractor Contract', 'contractor'],
            'event' => ['user/contract/event.php', 'Saperstone Studios LLC. Event Contract', 'event'],
            'partnership' => ['user/contract/partnership.php', 'Saperstone Studios LLC. Partnership Contract', 'partnership'],
            'portrait' => ['user/contract/portrait.php', 'Saperstone Studios LLC. Portrait Contract', 'portrait'],
            'wedding' => ['user/contract/wedding.php', 'Saperstone Studios LLC. Wedding Contract', 'wedding'],
        ];
    }
}
