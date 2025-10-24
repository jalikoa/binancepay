<?php
namespace App\Utils;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggerFactory
{
    public static function create(string $channel = 'app'): Logger
    {
        $logPath = __DIR__ . '/../../storage/logs/' . $channel . '.log';
        if (!is_dir(dirname($logPath))) {
            mkdir(dirname($logPath), 0755, true);
        }
        $logger = new Logger($channel);
        $logger->pushHandler(new StreamHandler($logPath, Logger::DEBUG));
        return $logger;
    }
}