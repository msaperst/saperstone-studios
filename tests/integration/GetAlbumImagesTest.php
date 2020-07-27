<?php
use PHPUnit\Framework\TestCase;

$_SERVER ['DOCUMENT_ROOT'] = dirname ( __DIR__ );
require_once dirname ( $_SERVER ['DOCUMENT_ROOT'] ) . DIRECTORY_SEPARATOR . "src/sql.php";

class GetAlbumImagesTest extends TestCase {
    private $client;
    protected function setUp() {
        $this->client = new GuzzleHttp\Client ( [ 
                'base_uri' => 'http://localhost/api/' 
        ] );
        // seed required test data
        $sql = new Sql ();
        $sql = "INSERT INTO `albums` ( `id`, `name`, `description`, `location`, `owner`) VALUES (9999998, 'Integration Test Album', 'An album to verify an integration test', '/tmp', 1);";
        mysqli_query ( $conn->db, $sql );
        $conn->disconnect ();
    }
    protected function tearDown() {
        // removed seeded test data
        $sql = new Sql ();
        $sql = "DELETE FROM `albums` WHERE `id` = '9999998';";
        mysqli_query ( $conn->db, $sql );
        $conn->disconnect ();
    }
    public function testNoAlbumProvided() {
        $response = $this->client->get ( 'http://localhost/api/get-album-images.php' );
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertEquals ( 200, $response->getStatusCode () );
        $this->assertArrayHasKey ( "err", $data );
        $this->assertEquals ( "Need to provide album", $data ['err'] );
    }
    public function testBadAlbumProvided() {
        $response = $this->client->get ( 'http://localhost/api/get-album-images.php', [ 
                'query' => [ 
                        'albumId' => 9999999 
                ] 
        ] );
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertEquals ( 200, $response->getStatusCode () );
        $this->assertArrayHasKey ( "err", $data );
        $this->assertEquals ( "Album doesn't exist!", $data ['err'] );
    }
    public function testNoPermissions() {
        $response = $this->client->get ( 'http://localhost/api/get-album-images.php', [ 
                'query' => [ 
                        'albumId' => 9999998 
                ] 
        ] );
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertEquals ( 200, $response->getStatusCode () );
        $this->assertArrayHasKey ( "err", $data );
        $this->assertEquals ( "You are not authorized to view this album", $data ['err'] );
    }
    
    // TODO authentication via session variables
}
?>