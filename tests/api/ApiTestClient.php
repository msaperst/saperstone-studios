<?php

namespace api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Lets API tests inspect application 400/500 responses directly while
 * preserving the existing exception-based assertions for authorization.
 */
class ApiTestClient extends Client {
    public function request(string $method, $uri = '', array $options = []): ResponseInterface {
        $throwAuthorizationErrors = ($options['http_errors'] ?? true) !== false;
        $options['http_errors'] = false;
        $response = parent::request($method, $uri, $options);
        if ($throwAuthorizationErrors && in_array($response->getStatusCode(), [401, 403], true)) {
            throw new ClientException(
                "Authorization request failed with status {$response->getStatusCode()}",
                new Request($method, $uri),
                $response
            );
        }
        return $response;
    }
}
