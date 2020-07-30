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

    public function testGetIdNoId() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array());
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals ( "", $user->getId () );
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

    public function testGetUsrNoUsr() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array());
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals ( "", $user->getUser () );
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

    public function testGetRoleNoRole() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array());
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals ( "", $user->getRole () );
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

    public function testNotLoggedInGetFirstName() {
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(NULL);
        $user = new User($mockSql);
        $this->assertEquals( "", $user->getFirstName() );
    }

    public function testGetFirstName() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "firstName" => "Max"
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals ( "Max", $user->getFirstName () );
    }

    public function testGetFirstNameNoFistName() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array());
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals ( "", $user->getFirstName () );
    }

    public function testNotLoggedInGetLastName() {
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(NULL);
        $user = new User($mockSql);
        $this->assertEquals( "", $user->getLastName() );
    }

    public function testGetLastName() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "lastName" => "Saperstone"
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals ( "Saperstone", $user->getLastName () );
    }

    public function testGetLastNameNoLastName() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array());
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals ( "", $user->getLastName () );
    }

    public function testGetNameNothing() {
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(NULL);
        $user = new User($mockSql);
        $this->assertEquals( "", $user->getName() );
    }

    public function testGetNameFirst() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "firstName" => "Max",
            "lastName" => NULL
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals( "Max", $user->getName() );
    }

    public function testGetNameFirstNoLast() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "firstName" => "Max",
            "lastName" => ""
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals( "Max", $user->getName() );
    }

    public function testGetNameLast() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "firstName" => NULL,
            "lastName" => "Saperstone"
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals( "Saperstone", $user->getName() );
    }

    public function testGetNameLastNoFirst() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "firstName" => "",
            "lastName" => "Saperstone"
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals( "Saperstone", $user->getName() );
    }

    public function testGetNameBoth() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "firstName" => "Max",
            "lastName" => "Saperstone"
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals( "Max Saperstone", $user->getName() );
    }

    public function testNotLoggedInGetEmail() {
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(NULL);
        $user = new User($mockSql);
        $this->assertEquals( "", $user->getEmail() );
    }

    public function testGetEmail() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "email" => "msaperst+sstest@gmail.com"
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals ( "msaperst+sstest@gmail.com", $user->getEmail () );
    }

    public function testGetEmailNoEmail() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array());
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertEquals ( "", $user->getEmail () );
    }

    public function testForceAdmin() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "role" => "admin"
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertNull ($user->forceAdmin () );
    }

    public function testForceLogin() {
        $_SESSION['hash'] = "123";
        $mockSql = $this->createMock(Sql::class);
        $mockSql->method("getRow")->willReturn(array(
            "id" => ""
        ));
        $user = new User($mockSql);
        unset($_SESSION['hash']);
        $this->assertNull ($user->forceLogin () );
    }
}

?>