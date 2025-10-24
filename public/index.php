<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Response;
use App\Utils\ResponseHelper;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$required = ['BINANCE_MERCHANT_ID', 'BINANCE_API_KEY', 'BINANCE_SECRET_KEY', 'DB_DATABASE'];
foreach ($required as $key) {
    if (empty($_ENV[$key])) {
        http_response_code(500);
        die("Configuration error: Missing required environment variable [$key].");
    }
}

require __DIR__ . '/../app/Config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    $response = new Response('', 204);
    echo \App\Middleware\CorsMiddleware::apply($response)->getContent();
    exit;
}

$routes = require __DIR__ . '/../app/Routes/web.php';

$dispatcher = FastRoute\simpleDispatcher(function ($r) use ($routes) {
    foreach ($routes as [$method, $path, $handler]) {
        $r->addRoute($method, $path, $handler);
    }
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        echo ResponseHelper::error('Endpoint not found', 404)->getContent();
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        echo ResponseHelper::error('Method not allowed', 405)->getContent();
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

        $httpClient = new \App\Services\GuzzleHttpClient($_ENV['BINANCE_BASE_URL']);
        $binanceService = new \App\Services\BinancePayService([
            'merchant_id' => $_ENV['BINANCE_MERCHANT_ID'],
            'api_key' => $_ENV['BINANCE_API_KEY'],
            'secret_key' => $_ENV['BINANCE_SECRET_KEY']
        ], $httpClient);

        $paymentRepo = new \App\Repositories\PaymentRepository();
        $paymentService = new \App\Services\PaymentService($binanceService, $paymentRepo);

        $controller = new $handler[0]($paymentService, $paymentRepo, $binanceService);
        $response = $controller->{$handler[1]}($request, $vars);

        $response = \App\Middleware\CorsMiddleware::apply($response);
        $response->send();
        break;
}