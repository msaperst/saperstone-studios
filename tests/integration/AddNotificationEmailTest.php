<?php
class GetAlbumImagesTest extends PHPUnit_Framework_TestCase {
    private $client;
    protected function setUp() {
        $this->client = new GuzzleHttp\Client ( [ 
                'base_uri' => 'http://localhost/api/' 
        ] );
    }
    public function testNoAlbumProvided() {
        $response = $this->client->get ( 'http://localhost/api/get-album-images.php' );
        
        $this->assertEquals ( 200, $response->getStatusCode () );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "err", $data );
        $this->assertEquals ( "Need to provide album", $data ['err'] );
    }
    public function testBadAlbumProvided() {
        $response = $this->client->get ( 'http://localhost/api/get-album-images.php', [ 
                'query' => [ 
                        'albumId' => 99999 
                ] 
        ] );
        
        $this->assertEquals ( 200, $response->getStatusCode () );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "err", $data );
        $this->assertEquals ( "Album doesn't exist!", $data ['err'] );
    }
    public function testNoPermissions() {
        $response = $this->client->get ( 'http://localhost/api/get-album-images.php', [ 
                'query' => [ 
                        'albumId' => 79 
                ] 
        ] );
        
        $this->assertEquals ( 200, $response->getStatusCode () );
        
        $data = json_decode ( $response->getBody (), true );
        
        $this->assertArrayHasKey ( "err", $data );
        $this->assertEquals ( "You are not authorized to view this album", $data ['err'] );
    }

    //TODO authentication via session variables
}
?>