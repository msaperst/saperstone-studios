<?php

namespace coverage\unit;

use Api;
use BadRequestException;
use Exception;
use PHPUnit\Framework\TestCase;
use SqlException;

// Suppress warnings/notices from autoloader
@require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class ApiUnitTest extends TestCase {
    private Api $api;
    private array $serverState = [];
    private array $sessionState = [];

    protected function setUp(): void {
        parent::setUp();
        $this->serverState = $_SERVER;
        $this->sessionState = $_SESSION ?? [];
        $this->api = new Api();
    }

    protected function tearDown(): void {
        parent::tearDown();
        $_POST = [];
        $_GET = [];
        $_SERVER = $this->serverState;
        $_SESSION = $this->sessionState;
        http_response_code(200);

        $projectRoot = dirname(__DIR__, 3);
        @unlink($projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'unit-storage-escape');
        @unlink($projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'unit-storage-content-link');
        @unlink($projectRoot . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'unit-storage-content.txt');
    }

    // ------------------------
    // HTTP Method
    // ------------------------
    public function testRequireMethodAllowsMatchingMethod(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        http_response_code(204);

        $this->assertTrue(Api::requireMethod('POST'));
        $this->assertSame(204, http_response_code());
    }

    public function testRequireMethodNormalizesMethodCase(): void {
        $_SERVER['REQUEST_METHOD'] = 'post';
        http_response_code(202);

        $this->assertTrue(Api::requireMethod('PoSt'));
        $this->assertSame(202, http_response_code());
    }

    public function testRequireMethodRejectsDifferentMethod(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        http_response_code(200);

        // PHPUnit may have already written CLI output, so suppress header()'s CLI-only warning.
        $this->assertFalse(@Api::requireMethod('POST'));
        $this->assertSame(405, http_response_code());
    }

    public function testCsrfProtectionAllowsSafeMethodsWithoutToken(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        unset($_SERVER['HTTP_ORIGIN'], $_SERVER['HTTP_X_CSRF_TOKEN']);

        $this->assertTrue(Api::requireCsrfProtection());
    }

    public function testCsrfProtectionAllowsValidSessionToken(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        unset($_SERVER['HTTP_ORIGIN']);

        $session = new \Session();
        $_SERVER['HTTP_X_CSRF_TOKEN'] = $session->getCsrfToken();

        $this->assertTrue(Api::requireCsrfProtection());
    }

    public function testCsrfProtectionAllowsMatchingOrigin(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_HOST'] = 'example.test';
        $_SERVER['SERVER_NAME'] = 'example.test';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['HTTP_ORIGIN'] = 'http://example.test';
        unset(
            $_SERVER['HTTPS'],
            $_SERVER['HTTP_X_FORWARDED_PROTO'],
            $_SERVER['HTTP_X_FORWARDED_HOST'],
            $_SERVER['HTTP_X_CSRF_TOKEN']
        );

        $this->assertTrue(Api::requireCsrfProtection());
    }

    public function testCsrfProtectionRejectsCrossOriginRequestWithoutToken(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_HOST'] = 'example.test';
        $_SERVER['SERVER_NAME'] = 'example.test';
        $_SERVER['HTTP_ORIGIN'] = 'https://evil.example';
        unset($_SERVER['HTTP_X_CSRF_TOKEN']);
        unset($_SESSION['csrf_token']);
        http_response_code(200);

        $this->assertFalse(Api::requireCsrfProtection());
        $this->assertSame(403, http_response_code());
    }

    public function testCsrfProtectionRejectsMissingOriginAndToken(): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        unset($_SERVER['HTTP_ORIGIN'], $_SERVER['HTTP_X_CSRF_TOKEN']);
        unset($_SESSION['csrf_token']);
        http_response_code(200);

        $this->assertFalse(Api::requireCsrfProtection());
        $this->assertSame(403, http_response_code());
    }

    public function testResolvePublicPathAllowsApiRelativePathInsidePublicRoot(): void {
        $path = Api::resolvePublicPath('../img/main/portraits.jpg', 'api');

        $this->assertSame(
            dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'img'
                . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'portraits.jpg',
            $path
        );
    }

    public function testResolvePublicPathAllowsPublicAbsolutePathInsidePublicRoot(): void {
        $path = Api::resolvePublicPath('/portrait/img/sample.jpg');

        $this->assertSame(
            dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'portrait'
                . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'sample.jpg',
            $path
        );
    }

    public function testResolvePublicPathRejectsTraversalOutsidePublicRoot(): void {
        $this->assertNull(Api::resolvePublicPath('../../outside.jpg', 'api'));
    }

    public function testResolvePublicPathRejectsNullByte(): void {
        $this->assertNull(Api::resolvePublicPath("/portrait/img/image.jpg\0.php"));
    }

    public function testResolveStoragePathAllowsExistingPublicFile(): void {
        $path = Api::resolveStoragePath('/css/saperstone-studios.css');

        $this->assertSame(
            realpath(
                dirname(__DIR__, 3)
                . DIRECTORY_SEPARATOR . 'public'
                . DIRECTORY_SEPARATOR . 'css'
                . DIRECTORY_SEPARATOR . 'saperstone-studios.css'
            ),
            $path
        );
    }

    public function testResolveStoragePathRejectsTraversalBeforeCanonicalization(): void {
        $this->assertNull(Api::resolveStoragePath('../../outside.jpg', 'api'));
    }

    public function testResolveStoragePathRejectsTraversalBeforeCanonicalization(): void {
        $this->assertNull(Api::resolveStoragePath('../../outside.jpg', 'api'));
    }

    public function testResolveStoragePathRejectsNullByteBeforeCanonicalization(): void {
        $this->assertNull(Api::resolveStoragePath("/portrait/img/image.jpg\0.php"));
    }

    public function testResolveStoragePathRejectsMissingFile(): void {
        $this->assertNull(Api::resolveStoragePath('/does-not-exist.jpg'));
    }

    public function testResolveStoragePathRejectsSymlinkOutsideStorageRoots(): void {
        $projectRoot = dirname(__DIR__, 3);
        $link = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'unit-storage-escape';
        @unlink($link);
        $this->assertTrue(symlink('../src/Strings.php', $link));

        $this->assertNull(Api::resolveStoragePath('/unit-storage-escape'));
    }

    public function testResolveStoragePathAllowsSymlinkIntoContentStorage(): void {
        $projectRoot = dirname(__DIR__, 3);
        $contentRoot = $projectRoot . DIRECTORY_SEPARATOR . 'content';
        if (!is_dir($contentRoot)) {
            $this->assertTrue(mkdir($contentRoot));
        }

        $target = $contentRoot . DIRECTORY_SEPARATOR . 'unit-storage-content.txt';
        file_put_contents($target, 'fixture');

        $link = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'unit-storage-content-link';
        @unlink($link);
        $this->assertTrue(symlink('../content/unit-storage-content.txt', $link));

        $this->assertSame(realpath($target), Api::resolveStoragePath('/unit-storage-content-link'));
    }

    public function testResolveStorageDirectoryRejectsTraversalBeforeCanonicalization(): void {
        $this->assertNull(Api::resolveStorageDirectory('../../outside.jpg', 'api'));
    }

    public function testResolveStorageDirectoryRejectsTraversalBeforeCanonicalization(): void {
        $this->assertNull(Api::resolveStorageDirectory('../../outside.jpg', 'api'));
    }

    public function testResolveStorageDirectoryRejectsNullByteBeforeCanonicalization(): void {
        $this->assertNull(Api::resolveStorageDirectory("/portrait/img/image.jpg\0.php"));
    }

    public function testResolveStorageDirectoryReturnsCanonicalParent(): void {
        $path = Api::resolveStorageDirectory('/css/example.css');

        $this->assertSame(
            realpath(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'css'),
            $path
        );
    }

    public function testResolveStorageDirectoryRejectsSymlinkOutsideStorageRoots(): void {
        $projectRoot = dirname(__DIR__, 3);
        $link = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'unit-storage-escape';
        @unlink($link);
        $this->assertTrue(symlink('../src', $link));

        $this->assertNull(Api::resolveStorageDirectory('/unit-storage-escape/image.jpg'));
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
