<?php

namespace ui\http;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'HttpTestBase.php';
require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

use Sql;

class PublicContractHttpTest extends HttpTestBase {
    private const CONTRACT_ID = 999;
    private const CONTRACT_LINK = '8e07fb32bf072e1825df8290a7bcdc57';

    protected function tearDown(): void {
        $sql = new Sql();
        try {
            $sql->executeStatement('DELETE FROM contracts WHERE id = ' . self::CONTRACT_ID);
        } finally {
            $sql->disconnect();
        }
    }

    /**
     * @dataProvider invalidContractProvider
     */
    public function testInvalidContractLinkReturnsNotFound(string $path): void {
        $response = $this->get($path);
        self::assertSame(404, $response->getStatusCode());
        self::assertSame('404 Not Found', $this->text($response, '//h1'));
        self::assertSame($this->copyright, $this->text($response, "//*[contains(concat(' ', normalize-space(@class), ' '), ' copyright ')]"));
        \CustomAsserts::assertEmailSubjectExists('404 Error');
    }

    public static function invalidContractProvider(): array {
        return [
            'missing contract link' => ['contract.php'],
            'blank contract link' => ['contract.php?c='],
            'unknown contract link' => ['contract.php?c=2'],
        ];
    }

    public function testUnsignedContractWithInvoice(): void {
        $this->insertContract('nope!');

        $response = $this->get('contract.php?c=' . self::CONTRACT_LINK);
        $this->assertPage($response, 'Saperstone Studios Contracts');

        self::assertSame((string) self::CONTRACT_ID, $this->attribute($response, "//*[@id='contract-id']", 'value'));
        self::assertSame(0, $this->elementCount($response, '//embed'));
        self::assertSame(1, $this->elementCount($response, "//*[@id='contract']"));
        self::assertSame(1, $this->elementCount($response, "//*[@id='contract-initial-holder']"));
        self::assertSame(1, $this->elementCount($response, "//*[@id='contract-submit']"));
        self::assertSame('Paypal Invoice Link', $this->text($response, "//*[@id='contract-messages']"));
        self::assertSame('nope!', $this->attribute($response, "//*[@id='contract-messages']//a", 'href'));
    }

    public function testUnsignedContractWithoutInvoice(): void {
        $this->insertContract('');

        $response = $this->get('contract.php?c=' . self::CONTRACT_LINK);
        $this->assertPage($response, 'Saperstone Studios Contracts');

        self::assertSame(0, $this->elementCount($response, '//embed'));
        self::assertSame(1, $this->elementCount($response, "//*[@id='contract']"));
        self::assertSame(1, $this->elementCount($response, "//*[@id='contract-initial-holder']"));
        self::assertSame(1, $this->elementCount($response, "//*[@id='contract-messages']"));
        self::assertSame(0, $this->elementCount($response, "//*[@id='contract-messages']//a"));
        self::assertSame(1, $this->elementCount($response, "//*[@id='contract-submit']"));
    }

    public function testSignedContractRendersPdfInsteadOfSigningForm(): void {
        $this->insertContract('', 'x', 'y', '../user/contracts/My File.pdf');

        $response = $this->get('contract.php?c=' . self::CONTRACT_LINK);
        $this->assertPage($response, 'Saperstone Studios Contracts');

        self::assertSame('../user/contracts/My File.pdf', $this->attribute($response, '//embed', 'src'));
        self::assertSame('application/pdf', $this->attribute($response, '//embed', 'type'));
        self::assertSame(0, $this->elementCount($response, "//*[@id='contract']"));
        self::assertSame(0, $this->elementCount($response, "//*[@id='contract-initial-holder']"));
        self::assertSame(0, $this->elementCount($response, "//*[@id='contract-messages']"));
        self::assertSame(0, $this->elementCount($response, "//*[@id='contract-submit']"));
    }

    private function insertContract(
        string $invoice,
        ?string $signature = null,
        ?string $initial = null,
        ?string $file = null
    ): void {
        $sql = new Sql();
        try {
            $statement = "INSERT INTO contracts (id, link, type, name, address, number, email, date, location, session, details, amount, deposit, invoice, content, signature, initial, file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $sql->executeStatement($statement, [
                self::CONTRACT_ID,
                self::CONTRACT_LINK,
                'commercial',
                'MaxMaxMax',
                'Address',
                '1234567890',
                'email-address',
                '2021-10-13',
                '1234 Sesame Street',
                'Some session',
                'details',
                '0.00',
                '0.00',
                $invoice,
                'content',
                $signature,
                $initial,
                $file,
            ]);
        } finally {
            $sql->disconnect();
        }
    }
}
