<?php
namespace App\Services;
use App\Interfaces\HttpClientInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    private Client $client;

    public function __construct(string $baseUri)
    {
        $this->client = new Client(['base_uri' => $baseUri]);
    }

    public function post(string $url, array $headers, array $body): ResponseInterface
    {
        return $this->client->post($url, [
            'headers' => $headers,
            'json' => $body
        ]);
    }
}