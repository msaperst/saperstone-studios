<?php

namespace coverage\unit;

use Api;
use BadRequestException;
use Exception;
use PHPUnit\Framework\TestCase;
use SqlException;

// Suppress warnings/notices from autoloader
@require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class TestableApi extends Api {
    public static function requestMethodMatchesForTest(string $method): bool {
        return parent::requestMethodMatches($method);
    }
}

class ApiUnitTest extends TestCase {
    private Api $api;
    private ?string $requestMethod = null;

    protected function setUp(): void {
        parent::setUp();
        $this->api = new Api();
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
    }

    protected function tearDown(): void {
        parent::tearDown();
        $_POST = [];
        $_GET = [];
        if ($this->requestMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->requestMethod;
        }
        http_response_code(200);
    }

    // ------------------------
    // HTTP Method
    // ------------------------
    public function testRequireMethodAllowsMatchingMethod(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        http_response_code(204);

        Api::requireMethod('POST');

        $this->assertSame(204, http_response_code());
    }

    public function testRequireMethodNormalizesMethodCase(): void {
        $_SERVER['REQUEST_METHOD'] = 'post';
        http_response_code(202);

        Api::requireMethod('PoSt');

        $this->assertSame(202, http_response_code());
    }

    public function testRequestMethodDoesNotMatchDifferentMethod(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->assertFalse(TestableApi::requestMethodMatchesForTest('POST'));
    }

    // ------------------------
    // Post Int
    // ------------------------
    public function testRetrievePostIntNotSet(): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Foo is required');
        $this->api->retrievePostInt('bar', 'Foo');
    }

    public function testRetrievePostIntBlank(): void {
        $_POST['bar'] = '';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Foo can not be blank');
        $this->api->retrievePostInt('bar', 'Foo');
    }

    public function testRetrievePostInt(): void {
        $_POST['bar'] = '5';
        $this->assertEquals(5, $this->api->retrievePostInt('bar', 'Foo'));
    }

    public function testRetrievePostIntNotInt(): void {
        $_POST['bar'] = 'abc';
        $this->assertEquals(0, $this->api->retrievePostInt('bar', 'Foo'));
    }

    public function testRetrievePostIntMixed(): void {
        $_POST['bar'] = '3c';
        $this->assertEquals(3, $this->api->retrievePostInt('bar', 'Foo'));
    }

    // ------------------------
    // Post Float
    // ------------------------
    public function testRetrievePostFloat(): void {
        $_POST['bar'] = '5';
        $this->assertEquals(5, $this->api->retrievePostFloat('bar', 'Foo'));
    }

    public function testRetrievePostFloatNotFloat(): void {
        $_POST['bar'] = 'abc';
        $this->assertEquals(0, $this->api->retrievePostFloat('bar', 'Foo'));
    }

    public function testRetrievePostFloatMixed(): void {
        $_POST['bar'] = '3c';
        $this->assertEquals(3, $this->api->retrievePostFloat('bar', 'Foo'));
    }

    public function testRetrievePostFloatMoney(): void {
        $_POST['bar'] = '$12.245';
        $this->assertEqualsWithDelta(12.245, $this->api->retrievePostFloat('bar', 'Foo'), 0.001);
    }

    // ------------------------
    // Validated Post
    // ------------------------
    public function testRetrieveValidatedPostNotSet(): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Foo is required');
        $this->api->retrieveValidatedPost('bar', 'Foo', null);
    }

    public function testRetrieveValidatedPostBlank(): void {
        $_POST['bar'] = '';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Foo can not be blank');
        $this->api->retrieveValidatedPost('bar', 'Foo', null);
    }

    public function testRetrieveValidatedPostNullValidation(): void {
        $_POST['bar'] = 'foo';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Foo is not valid');
        $this->api->retrieveValidatedPost('bar', 'Foo', null);
    }

    public function testRetrieveValidatedPostBadFormat(): void {
        $_POST['bar'] = 'foo';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Foo is not valid');
        $this->api->retrieveValidatedPost('bar', 'Foo', FILTER_VALIDATE_BOOLEAN);
    }

    // ------------------------
    // Post DateTime
    // ------------------------
    public function testRetrievePostDateTimeNotSet(): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Foo is required');
        $this->api->retrievePostDateTime('bar', 'Foo', null);
    }

    public function testRetrievePostDateTimeBlank(): void {
        $_POST['bar'] = '';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Foo can not be blank');
        $this->api->retrievePostDateTime('bar', 'Foo', null);
    }

    // ------------------------
    // Get Int
    // ------------------------
    public function testRetrieveGetIntNotSet(): void {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Foo is required');
        $this->api->retrieveGetInt('bar', 'Foo');
    }

    public function testRetrieveGetIntBlank(): void {
        $_GET['bar'] = '';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Foo can not be blank');
        $this->api->retrieveGetInt('bar', 'Foo');
    }

    public function testRetrieveGetInt(): void {
        $_GET['bar'] = '5';
        $this->assertEquals(5, $this->api->retrieveGetInt('bar', 'Foo'));
    }

    public function testRetrieveGetIntNotInt(): void {
        $_GET['bar'] = 'abc';
        $this->assertEquals(0, $this->api->retrieveGetInt('bar', 'Foo'));
    }

    public function testRetrieveGetIntMixed(): void {
        $_GET['bar'] = '3c';
        $this->assertEquals(3, $this->api->retrieveGetInt('bar', 'Foo'));
    }

    // ------------------------
    // Get Float
    // ------------------------
    public function testRetrieveGetFloat(): void {
        $_GET['bar'] = '5';
        $this->assertEquals(5, $this->api->retrieveGetFloat('bar', 'Foo'));
    }

    public function testRetrieveGetFloatNotFloat(): void {
        $_GET['bar'] = 'abc';
        $this->assertEquals(0, $this->api->retrieveGetFloat('bar', 'Foo'));
    }

    public function testRetrieveGetFloatMixed(): void {
        $_GET['bar'] = '3c';
        $this->assertEquals(3, $this->api->retrieveGetFloat('bar', 'Foo'));
    }

    public function testRetrieveGetFloatMoney(): void {
        $_GET['bar'] = '$12.245';
        $this->assertEqualsWithDelta(12.245, $this->api->retrieveGetFloat('bar', 'Foo'), 0.001);
    }

    public function testBadRequestSetsClientErrorStatus(): void {
        Api::setErrorResponseCode(new BadRequestException('Bad request'));
        $this->assertSame(400, http_response_code());
    }

    public function testSqlExceptionSetsServerErrorStatus(): void {
        Api::setErrorResponseCode(new SqlException('Database failure'));
        $this->assertSame(500, http_response_code());
    }

    public function testUnexpectedExceptionSetsServerErrorStatus(): void {
        Api::setErrorResponseCode(new Exception('Unexpected failure'));
        $this->assertSame(500, http_response_code());
    }
}
