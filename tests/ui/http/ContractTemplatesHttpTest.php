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

    public function testPhotoBoothContractHasReadableSectionsAndStableFields(): void {
        $this->adminLogin();
        $response = $this->get('user/contract/photobooth.php');
        self::assertSame(200, $response->getStatusCode());
        $terms = [
            'Scope of Services.',
            'Professional Conduct.',
            'Equipment and Supplies.',
            'Scheduling and Attendance.',
            'Compensation.',
            'Cancellation and Unavailability.',
            'Confidentiality and Client Privacy.',
            'Photography and Social Media.',
        ];
        self::assertSame(count($terms), $this->elementCount($response, '//ol/li'));
        self::assertSame(1, $this->elementCount($response, "//ol[contains(concat(' ', normalize-space(@class), ' '), ' photo-booth-terms ')]"));
        foreach ($terms as $term) {
            self::assertSame(1, $this->elementCount($response, "//ol/li/strong[normalize-space(.)='$term']"));
        }
        self::assertSame(1, $this->elementCount($response, "//h4[normalize-space(.)='AGREEMENT AND EVENT DETAILS:']"));
        foreach (['contract-date', 'contract-location', 'contract-arrival-time', 'contract-operating-hours', 'contract-amount'] as $field) {
            self::assertSame(1, $this->elementCount($response, "//*[@id='$field']"));
            self::assertSame(1, $this->elementCount($response, "//div[contains(concat(' ', normalize-space(@class), ' '), ' form-group ')]//label[@for='$field']"));
        }
        self::assertSame(1, $this->elementCount(
            $response,
            "//*[@id='contract-compensation-unit' and @aria-label='Compensation Unit']"
        ));
        self::assertSame('photobooth', $this->attribute($response, "//*[@id='contract-type']", 'value'));
        self::assertSame(1, $this->elementCount($response, "//h4[normalize-space(.)='Company:']"));
    }

    public static function contractTemplateProvider(): array {
        return [
            'commercial' => ['user/contract/commercial.php', 'Saperstone Studios LLC. Commercial Contract', 'commercial'],
            'contractor' => ['user/contract/contractor.php', 'Saperstone Studios LLC. Contractor Contract', 'contractor'],
            'event' => ['user/contract/event.php', 'Saperstone Studios LLC. Event Contract', 'event'],
            'partnership' => ['user/contract/partnership.php', 'Saperstone Studios LLC. Partnership Contract', 'partnership'],
            'photobooth' => ['user/contract/photobooth.php', 'Saperstone Studios LLC. Photo Booth Attendant Contract', 'photobooth'],
            'portrait' => ['user/contract/portrait.php', 'Saperstone Studios LLC. Portrait Contract', 'portrait'],
            'wedding' => ['user/contract/wedding.php', 'Saperstone Studios LLC. Wedding Contract', 'wedding'],
        ];
    }
}
