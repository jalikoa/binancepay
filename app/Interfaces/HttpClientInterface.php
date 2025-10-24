<?php
namespace App\Interfaces;
use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface {
    public function post(string $url, array $headers, array $body): ResponseInterface;
}