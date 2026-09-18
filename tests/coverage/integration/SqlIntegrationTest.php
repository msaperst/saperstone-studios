<?php

namespace coverage\integration;

use Exception;
use mysqli_sql_exception;
use PHPUnit\Framework\TestCase;
use Sql;
use SqlException;

require_once dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoloader.php';

class SqlIntegrationTest extends TestCase {

    private Sql $sql;

    public function setUp(): void {
        $this->sql = new Sql();
    }

    public function tearDown(): void {
        $this->sql->disconnect();
    }

    public function testIsConnected() {
        $this->assertTrue($this->sql->isConnected());
        $this->sql->disconnect();
        $this->assertFalse($this->sql->isConnected());
    }

    public function testEscapeString() {
        $this->assertEquals("\\'", $this->sql->escapeString("'"));
        $this->assertEquals('\\"', $this->sql->escapeString('"'));
        $this->assertEquals(';', $this->sql->escapeString(';'));
        $this->assertEquals('\\\\', $this->sql->escapeString('\\'));
        $this->sql->disconnect();
        $this->assertEquals("'", $this->sql->escapeString("'"));
    }

    public function testGetRow() {
        $row = $this->sql->getRow("SELECT * FROM users;");
        $this->assertEquals(0, $row['id']);
        $this->assertEquals('<i>All Users</i>', $row['usr']);
        $this->assertEquals('', $row['pass']);
        $this->assertEquals('', $row['firstName']);
        $this->assertEquals('', $row['lastName']);
        $this->assertEquals('', $row['email']);
        $this->assertEquals('admin', $row['role']);
        $this->assertEquals('', $row['hash']);
        $this->assertEquals(0, $row['active']);
        $this->assertEquals(1, preg_match("/([\\d]{4})-([\\d]{2})-([\\d]{2}) ([\\d]{2}):([\\d]{2}):([\\d]{2})/", $row['created']));
        $this->assertNull($row['lastLogin']);
        $this->assertNull($row['resetKey']);
        $this->sql->disconnect();
        $row = $this->sql->getRow("SELECT * FROM users;");
        $this->assertEquals(array(), $row);
    }

    public function testGetRowParametersCannotChangeQueryLogic() {
        $row = $this->sql->getRow(
            "SELECT id FROM users WHERE usr = ? AND pass = ?",
            ["ci' OR 1=1 -- ", "' OR 1=1 -- "]
        );
        $this->assertNull($row);
    }

    public function testGetRows() {
        $rows = $this->sql->getRows("SELECT * FROM reviews;");
        $this->assertEquals(17, sizeOf($rows));
        $this->sql->disconnect();
        $rows = $this->sql->getRows("SELECT * FROM review;");
        $this->assertEquals(0, sizeOf($rows));
    }

    public function testGetRowsTreatsSqlMetacharactersAsData() {
        $rows = $this->sql->getRows(
            "SELECT id FROM users WHERE usr = ?",
            ["' UNION SELECT id FROM users -- "]
        );
        $this->assertSame([], $rows);
    }

    public function testGetRowsNoTable() {
        $this->expectException(mysqli_sql_exception::class);
        $this->expectExceptionMessage("Table 'saperstone-studios.review' doesn't exist");
        $this->sql->getRows("SELECT * FROM review;");
    }

    public function testGetRowCount() {
        $this->assertEquals(17, $this->sql->getRowCount("SELECT * FROM reviews;"));
        $this->sql->disconnect();
        $this->assertEquals(0, $this->sql->getRowCount("SELECT * FROM reviews;"));
    }

    public function testGetRowCountTreatsBooleanPayloadAsData() {
        $this->assertSame(
            0,
            $this->sql->getRowCount("SELECT * FROM users WHERE usr = ?", ["' OR '1'='1"])
        );
    }

    /**
     * @throws SqlException
     */
    public function testExecuteStatement() {
        try {
            $this->assertEquals(81, $this->sql->executeStatement("INSERT INTO `tags` (`tag`) VALUES ('test-tag');"));
        } finally {
            $this->sql->executeStatement("DELETE FROM `tags` WHERE `tags`.`id` = 81");
            $count = $this->sql->getRow("SELECT MAX(`id`) AS `count` FROM `tags`;")['count'];
            $count++;
            $this->sql->executeStatement("ALTER TABLE `tags` AUTO_INCREMENT = $count;");
        }
    }

    public function testExecuteStatementStoresInjectionPayloadWithoutExecutingIt() {
        $payload = "parameter-test'); DELETE FROM tags; -- ";
        $id = null;
        try {
            $before = $this->sql->getRowCount("SELECT * FROM tags");
            $id = $this->sql->executeStatement("INSERT INTO tags (tag) VALUES (?)", [$payload]);

            $this->assertSame($payload, $this->sql->getRow("SELECT tag FROM tags WHERE id = ?", [$id])['tag']);
            $this->assertSame($before + 1, $this->sql->getRowCount("SELECT * FROM tags"));
        } finally {
            if ($id !== null) {
                $this->sql->executeStatement("DELETE FROM tags WHERE id = ?", [$id]);
            }
        }
    }

    public function testParametersSupportNullAndNumericValues() {
        $row = $this->sql->getRow(
            "SELECT ? AS integerValue, ? AS decimalValue, ? AS nullValue",
            [42, 12.5, null]
        );

        $this->assertEquals(42, $row['integerValue']);
        $this->assertEquals(12.5, $row['decimalValue']);
        $this->assertNull($row['nullValue']);
    }

    public function testParameterCountMismatchThrows(): void {
        $this->expectException(mysqli_sql_exception::class);
        $this->sql->getRow("SELECT * FROM users WHERE usr = ?", []);
    }

    public function testExecuteStatementDisconnected() {
        $this->sql->disconnect();
        try {
            $this->sql->executeStatement("INSERT INTO `tags` (`tag`) VALUES ('test-tag');");
        } catch (Exception $e) {
            $this->assertEquals('Not connected, unable to execute statement: \'INSERT INTO `tags` (`tag`) VALUES (\'test-tag\');\'', $e->getMessage());
        }
    }

    public function testGetEnumValues() {
        $enums = $this->sql->getEnumValues('users', 'role');
        $this->assertEquals(3, sizeOf($enums));
        $this->assertEquals('admin', $enums[0]);
        $this->assertEquals('uploader', $enums[1]);
        $this->assertEquals('downloader', $enums[2]);
        $this->sql->disconnect();
        $this->assertEquals(array(), $this->sql->getEnumValues('users', 'role'));
    }

    public function testGetEnumValuesRejectsUnsafeIdentifier() {
        $this->expectException(\InvalidArgumentException::class);
        $this->sql->getEnumValues('users; DROP TABLE users', 'role');
    }

    public function testQuoteIdentifierAllowsOnlyIdentifiers(): void {
        $this->assertSame('`album_images`', $this->sql->quoteIdentifier('album_images'));

        $this->expectException(\InvalidArgumentException::class);
        $this->sql->quoteIdentifier('album_images; DROP TABLE users');
    }
}
