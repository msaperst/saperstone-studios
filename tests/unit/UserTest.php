<?php
use PHPUnit\Framework\TestCase;

$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/user.php";
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";

class UserTest extends TestCase {

    public function testNotLoggedIn() {
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(NULL);
        $user = new User($mockSql);
        $this->assertFalse ( $user->isLoggedIn () );
    }

    public function testNoHash() {
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "id" => ""
        ));
        $user = new User($mockSql);
        $this->assertFalse ( $user->isLoggedIn () );
    }

    public function testLoggedInCookie() {
        $_COOKIE['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "id" => ""
        ));
        $user = new User($mockSql);
        unset($_COOKIE['hash']);
        $this->assertTrue ( $user->isLoggedIn () );
    }

    public function testLoggedInSession() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "id" => ""
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertTrue ( $user->isLoggedIn () );
    }

    public function testNotLoggedInCookie() {
        $_COOKIE['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(NULL);
        $user = new User($mockSql);
        unset($_COOKIE['hash']);
        $this->assertFalse ( $user->isLoggedIn () );
    }

    public function testNotLoggedInGetId() {
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(NULL);
        $user = new User($mockSql);
        $this->assertEquals( "", $user->getId() );
    }

    public function testGetId() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "id" => 5
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals ( "5", $user->getId () );
    }

    public function testNotLoggedInGetUsr() {
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(NULL);
        $user = new User($mockSql);
        $this->assertEquals( "", $user->getUser() );
    }

    public function testGetUsr() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "usr" => "Max"
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals ( "Max", $user->getUser () );
    }

    public function testNotLoggedInGetRole() {
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(NULL);
        $user = new User($mockSql);
        $this->assertEquals( "", $user->getRole() );
    }

    public function testGetRole() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "role" => "awesome"
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals ( "awesome", $user->getRole () );
    }

    public function testNotLoggedInIsAdmin() {
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(NULL);
        $user = new User($mockSql);
        $this->assertFalse($user->isAdmin() );
    }

    public function testIsAdminFalse() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "role" => "awesome"
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertFalse ($user->isAdmin () );
    }

    public function testIsAdminTrue() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "role" => "admin"
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertTrue ($user->isAdmin () );
    }
}

?>