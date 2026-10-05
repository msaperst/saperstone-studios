<?php

namespace ui\http;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'HttpTestBase.php';

class ContactPageHttpTest extends HttpTestBase {
    public function testContactFormContract(): void {
        $response = $this->get('contact.php');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(1, $this->elementCount($response, "//*[@id='contactForm']"));
        self::assertSame(1, $this->elementCount($response, "//*[@id='success']"));
        self::assertSame(1, $this->elementCount($response, "//*[@id='submit-contact-form']"));

        foreach (['name', 'phone', 'email', 'message'] as $field) {
            self::assertSame(
                1,
                $this->elementCount($response, "//*[@id='$field' and @required]"),
                "Contact field '$field' must remain required"
            );
        }

        self::assertSame(
            '999',
            $this->attribute($response, "//*[@id='message']", 'maxlength')
        );
    }

    public function testContactSpamProtectionFieldsRemainHiddenFromNormalInteraction(): void {
        $response = $this->get('contact.php');

        foreach (['loadtime', 'company'] as $field) {
            self::assertSame(
                '-1',
                $this->attribute($response, "//*[@id='$field']", 'tabindex')
            );
            self::assertSame(
                'off',
                $this->attribute($response, "//*[@id='$field']", 'autocomplete')
            );
            self::assertStringContainsString(
                'csp-hidden',
                $this->attribute($response, "//*[@id='$field']", 'class')
            );
        }
    }

    public function testContactPageLoadsValidationAndSubmissionScripts(): void {
        $response = $this->get('contact.php');

        self::assertSame(
            1,
            $this->elementCount($response, "//script[contains(@src, 'js/jqBootstrapValidation.js')]")
        );
        self::assertSame(
            1,
            $this->elementCount($response, "//script[contains(@src, 'js/contact_me.js')]")
        );
    }
}
