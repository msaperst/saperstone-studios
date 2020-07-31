<?php
use PHPUnit\Framework\TestCase;

$_SERVER ['DOCUMENT_ROOT'] = dirname( dirname ( __DIR__ ) );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";

class SqlIntegrationTest extends TestCase {

    private $sql;

    public function setUp() {
        $this->sql = new Sql();
    }

    public function tearDown() {
        $this->sql->disconnect();
    }

    public function testIsConnected() {
        $this->assertTrue( $this->sql->isConnected() );
        $this->sql->disconnect();
        $this->assertFalse( $this->sql->isConnected() );
    }

    public function testEscapeString() {
        $this->assertEquals( "\\'", $this->sql->escapeString( "'" ) );
        $this->assertEquals( '\\"', $this->sql->escapeString( '"' ) );
        $this->assertEquals( ';', $this->sql->escapeString( ';' ) );
        $this->assertEquals( '\\\\', $this->sql->escapeString( '\\' ) );
        $this->sql->disconnect();
        $this->assertEquals( "'", $this->sql->escapeString( "'" ) );
    }

    public function testGetRow() {
        $row = $this->sql->getRow( "SELECT * FROM users;" );
        $this->assertEquals( 0, $row['id'] );
        $this->assertEquals( '<i>All Users</i>', $row['usr'] );
        $this->assertEquals( '', $row['pass'] );
        $this->assertEquals( '', $row['firstName'] );
        $this->assertEquals( '', $row['lastName'] );
        $this->assertEquals( '', $row['email'] );
        $this->assertEquals( 'admin', $row['role'] );
        $this->assertEquals( '', $row['hash'] );
        $this->assertEquals( 0, $row['active'] );
        $this->assertEquals( 1, preg_match( "/([\\d]{4})-([\\d]{2})-([\\d]{2}) ([\\d]{2}):([\\d]{2}):([\\d]{2})/", $row['created'] ) );
        $this->assertNull( $row['lastLogin'] );
        $this->assertNull( $row['resetKey'] );
        $this->sql->disconnect();
        $row = $this->sql->getRow( "SELECT * FROM users;" );
        $this->assertEquals( array(), $row );
    }

    public function testGetRows() {
        $rows = $this->sql->getRows( "SELECT * FROM users;" );
        $this->assertEquals( 5, sizeOf( $rows ) );
        $this->assertEquals( 0, $rows[0]['id'] );
        $this->assertEquals( '<i>All Users</i>', $rows[0]['usr'] );
        $this->assertEquals( '', $rows[0]['pass'] );
        $this->assertEquals( '', $rows[0]['firstName'] );
        $this->assertEquals( '', $rows[0]['lastName'] );
        $this->assertEquals( '', $rows[0]['email'] );
        $this->assertEquals( 'admin', $rows[0]['role'] );
        $this->assertEquals( '', $rows[0]['hash'] );
        $this->assertEquals( 0, $rows[0]['active'] );
        $this->assertEquals( 1, preg_match( "/([\\d]{4})-([\\d]{2})-([\\d]{2}) ([\\d]{2}):([\\d]{2}):([\\d]{2})/", $rows[0]['created'] ) );
        $this->assertNull( $rows[0]['lastLogin'] );
        $this->assertNull( $rows[0]['resetKey'] );
        $rows = $this->sql->getRows( "SELECT * FROM user;" );
        $this->assertEquals( array(), $rows );
        $this->sql->disconnect();
        $rows = $this->sql->getRows( "SELECT * FROM users;" );
        $this->assertEquals( array(), $rows );
    }

    public function testGetRowCount() {
        $this->assertEquals( 5, $this->sql->getRowCount( "SELECT * FROM users;" ) );
        $this->assertEquals( 0, $this->sql->getRowCount( "SELECT * FROM user;" ) );
        $this->sql->disconnect();
        $this->assertEquals( 0, $this->sql->getRowCount( "SELECT * FROM users;" ) );
    }

    public function testExecuteStatement() {
        try {
            $this->assertEquals( 81, $this->sql->executeStatement( "INSERT INTO `tags` (`tag`) VALUES ('test-tag');" ) );
        } finally {
            $this->sql->executeStatement( "DELETE FROM `tags` WHERE `tags`.`id` = 81" );
            $count = $this->sql->getRow( "SELECT MAX(`id`) AS `count` FROM `tags`;")['count'];
            $count++;
            $this->sql->executeStatement( "ALTER TABLE `tags` AUTO_INCREMENT = $count;" );
        }
    }

    public function testGetEnumValues() {
        $enums = $this->sql->getEnumValues('users', 'role');
        $this->assertEquals( 3, sizeOf( $enums ) );
        $this->assertEquals( 'admin', $enums[0] );
        $this->assertEquals( 'uploader', $enums[1] );
        $this->assertEquals( 'downloader', $enums[2] );
    }
}
?>